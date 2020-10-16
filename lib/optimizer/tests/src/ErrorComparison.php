<?php

namespace AmpProject\Optimizer\Tests;

use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use ReflectionClass;

/**
 * Compare produced errors while disregarding their specific representation.
 *
 * @package ampproject/optimizer
 */
trait ErrorComparison
{

    /**
     * Assert that two sets of errors are the same.
     *
     * @param ErrorCollection|Error[] $expected_errors Set of expected errors.
     * @param ErrorCollection|Error[] $actualErrors    Set of actual errors.
     */
    protected function assertSameErrors( $expected_errors, $actualErrors)
    {
        $this->assertCount(count($expected_errors), $actualErrors, 'Unexpected number of errors');

        if ( $expected_errors instanceof ErrorCollection) {
            $expected_errors = iterator_to_array($expected_errors, false);
        }

        if ($actualErrors instanceof ErrorCollection) {
            $actualErrors = iterator_to_array($actualErrors, false);
        }

        $expectedCount = count($expected_errors);
        for ($index = 0; $index < $expectedCount; $index++) {
            $expectedError = $expected_errors[ $index];
            $actualError   = $actualErrors[$index];
            if (is_string($expectedError)) {
                // If strings were passed, assume the error code is used.
                $this->assertInstanceOf($expectedError, $actualError, 'Unexpected error instance type');
                $this->assertEquals((new ReflectionClass($actualError))->getShortName(), $actualError->getCode(), 'Unexpected error code');
            } else {
                $this->assertInstanceOf(get_class($expectedError), $actualError, 'Unexpected error type');
                $this->assertEquals($expectedError->getCode(), $actualError->getCode(), 'Unexpected error code');
                $this->assertEquals($expectedError->getMessage(), $actualError->getMessage(), 'Unexpected error message');
            }
        }
    }
}
