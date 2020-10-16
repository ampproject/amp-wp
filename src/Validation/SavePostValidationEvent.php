<?php
/**
 * WP cron process to validate URLs in the background.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Post_Type_Support;
use AmpProject\AmpWP\BackgroundTask\SingleScheduledBackgroundTask;

/**
 * SavePostValidationEvent class.
 *
 * @since 2.1
 *
 * @internal
 */
final class SavePostValidationEvent extends SingleScheduledBackgroundTask {
	/**
	 * The cron action name.
	 *
	 * @var string
	 */
	const BACKGROUND_TASK_NAME = 'amp_single_post_validate';

	/**
	 * Get the event name.
	 *
	 * This is the "slug" of the event, not the display name.
	 *
	 * Note: the event name should be prefixed to prevent naming collisions.
	 *
	 * @return string Name of the event.
	 */
	protected function get_event_name() {
		return self::BACKGROUND_TASK_NAME;
	}

	/**
	 * Returns whether the event should be scheduled.
	 *
	 * @return boolean
	 */
	protected function should_schedule_event( $args ) {
		if ( ! is_array( $args ) || count( $args ) !== 1 ) {
			return false;
		}

		$id = reset( $args );

		if ( wp_is_post_revision( $id ) ) {
			return false;
		}

		if ( ! amp_is_post_supported( $id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Callback for the cron action.
	 *
	 * @param int $post_id The ID of a saved post.
	 */
	public function process( $post_id ) {
		if ( empty( get_post( $post_id ) ) ) {
			return;
		}

		$post_type = get_post_type( $post_id );

		( new URLValidationProvider() )->get_url_validation( get_the_permalink( $post_id ), $post_type );
	}

	/**
	 * Gets the hook on which to schedule the event.
	 *
	 * @return string The action hook name.
	 */
	protected function get_action_hook() {
		return 'save_post';
	}
}
