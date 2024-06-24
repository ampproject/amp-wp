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
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * EditorSupport class.
 *
 * @internal
 */
final class EditorSupport implements Service {

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
