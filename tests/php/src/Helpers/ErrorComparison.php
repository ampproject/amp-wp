<?php

namespace AmpProject\AmpWP\Tests\Helpers;

use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use ReflectionClass;

/**
 * Compare produced errors while disregarding their specific representation.
 *
 * @package AmpProject\AmpWP
 */
trait ErrorComparison {

	/**
	 * Assert that two sets of errors are the same.
	 *
	 * @param ErrorCollection|Error[] $expected_errors Set of expected errors.
	 * @param ErrorCollection|Error[] $actual_errors   Set of actual errors.
	 */
	protected function assertSameErrors( $expected_errors, $actual_errors ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		$this->assertCount(
			count( $expected_errors ),
			$actual_errors,
			'Unexpected number of errors'
		);

		if ( $expected_errors instanceof ErrorCollection ) {
			$expected_errors = iterator_to_array( $expected_errors, false );
		}

		if ( $actual_errors instanceof ErrorCollection ) {
			$actual_errors = iterator_to_array( $actual_errors, false );
		}

		$expected_count = count( $expected_errors );
		for ( $index = 0; $index < $expected_count; $index ++ ) {
			$expected_error = $expected_errors[ $index ];
			$actual_error   = $actual_errors[ $index ];

			if ( is_string( $expected_error ) ) {
				// If strings were passed, assume the error code is used.
				$this->assertInstanceOf(
					$expected_error,
					$actual_error,
					'Unexpected error instance type'
				);

				$this->assertEquals(
					( new ReflectionClass( $actual_error ) )->getShortName(),
					$actual_error->getCode(),
					'Unexpected error code'
				);
			} else {
				$this->assertInstanceOf(
					get_class( $expected_error ),
					$actual_error,
					'Unexpected error type'
				);

				$this->assertEquals(
					$expected_error->getCode(),
					$actual_error->getCode(),
					'Unexpected error code'
				);

				$this->assertEquals(
					$expected_error->getMessage(),
					$actual_error->getMessage(),
					'Unexpected error message'
				);
			}
		}
	}
}
