<?php
/**
 * Functionality around editor support for AMP plugin features.
 *
 * @since 2.1
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Editor;

use AMP_Post_Type_Support;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * EditorSupport class.
 *
 * @internal
 */
final class EditorSupport implements Registerable, Service {

	/**
	 * The minimum version of Gutenberg supported by editor features.
	 *
	 * @var string
	 */
	const GB_MIN_VERSION = '5.4.0';

	/**
	 * The minimum version of WordPress supported by editor features.
	 *
	 * @var string
	 */
	const WP_MIN_VERSION = '5.3';

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_show_notice' ], 99 );
	}

	/**
	 * Shows a notice in the editor if the Gutenberg or WP version prevents plugin features from working.
	 */
	public function maybe_show_notice() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		if ( ! isset( $screen->is_block_editor ) || false === $screen->is_block_editor ) {
			return;
		}

		if ( ! in_array( get_post_type(), AMP_Post_Type_Support::get_eligible_post_types(), true ) ) {
			return;
		}

		if ( $this->editor_supports_amp_block_editor_features() ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) ) {
			wp_add_inline_script(
				'wp-edit-post',
				sprintf(
					'wp.domReady(
						function () {
							wp.data.dispatch( "core/notices" ).createWarningNotice( %s )
						}
					);',
					wp_json_encode( __( 'AMP functionality is not available since your version of the Block Editor is too old. Please either update WordPress core to the latest version or activate the Gutenberg plugin. As a last resort, you may use the Classic Editor plugin instead.', 'amp' ) )
				)
			);
		}
	}

	/**
	 * Returns whether the editor in the current environment supports plugin features.
	 *
	 * @return bool
	 */
	public function editor_supports_amp_block_editor_features() {
		// Check for plugin constant here as well as in the function because editor features won't work in
		// supported WP versions if an old, unsupported GB version is overriding the editor.
		if ( defined( 'GUTENBERG_VERSION' ) ) {
			return $this->has_support_from_gutenberg_plugin();
		}

		return $this->has_support_from_core();
	}

	/**
	 * Returns whether the Gutenberg plugin provides minimal support.
	 *
	 * @return bool
	 */
	public function has_support_from_gutenberg_plugin() {
		return defined( 'GUTENBERG_VERSION' ) && version_compare( GUTENBERG_VERSION, self::GB_MIN_VERSION, '>=' );
	}

	/**
	 * Returns whether WP core provides minimum Gutenberg support.
	 *
	 * @return bool
	 */
	public function has_support_from_core() {
		return version_compare( get_bloginfo( 'version' ), self::WP_MIN_VERSION, '>=' );
	}
}
