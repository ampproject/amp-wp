<?php
/**
 * File containing helper trait for validation.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

/**
 * Helper trait for validation
 */
trait HandleValidation {

	/**
	 * Whether or not to enable acceptance of sanitization by default.
	 *
	 * @param bool $value Value to return when AMP_Validation_Manager::is_sanitization_auto_accepted() is called.
	 * @return void
	 */
	private function accept_sanitization_by_default( $value ) {
		remove_all_filters( 'amp_validation_error_default_sanitized' );

		add_filter(
			'amp_validation_error_default_sanitized',
			static function () use ( $value ) {
				return $value;
			}
		);
	}
}
