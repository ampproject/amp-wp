## Class `AmpProject\AmpWP\Validation\URLValidationProvider`

URLValidationProvider class.

### Methods

* [`is_locked`](../method/Validation/URLValidationProvider/is_locked.md) - Returns whether validation is currently locked.
* [`with_lock`](../method/Validation/URLValidationProvider/with_lock.md) - Runs a callback with a lock set for the duration of the callback.
* [`reset_lock`](../method/Validation/URLValidationProvider/reset_lock.md) - Resets the lock timeout. This allows long-running processes to keep running beyond the lock timeout.
* [`get_total_errors`](../method/Validation/URLValidationProvider/get_total_errors.md) - Provides the total number of validation errors found.
* [`get_unaccepted_errors`](../method/Validation/URLValidationProvider/get_unaccepted_errors.md) - Provides the total number of unaccepted errors.
* [`get_number_validated`](../method/Validation/URLValidationProvider/get_number_validated.md) - Provides the number of URLs that have been checked.
* [`get_validity_by_type`](../method/Validation/URLValidationProvider/get_validity_by_type.md) - Provides the validity counts by type.
* [`get_url_validation`](../method/Validation/URLValidationProvider/get_url_validation.md) - Validates a URL, stores the results, and increments the counts.
### Source

:link: [src/Validation/URLValidationProvider.php:21](/src/Validation/URLValidationProvider.php#L21-L243)

<details>
<summary>Show Code</summary>

```php
final class URLValidationProvider {

	/**
	 * Key for the transient signaling validation is locked.
	 *
	 * @var string
	 */
	const LOCK_KEY = 'amp_validation_locked';

	/**
	 * The length of time to keep the lock in place if a process fails to unlock.
	 *
	 * @var int
	 */
	const LOCK_TIMEOUT = 5 * MINUTE_IN_SECONDS;

	/**
	 * The total number of validation errors, regardless of whether they were accepted.
	 *
	 * @var int
	 */
	private $total_errors = 0;

	/**
	 * The total number of unaccepted validation errors.
	 *
	 * If an error has been accepted in the /wp-admin validation UI,
	 * it won't count toward this.
	 *
	 * @var int
	 */
	private $unaccepted_errors = 0;

	/**
	 * The number of URLs crawled, regardless of whether they have validation errors.
	 *
	 * @var int
	 */
	private $number_validated = 0;

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
	private $validity_by_type = [];

	/**
	 * Locks validation.
	 */
	private function lock() {
		update_option( self::LOCK_KEY, time(), false );
	}

	/**
	 * Unlocks validation.
	 */
	private function unlock() {
		delete_option( self::LOCK_KEY );
	}

	/**
	 * Returns whether validation is currently locked.
	 *
	 * @return boolean
	 */
	public function is_locked() {
		$lock_time = (int) get_option( self::LOCK_KEY, 0 );

		// It's locked if the difference between the lock time and the current time is less than the lockout time.
		return time() - $lock_time < self::LOCK_TIMEOUT;
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
		$result = $callback();
		$this->unlock();

		return $result;
	}

	/**
	 * Resets the lock timeout. This allows long-running processes to keep running beyond the lock timeout.
	 */
	public function reset_lock() {
		$this->lock();
	}

	/**
	 * Provides the total number of validation errors found.
	 *
	 * @return int
	 */
	public function get_total_errors() {
		return $this->total_errors;
	}

	/**
	 * Provides the total number of unaccepted errors.
	 *
	 * @return int
	 */
	public function get_unaccepted_errors() {
		return $this->unaccepted_errors;
	}

	/**
	 * Provides the number of URLs that have been checked.
	 *
	 * @return int
	 */
	public function get_number_validated() {
		return $this->number_validated;
	}

	/**
	 * Provides the validity counts by type.
	 *
	 * @return array[]
	 */
	public function get_validity_by_type() {
		return $this->validity_by_type;
	}

	/**
	 * Validates a URL, stores the results, and increments the counts.
	 *
	 * @param string $url  The URL to validate.
	 * @param string $type The type of template, post, or taxonomy.
	 * @param bool   $force_revalidate Whether to force revalidation regardless of whether the current results are stale.
	 * @return array|WP_Error Associative array containing validity result and whether the URL was revalidated, or a WP_Error on failure.
	 */
	public function get_url_validation( $url, $type, $force_revalidate = false ) {
		$validity    = null;
		$revalidated = true;

		if ( ! $force_revalidate ) {
			$url_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( $url );

			if ( $url_post && empty( AMP_Validated_URL_Post_Type::get_post_staleness( $url_post ) ) ) {
				$validity    = AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $url_post );
				$revalidated = false;
			}
		}

		if ( is_null( $validity ) ) {
			$validity = AMP_Validation_Manager::validate_url_and_store( $url );
		}

		if ( is_wp_error( $validity ) ) {
			return $validity;
		}

		if ( $validity && isset( $validity['results'] ) ) {
			$this->update_state_from_validity( $validity, $type );
		}

		return compact( 'validity', 'revalidated' );
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

		$this->number_validated++;

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
```

</details>
