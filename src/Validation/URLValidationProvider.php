<?php
/**
 * Provides URL validation.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validation_Error_Taxonomy;
use AMP_Validation_Manager;
use WP_Error;

/**
 * URLValidationProvider class.
 *
 * @since 2.1
 * @internal
 */
final class URLValidationProvider {
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
	 * @see AMP_Validation_Manager::validate_url_and_store()
	 *
	 * @param string $url  The URL to validate.
	 * @param string $type The type of template, post, or taxonomy.
	 * @return array|WP_Error Associative array containing validity result or a WP_Error on failure.
	 */
	public function get_url_validation( $url, $type ) {
		$validity = AMP_Validation_Manager::validate_url_and_store( $url );
		if ( is_wp_error( $validity ) ) {
			return $validity;
		}

		$this->update_state_from_validity( $validity, $type );
		return $validity;
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
