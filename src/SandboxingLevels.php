<?php
/**
 * Class SandboxingLevels.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Comments_Sanitizer;
use AMP_Form_Sanitizer;
use AMP_Options_Manager;
use AMP_Script_Sanitizer;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Experimental service to facilitate flexible AMP.
 *
 * @package AmpProject\AmpWP
 * @since 2.2
 * @internal
 */
final class SandboxingLevels implements Service, Registerable, Conditional {

	/**
	 * Option key for sandboxing level.
	 *
	 * @todo Move this to the Options interface once no longer experimental.
	 * @var string
	 */
	const OPTION_SANDBOXING_LEVEL = 'sandboxing_level';

	/**
	 * Sandboxing levels.
	 *
	 * @var int[]
	 */
	const SANDBOXING_LEVELS = [ 1, 2, 3 ];

	/**
	 * Default sandboxing level.
	 *
	 * @var int
	 */
	const DEFAULT_SANDBOXING_LEVEL = 3;

	/**
	 * Whether service is needed.
	 *
	 * @return bool
	 */
	public static function is_needed() {
		/**
		 * Filters whether experimental sandboxing is enabled.
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
				self::OPTION_SANDBOXING_LEVEL => [
					'type'    => 'int',
					'enum'    => self::SANDBOXING_LEVELS,
					'default' => self::DEFAULT_SANDBOXING_LEVEL,
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
		$defaults[ self::OPTION_SANDBOXING_LEVEL ] = self::DEFAULT_SANDBOXING_LEVEL;
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
			isset( $new_options[ self::OPTION_SANDBOXING_LEVEL ] )
			&&
			in_array( $new_options[ self::OPTION_SANDBOXING_LEVEL ], self::SANDBOXING_LEVELS, true )
		) {
			$options[ self::OPTION_SANDBOXING_LEVEL ] = $new_options[ self::OPTION_SANDBOXING_LEVEL ];
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

		add_filter( 'amp_meta_generator', [ $this, 'filter_amp_meta_generator' ] );

		$sandboxing_level = AMP_Options_Manager::get_option( self::OPTION_SANDBOXING_LEVEL );

		// Opt-in to the new script sanitization logic in the script sanitizer.
		add_filter(
			'amp_content_sanitizers',
			static function ( $sanitizers ) use ( $sandboxing_level ) {
				$sanitizers[ AMP_Script_Sanitizer::class ]['sanitize_js_scripts'] = true;

				if ( $sandboxing_level < 3 ) { // <3 === ❤️
					$sanitizers[ AMP_Script_Sanitizer::class ]['comment_reply_allowed']      = 'conditionally';
					$sanitizers[ AMP_Form_Sanitizer::class ]['native_post_forms_allowed']    = 'conditionally';
					$sanitizers[ AMP_Comments_Sanitizer::class ]['ampify_comment_threading'] = 'conditionally';
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
	 * Append the sandboxing level to the AMP meta generator tag.
	 *
	 * @param string $content Meta generator content.
	 * @return string Amended content.
	 */
	public function filter_amp_meta_generator( $content ) {
		$sandboxing_level = AMP_Options_Manager::get_option( self::OPTION_SANDBOXING_LEVEL );
		return $content . sprintf( '; sandboxing-level=%d', $sandboxing_level );
	}
}
