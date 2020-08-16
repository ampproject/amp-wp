<?php
/**
 * Class ValidatedUrlStylesheetDataGarbageCollection.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AMP_Validated_URL_Post_Type;

/**
 * Delete stylesheet data from amp_validated_url posts which have not been validated in a week.
 *
 * This background task will update the oldest 100 amp_validated_url posts each time it runs, excluding URLs that have
 * been validated within the past week. The batch size of 100 follows the lead of `_wp_batch_update_comment_type()` in
 * WordPress 5.5. Deleting data from posts older than 1 week follows the lead of `wp_delete_auto_drafts()`.
 *
 * @since 2.0
 * @see _wp_batch_update_comment_type()
 * @see wp_delete_auto_drafts()
 *
 * @link https://github.com/ampproject/amp-wp/issues/5132
 * @package AmpProject\AmpWP
 * @internal
 */
final class ValidatedUrlStylesheetDataGarbageCollection extends CronBasedBackgroundTask {

	/**
	 * Name of the event to schedule.
	 *
	 * @var string
	 */
	const EVENT_NAME = 'amp_validated_url_stylesheet_gc';

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name.
	 */
	protected function get_interval() {
		return self::DEFAULT_INTERVAL_HOURLY;
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
	 * @return void
	 */
	public function process() {
		AMP_Validated_URL_Post_Type::delete_stylesheets_postmeta_batch( 100, '1 week ago' );
	}
}
