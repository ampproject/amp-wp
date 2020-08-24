<?php
/**
 * Provides URL validation.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validated_URL_Post_Type;
use AMP_Validation_Error_Taxonomy;
use AMP_Validation_Manager;
use WP_Error;

/**
 * URLValidationProvider class.
 *
 * @since 2.1
 */
final class URLValidationProvider {

	/**
	 * Key for the transient signaling validation is locked.
	 *
	 * @var string
	 */
	const LOCK_TRANSIENT = 'amp_validation_locked';

	/**
	 * Flag to pass to get_url_validation to force revalidation.
	 *
	 * @param string
	 */
	const FLAG_FORCE_REVALIDATE = 'amp_force_revalidate';

	/**
	 * Flag to pass to get_url_validation to skip revalidation.
	 *
	 * @param string
	 */
	const FLAG_NO_REVALIDATE = 'amp_no_revalidate';

	/**
	 * The total number of validation errors, regardless of whether they were accepted.
	 *
	 * @var int
	 */
	public $total_errors = 0;

	/**
	 * The total number of unaccepted validation errors.
	 *
	 * If an error has been accepted in the /wp-admin validation UI,
	 * it won't count toward this.
	 *
	 * @var int
	 */
	public $unaccepted_errors = 0;

	/**
	 * The number of URLs crawled, regardless of whether they have validation errors.
	 *
	 * @var int
	 */
	public $number_crawled = 0;

	/**
	 * The validation counts by type, like template or post type.
	 *
	 * @var array[] {
	 *     Validity by type.
	 *
	 *     @type array $type {
	 *         @type int $valid The number of valid URLs for this type.
	 *         @type int $total The total number of URLs for this type, valid or invalid.
	 *     }
	 * }
	 */
	public $validity_by_type = [];

	/**
	 * Locks validation.
	 */
	private function lock() {
		set_transient( self::LOCK_TRANSIENT, 'locked', $this->get_lock_timeout() );
	}

	/**
	 * Unlocks validation.
	 */
	private function unlock() {
		delete_transient( self::LOCK_TRANSIENT );
	}

	/**
	 * Returns whether validation is currently locked.
	 *
	 * @return boolean
	 */
	public function is_locked() {
		return get_transient( self::LOCK_TRANSIENT );
	}

	/**
	 * Provides the length of time, in seconds, to lock validation when this runs.
	 *
	 * @return int
	 */
	private function get_lock_timeout() {
		/**
		 * Filters the length of time to lock URL validation when a process starts.
		 *
		 * @param int $timeout Time in seconds. Default 300 seconds.
		 */
		return apply_filters( 'amp_validation_lock_timeout', 5 * MINUTE_IN_SECONDS );
	}

	/**
	 * Runs a callback with a lock set for the duration of the callback.
	 *
	 * @param callable $callback Callback to run with the lock set.
	 * @return mixed  WP_Error if a lock is in place. Otherwise, the result of the callback or void if it doesn't return anything.
	 */
	public function with_lock( $callback ) {
		if ( $this->is_locked() ) {
			return new WP_Error(
				'amp_url_validation_locked',
				__( 'URL validation cannot start right now because another process is already validating URLs. Try again in a few minutes.', 'amp' )
			);
		}

		$this->lock();
		$result = call_user_func( $callback );
		$this->unlock();

		return $result;
	}

	/**
	 * Validates a URL, stores the results, and increments the counts.
	 *
	 * @param string $url  The URL to validate.
	 * @param string $type The type of template, post, or taxonomy.
	 * @param string $flag Flag determining whether the URL should be revalidated.
	 * @return array Associative array containing validity results along with error info and whether the URL was revalidated.
	 */
	public function get_url_validation( $url, $type, $flag = null ) {
		$validity    = null;
		$revalidated = true;
		$error       = null;

		if ( self::FLAG_FORCE_REVALIDATE !== $flag ) {
			$url_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( $url );

			if ( $url_post && empty( AMP_Validated_URL_Post_Type::get_post_staleness( $url_post ) ) ) {
				$validity    = AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $url_post );
				$revalidated = false;
			}
		}

		if ( self::FLAG_NO_REVALIDATE !== $flag && ( is_null( $validity ) || self::FLAG_FORCE_REVALIDATE === true ) ) {
			$validity = AMP_Validation_Manager::validate_url_and_store( $url );
		}

		if ( is_wp_error( $validity ) ) {
			$validity = null;
			$error    = $validity;
		} elseif ( $validity && isset( $validity['results'] ) ) {
			$this->update_state_from_validity( $validity, $type );
		}

		return compact( 'validity', 'revalidated', 'error' );
	}

	/**
	 * Increments crawl counts from a validation result.
	 *
	 * @param array  $validity Validity results.
	 * @param string $type The URL type.
	 */
	private function update_state_from_validity( $validity, $type ) {
		$validation_errors      = wp_list_pluck( $validity['results'], 'error' );
		$unaccepted_error_count = count(
			array_filter(
				$validation_errors,
				static function( $error ) {
					$validation_status = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $error );
					return (
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS !== $validation_status['term_status']
					&&
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS !== $validation_status['term_status']
					);
				}
			)
		);

		if ( count( $validation_errors ) > 0 ) {
			$this->total_errors++;
		}
		if ( $unaccepted_error_count > 0 ) {
			$this->unaccepted_errors++;
		}

		$this->number_crawled++;

		if ( ! isset( $this->validity_by_type[ $type ] ) ) {
			$this->validity_by_type[ $type ] = [
				'valid' => 0,
				'total' => 0,
			];
		}
			$this->validity_by_type[ $type ]['total']++;
		if ( 0 === $unaccepted_error_count ) {
			$this->validity_by_type[ $type ]['valid']++;
		}
	}
}
