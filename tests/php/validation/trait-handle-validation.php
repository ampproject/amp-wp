<?php

trait AMP_Test_HandleValidation {

	/**
	 * Whether or not to enable auto-sanitization if AMP is not canonical.
	 *
	 * @param bool $value Value to return when AMP_Validation_Manager::is_sanitization_auto_accepted() is called.
	 * @return void
	 */
	private function auto_accept_sanitization( $value ) {
		remove_all_filters( 'amp_is_sanitization_auto_accepted' );

		add_filter(
			'amp_is_sanitization_auto_accepted',
			static function () use ( $value ) {
				return $value;
			}
		);
	}
}
