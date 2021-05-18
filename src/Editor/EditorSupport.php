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
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$is_block_editor = (
			! empty( $screen->is_block_editor )
			||
			// Applicable to Gutenberg v5.5.0 and older.
			( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() )
		);
		if ( ! $is_block_editor ) {
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
					wp_json_encode( __( 'AMP functionality is not available since your version of the Block Editor is too old. Please either update WordPress core to the latest version or activate the Gutenberg plugin.', 'amp' ) )
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
		return $this->dependency_support->has_support();
	}
}
