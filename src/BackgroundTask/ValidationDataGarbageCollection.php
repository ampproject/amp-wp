<?php
/**
 * Class ValidationDataGarbageCollection.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AMP_Validated_URL_Post_Type;
use AMP_Validation_Error_Taxonomy;

/**
 * Garbage collect validation data.
 *
 * This scheduled event removes validation data (amp_validated_url posts and amp_validation_error terms) which no longer
 * have any need to be retained. The batch size of 100 follows the lead of `_wp_batch_update_comment_type()` in
 * WordPress 5.5. Deleting data from posts older than 1 week follows the lead of `wp_delete_auto_drafts()`.
 *
 * @since 2.2
 * @see _wp_batch_update_comment_type()
 * @see wp_delete_auto_drafts()
 *
 * @link https://github.com/ampproject/amp-wp/issues/4779
 * @package AmpProject\AmpWP
 * @internal
 */
final class ValidationDataGarbageCollection extends RecurringBackgroundTask {

	/**
	 * Name of the event to schedule.
	 *
	 * @var string
	 */
	const EVENT_NAME = 'amp_validation_data_gc';

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name.
	 */
	protected function get_interval() {
		return self::DEFAULT_INTERVAL_DAILY;
	}

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
		return self::EVENT_NAME;
	}

	/**
	 * Process a single cron tick.
	 *
	 * @param mixed[] ...$args Unused callback arguments.
	 * @return void
	 */
	public function process( ...$args ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		AMP_Validated_URL_Post_Type::garbage_collect_validated_urls( 100, '1 week ago' );

		// Finally, delete validation errors which may now be empty.
		AMP_Validation_Error_Taxonomy::delete_empty_terms();
	}
}
