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
use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * EditorSupport class.
 *
 * @internal
 */
final class EditorSupport implements Registerable, Service {

	/** @var DependencySupport */
	private $dependency_support;

	/**
	 * Constructor.
	 *
	 * @param DependencySupport $dependency_support DependencySupport instance.
	 */
	public function __construct( DependencySupport $dependency_support ) {
		$this->dependency_support = $dependency_support;
	}

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
		if ( $this->dependency_support->has_support() ) {
			return;
		}

		if ( ! $this->is_current_screen_block_editor_for_amp_enabled_post_type() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_inline_script(
			'wp-edit-post',
			sprintf(
				'wp.domReady(
					function () {
						wp.data.dispatch( "core/notices" ).createWarningNotice( %s )
					}
				);',
				wp_json_encode( __( 'AMP functionality is not available since your version of the Block Editor is too old. Please either update WordPress core to the latest version or activate the Gutenberg plugin.', 'amp' ) )
			)
		);
	}

	/**
	 * Returns whether the current screen is using the block editor and the post being edited supports AMP.
	 *
	 * @return bool
	 */
	public function is_current_screen_block_editor_for_amp_enabled_post_type() {
		$screen = get_current_screen();
		return (
			$screen
			&&
			! empty( $screen->is_block_editor )
			&&
			in_array( get_post_type(), AMP_Post_Type_Support::get_supported_post_types(), true )
		);
	}
}
