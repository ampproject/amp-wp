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
	 * Callback for the cron action.
	 *
	 * @param array ...$args Any number of args.
	 */
	public function process( ...$args ) {
		if ( 1 !== count( $args ) ) {
			return;
		}

		$post_id = reset( $args );

		$post_type = get_post_type( $post_id );

		if ( ! post_type_supports( $post_type, AMP_Post_Type_Support::SLUG ) ) {
			return;
		}

		( new URLValidationProvider() )
			->get_url_validation( get_the_permalink( $post_id ), get_post_type() );
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
