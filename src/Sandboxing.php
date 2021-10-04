<?php
/**
 * Class Sandboxing.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Comments_Sanitizer;
use AMP_Form_Sanitizer;
use AMP_Options_Manager;
use AMP_Script_Sanitizer;
use AMP_Style_Sanitizer;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Attribute;
use AmpProject\Tag;
use DOMAttr;

/**
 * Experimental service to facilitate flexible AMP.
 *
 * @package AmpProject\AmpWP
 * @since 2.2
 * @internal
 */
final class Sandboxing implements Service, Registerable, Conditional {

	/**
	 * Option key for sandboxing level.
	 *
	 * @todo Move this to the Options interface once no longer experimental.
	 * @var string
	 */
	const OPTION_LEVEL = 'sandboxing_level';

	/**
	 * Sandboxing levels.
	 *
	 * @var int[]
	 */
	const LEVELS = [ 1, 2, 3 ];

	/**
	 * Default sandboxing level.
	 *
	 * Note: This will eventually move to level 1 as the default.
	 *
	 * @var int
	 */
	const DEFAULT_LEVEL = 3;

	/**
	 * Whether service is needed.
	 *
	 * @return bool
	 */
	public static function is_needed() {
		/**
		 * Filters whether experimental sandboxing is enabled.
		 *
		 * Note: This filter will be removed and the service as a whole will no longer be Conditional once the feature
		 * is no longer experimental.
		 *
		 * @internal
		 * @since 2.2
		 *
		 * @param bool $enabled Sandboxing enabled.
		 */
		return (bool) apply_filters( 'amp_experimental_sandboxing_enabled', false );
	}

	/**
	 * Register.
	 */
	public function register() {
		add_filter( 'amp_rest_options_schema', [ $this, 'filter_rest_options_schema' ] );
		add_filter( 'amp_default_options', [ $this, 'filter_default_options' ], 10, 2 );
		add_filter( 'amp_options_updating', [ $this, 'sanitize_options' ], 10, 2 );

		add_action( 'init', [ $this, 'add_hooks' ] );
	}

	/**
	 * Filter the REST options schema to add items.
	 *
	 * @param array $schema Schema.
	 * @return array Schema.
	 */
	public function filter_rest_options_schema( $schema ) {
		return array_merge(
			$schema,
			[
				self::OPTION_LEVEL => [
					'type'    => 'int',
					'enum'    => self::LEVELS,
					'default' => self::DEFAULT_LEVEL,
				],
			]
		);
	}

	/**
	 * Add default options.
	 *
	 * @param array $defaults Default options.
	 * @return array Defaults.
	 */
	public function filter_default_options( $defaults ) {
		$defaults[ self::OPTION_LEVEL ] = self::DEFAULT_LEVEL;
		return $defaults;
	}

	/**
	 * Sanitize options.
	 *
	 * @param array $options     Existing options with already-sanitized values for updating.
	 * @param array $new_options Unsanitized options being submitted for updating.
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $options, $new_options ) {
		if (
			isset( $new_options[ self::OPTION_LEVEL ] )
			&&
			in_array( $new_options[ self::OPTION_LEVEL ], self::LEVELS, true )
		) {
			$options[ self::OPTION_LEVEL ] = $new_options[ self::OPTION_LEVEL ];
		}
		return $options;
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		// Limit to Standard mode for now. To support in Transitional/Reader we'd need to discontinue redirecting invalid
		// AMP to non-AMP and omit the amphtml link (in which case it would only be relevant when mobile redirection is
		// enabled).
		if ( ! amp_is_canonical() ) {
			return;
		}

		$sandboxing_level = AMP_Options_Manager::get_option( self::OPTION_LEVEL );

		// Opt-in to the new script sanitization logic in the script sanitizer.
		add_filter(
			'amp_content_sanitizers',
			static function ( $sanitizers ) use ( $sandboxing_level ) {
				$sanitizers[ AMP_Script_Sanitizer::class ]['sanitize_js_scripts'] = true;

				if ( $sandboxing_level < 3 ) { // <3 === ❤️
					$sanitizers[ AMP_Script_Sanitizer::class ]['comment_reply_allowed']      = 'conditionally';
					$sanitizers[ AMP_Form_Sanitizer::class ]['native_post_forms_allowed']    = 'conditionally';
					$sanitizers[ AMP_Comments_Sanitizer::class ]['ampify_comment_threading'] = 'conditionally';
					$sanitizers[ AMP_Style_Sanitizer::class ]['allow_excessive_css']         = true;
				}
				return $sanitizers;
			}
		);

		if ( 1 === $sandboxing_level ) {
			// Keep all invalid AMP markup by default.
			add_filter( 'amp_validation_error_default_sanitized', '__return_false' );
		}

		// To facilitate testing, vary the errors by the sandboxing level.
		add_filter(
			'amp_validation_error',
			static function ( $error ) use ( $sandboxing_level ) {
				$error['sandboxing_level'] = $sandboxing_level;

				return $error;
			}
		);
	}

	/**
	 * Get the effective sandboxing level.
	 *
	 * Even though a site may be configured with a given sandboxing level (e.g. level 1) to allow custom scripts, if no such
	 * markup is on the page to necessitate the loose level, then the effective level will be actually higher.
	 *
	 * @param Document $dom Document.
	 * @return int Effective sandboxing level.
	 */
	public static function get_effective_level( Document $dom ) {
		if ( ValidationExemption::is_document_with_amp_unvalidated_nodes( $dom ) ) {
			return 1;
		} elseif ( ValidationExemption::is_document_with_px_verified_nodes( $dom ) ) {
			return 2;
		} else {
			return 3;
		}
	}

	/**
	 * Finalize document.
	 *
	 * @param Document $dom Document.
	 * @param int|null $effective_sandboxing_level Effective sandboxing level.
	 */
	public function finalize_document( Document $dom, $effective_sandboxing_level ) {
		$actual_sandboxing_level = AMP_Options_Manager::get_option( self::OPTION_LEVEL );

		$meta_generator = $dom->xpath->query( '/html/head/meta[ @name = "generator" and starts-with( @content, "AMP Plugin" ) ]/@content' )->item( 0 );
		if ( $meta_generator instanceof DOMAttr ) {
			$meta_generator->nodeValue .= "; sandboxing-level={$actual_sandboxing_level}:{$effective_sandboxing_level}";
		}

		$amp_admin_bar_menu_item = $dom->xpath->query( '//div[ @id = "wpadminbar" ]//li[ @id = "wp-admin-bar-amp" ]/a' )->item( 0 );
		if ( $amp_admin_bar_menu_item instanceof Element ) {
			$span = $dom->createElement( Tag::SPAN );
			$span->setAttribute(
				Attribute::TITLE,
				sprintf(
					/* translators: %d is the effective sandboxing level */
					__( 'Effective sandboxing level: %d', 'amp' ),
					$effective_sandboxing_level
				)
			);
			switch ( $effective_sandboxing_level ) {
				case 1:
					$text = '1️⃣';
					break;
				case 2:
					$text = '2️⃣';
					break;
				default:
					$text = '3️⃣';
					break;
			}
			$span->textContent = $text;
			$amp_admin_bar_menu_item->appendChild( $dom->createTextNode( ' ' ) );
			$amp_admin_bar_menu_item->appendChild( $span );
		}
	}
}
