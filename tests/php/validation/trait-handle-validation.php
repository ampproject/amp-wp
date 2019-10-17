<?php
/**
 * File containing helper trait for validation.
 *
 * @package AMP
 */

/**
 * Helper trait for validation
 */
trait AMP_Test_HandleValidation {

	/**
	 * Whether or not to enable auto-sanitization.
	 *
	 * @param bool $value Value to return when AMP_Validation_Manager::is_sanitization_auto_accepted() is called.
	 * @return void
	 */
	private function auto_accept_sanitization( $value ) {
		remove_all_filters( 'amp_validation_error_auto_sanitized' );

		add_filter(
			'amp_validation_error_auto_sanitized',
			static function () use ( $value ) {
				return $value;
			}
		);
	}
}
