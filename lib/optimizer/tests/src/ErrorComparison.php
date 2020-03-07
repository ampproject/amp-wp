<?php

namespace Amp\Optimizer\Tests;

use Amp\Optimizer\Error;
use Amp\Optimizer\ErrorCollection;
use ReflectionClass;

/**
 * Compare produced errors while disregarding their specific representation.
 *
 * @package amp/optimizer
 */
trait ErrorComparison
{

    /**
     * Assert that two sets of errors are the same.
     *
     * @param ErrorCollection|Error[] $expectedErrors Set of expected errors.
     * @param ErrorCollection|Error[] $actualErrors   Set of actual errors.
     */
    protected function assertSameErrors($expectedErrors, $actualErrors)
    {
        $this->assertCount(count($expectedErrors), $actualErrors, 'Unexpected number of errors');

        if ($expectedErrors instanceof ErrorCollection) {
            $expectedErrors = iterator_to_array($expectedErrors);
        }

        if ($actualErrors instanceof ErrorCollection) {
            $actualErrors = iterator_to_array($actualErrors);
        }

        for ($index = 0; $index < count($expectedErrors); $index++) {
            $expectedError = $expectedErrors[$index];
            $actualError   = $actualErrors[$index];
            if ( is_string( $expectedError ) ) {
                // If strings were passed, assume the error code is used.
                $this->assertInstanceOf($expectedError, $actualError, 'Unexpected error instance type');
                $this->assertEquals((new ReflectionClass( $actualError ))->getShortName(), $actualError->getCode(), 'Unexpected error code');
            } else {
                $this->assertInstanceOf(get_class($expectedError), $actualError, 'Unexpected error type');
                $this->assertEquals($expectedError->getCode(), $actualError->getCode(), 'Unexpected error code');
                $this->assertEquals($expectedError->getMessage(), $actualError->getMessage(), 'Unexpected error message');
            }
        }
    }
}
