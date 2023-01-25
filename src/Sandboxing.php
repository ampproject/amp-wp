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
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use DOMAttr;

/**
 * Experimental service to facilitate flexible AMP.
 *
 * @package AmpProject\AmpWP
 * @since 2.2
 * @internal
 */
final class Sandboxing implements Service, Registerable {

	/**
	 * Option key for enabling sandboxing.
	 *
	 * @deprecated Use Options interface for option keys.
	 *
	 * @var string
	 */
	const OPTION_ENABLED = 'sandboxing_enabled';

	/**
	 * Option key for sandboxing level.
	 *
	 * @deprecated Use Options interface for option keys.
	 *
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
	 * Default sandboxing options schema.
	 *
	 * @var array
	 */
	const DEFAULT_OPTIONS_SCHEMA = [
		Option::SANDBOXING_ENABLED => [
			'type'    => 'bool',
			'default' => false,
		],
		Option::SANDBOXING_LEVEL   => [
			'type'    => 'int',
			'enum'    => self::LEVELS,
			'default' => 1,
		],
	];

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
			self::DEFAULT_OPTIONS_SCHEMA
		);
	}

	/**
	 * Add default options.
	 *
	 * @param array $defaults Default options.
	 * @return array Defaults.
	 */
	public function filter_default_options( $defaults ) {
		foreach ( self::DEFAULT_OPTIONS_SCHEMA as $option_name => $option_schema ) {
			$defaults[ $option_name ] = $option_schema['default'];
		}
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
		if ( isset( $new_options[ Option::SANDBOXING_ENABLED ] ) ) {
			$options[ Option::SANDBOXING_ENABLED ] = (bool) $new_options[ Option::SANDBOXING_ENABLED ];
		}
		if (
			isset( $new_options[ Option::SANDBOXING_LEVEL ] )
			&&
			in_array( $new_options[ Option::SANDBOXING_LEVEL ], self::LEVELS, true )
		) {
			$options[ Option::SANDBOXING_LEVEL ] = $new_options[ Option::SANDBOXING_LEVEL ];
		}
		return $options;
	}

	/**
	 * Add hooks.
	 */
	public function add_hooks() {
		$sandboxing_level = amp_get_sandboxing_level();

		if ( 0 === $sandboxing_level ) {
			return;
		}

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

		add_action( 'amp_finalize_dom', [ $this, 'finalize_document' ], 10, 2 );
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
	 * Remove required AMP markup if not used.
	 *
	 * @param Document $dom                        Document.
	 * @param int      $effective_sandboxing_level Effective sandboxing level.
	 */
	private function remove_required_amp_markup_if_not_used( Document $dom, $effective_sandboxing_level ) {
		if ( 3 === $effective_sandboxing_level ) {
			// When valid AMP is the target, don't remove the scripts since it won't be valid AMP.
			return;
		}

		if ( $dom->ampElements->length > 0 ) {
			return;
		}

		$amp_scripts = $dom->xpath->query( '//script[ @custom-element or @custom-template ]' );
		if ( $amp_scripts->length > 0 ) {
			return;
		}

		// Remove runtime script(s).
		$runtime_scripts = $dom->xpath->query( '//script[ @async and @src and starts-with( @src, "https://cdn.ampproject.org/" ) and contains( @src, "v0" ) ]' );
		foreach ( $runtime_scripts as $runtime_script ) {
			if ( $runtime_script instanceof Element ) {
				$runtime_script->parentNode->removeChild( $runtime_script );
			}
		}

		// Remove runtime style.
		$runtime_style = $dom->xpath->query( './style[ @amp-runtime ]', $dom->head )->item( 0 );
		if ( $runtime_style instanceof Element ) {
			$dom->head->removeChild( $runtime_style );
		}

		// Remove preconnect link.
		$preconnect_link = $dom->xpath->query( './link[ @href = "https://cdn.ampproject.org" ]', $dom->head )->item( 0 );
		if ( $preconnect_link instanceof Element ) {
			$dom->head->removeChild( $preconnect_link );
		}
	}

	/**
	 * Finalize document.
	 *
	 * @param Document $dom                        Document.
	 * @param int      $effective_sandboxing_level Effective sandboxing level.
	 */
	public function finalize_document( Document $dom, $effective_sandboxing_level ) {
		$actual_sandboxing_level = AMP_Options_Manager::get_option( Option::SANDBOXING_LEVEL );

		$meta_generator = $dom->xpath->query( '/html/head/meta[ @name = "generator" and starts-with( @content, "AMP Plugin" ) ]/@content' )->item( 0 );
		if ( $meta_generator instanceof DOMAttr ) {
			$meta_generator->nodeValue .= "; sandboxing-level={$actual_sandboxing_level}:{$effective_sandboxing_level}";
		}

		$this->remove_required_amp_markup_if_not_used( $dom, $effective_sandboxing_level );

		$amp_admin_bar_menu_item = $dom->xpath->query( '//div[ @id = "wpadminbar" ]//li[ @id = "wp-admin-bar-amp" ]' )->item( 0 );
		if ( $amp_admin_bar_menu_item instanceof Element ) {

			switch ( $effective_sandboxing_level ) {
				case 1:
					$text  = '1️⃣';
					$title = __( 'Sandboxing level: Loose (1)', 'amp' );
					break;
				case 2:
					$text  = '2️⃣';
					$title = __( 'Sandboxing level: Moderate (2)', 'amp' );
					break;
				default:
					$text  = '3️⃣';
					$title = __( 'Sandboxing level: Strict (3)', 'amp' );
					break;
			}

			$amp_link = $dom->xpath->query( './a', $amp_admin_bar_menu_item )->item( 0 );
			if ( $amp_link instanceof Element ) {
				$span = $dom->createElement( Tag::SPAN );
				$span->setAttribute( Attribute::TITLE, $title );
				$span->textContent = $text;

				$amp_link->appendChild( $dom->createTextNode( ' ' ) );
				$amp_link->appendChild( $span );
			}

			$amp_submenu_ul = $dom->xpath->query( './div/ul[ @id = "wp-admin-bar-amp-default" ]', $amp_admin_bar_menu_item )->item( 0 );
			if ( $amp_submenu_ul instanceof Element ) {
				$level_li = $dom->createElement( Tag::LI );
				$level_li->setAttribute( Attribute::ID, 'wp-admin-bar-amp-sandboxing-level' );

				$link = $dom->createElement( Tag::A );
				$link->setAttribute( Attribute::CLASS_, 'ab-item' );
				$link->textContent = $title;
				if ( current_user_can( 'manage_options' ) ) {
					$link->setAttribute(
						Attribute::HREF,
						add_query_arg( 'page', AMP_Options_Manager::OPTION_NAME, admin_url( 'admin.php' ) ) . '#sandboxing'
					);
				}

				$level_li->appendChild( $link );
				$amp_submenu_ul->appendChild( $level_li );
			}
		}
	}
}
