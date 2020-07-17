<?php
/**
 * Trait AssertRestApiField.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

/**
 * Helper trait for testing REST API fields on post types.
 *
 * @package AmpProject\AmpWP
 */
trait AssertRestApiField {

	/**
	 * Asserts that the post types have the additional REST field.
	 *
	 * @param string[] $post_types Post types to run assertions against.
	 * @param string   $expected_field_name Expected REST API field name.
	 * @param array    $expected_field_args Expected arguments for REST API field.
	 */
	protected function assertRestApiFieldPresent( $post_types, $expected_field_name, $expected_field_args ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		foreach ( $post_types as $post_type ) {
			$this->assertTrue( isset( $GLOBALS['wp_rest_additional_fields'][ $post_type ][ $expected_field_name ] ) );
			$field = $GLOBALS['wp_rest_additional_fields'][ $post_type ][ $expected_field_name ];

			$this->assertEquals( $expected_field_args['schema'], $field['schema'] );
			$this->assertEquals( $expected_field_args['get_callback'], $field['get_callback'] );

			if ( isset( $expected_field_args['update_callback'] ) ) {
				$this->assertTrue( isset( $field['update_callback'] ) );
				$this->assertEquals( $expected_field_args['update_callback'], $field['update_callback'] );
			}
		}
	}
}
