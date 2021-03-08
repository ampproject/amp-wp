<?php
/**
 * Single cron event to validate a saved post's permalink in the background.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\SingleScheduledBackgroundTask;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Conditional;

/**
 * SavePostValidationEvent class.
 *
 * @since 2.1
 *
 * @internal
 */
final class SavePostValidationEvent extends SingleScheduledBackgroundTask implements Conditional {

	/**
	 * Instance of URLValidationProvider
	 *
	 * @var URLValidationProvider
	 */
	private $url_validation_provider;

	/**
	 * Instance of UserAccess.
	 *
	 * @var UserAccess
	 */
	private $dev_tools_user_access;

	/**
	 * The cron action name.
	 *
	 * @var string
	 */
	const BACKGROUND_TASK_NAME = 'amp_single_post_validate';

	/**
	 * Check whether the service is currently needed.
	 *
	 * @return bool Whether needed.
	 */
	public static function is_needed() {
		return URLValidationCron::is_needed();
	}

	/**
	 * Class constructor.
	 *
	 * @param BackgroundTaskDeactivator $background_task_deactivator Background task deactivator instance.
	 * @param UserAccess                $dev_tools_user_access Dev tools user access class instance.
	 * @param URLValidationProvider     $url_validation_provider URLValidationProvider instance.
	 */
	public function __construct( BackgroundTaskDeactivator $background_task_deactivator, UserAccess $dev_tools_user_access, URLValidationProvider $url_validation_provider ) {
		parent::__construct( $background_task_deactivator );

		$this->dev_tools_user_access   = $dev_tools_user_access;
		$this->url_validation_provider = $url_validation_provider;
	}

	/**
	 * Callback for the cron action.
	 *
	 * @param mixed ...$args The args received with the action hook where the event was scheduled.
	 */
	public function process( ...$args ) {
		$post_id = reset( $args );

		if ( empty( $post_id ) || empty( get_post( $post_id ) ) ) {
			return;
		}

		$this->url_validation_provider->get_url_validation(
			get_the_permalink( $post_id ),
			get_post_type( $post_id )
		);
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
		return self::BACKGROUND_TASK_NAME;
	}

	/**
	 * Returns whether the event should be scheduled.
	 *
	 * @param array $args Args passed from the action hook where the event is scheduled.
	 * @return boolean
	 */
	protected function should_schedule_event( $args ) {
		if ( ! is_array( $args ) || count( $args ) !== 1 ) {
			return false;
		}

		// Validation is performed on post save if user has dev tools on.
		if ( $this->dev_tools_user_access->is_user_enabled( wp_get_current_user() ) ) {
			return false;
		}

		$id = reset( $args );
		if ( empty( $id ) ) {
			return false;
		}

		$post = get_post( $id );

		// @todo This needs to be limited to when the status is publish because otherwise the validation request will fail to be able to access the post, as the request is not authenticated.
		if ( ! $post
			||
			wp_is_post_revision( $post )
			||
			wp_is_post_autosave( $post )
			||
			'auto-draft' === $post->post_status
			||
			'trash' === $post->post_status
		) {
			return false;
		}

		if ( ! amp_is_post_supported( $id ) ) {
			return false;
		}

		return true;
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
