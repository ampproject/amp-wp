<?php

namespace AmpProject\Optimizer\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid configuration value was provided.
 *
 * @package ampproject/optimizer
 */
final class InvalidConfigurationValue extends InvalidArgumentException implements AmpOptimizerException
{

    /**
     * Instantiate an InvalidConfigurationValue exception for an invalid value type.
     *
     * @param string $key      Key that was invalid.
     * @param string $expected Value type that was expected.
     * @param string $actual   Value type that was actually provided.
     * @return self
     */
    public static function forInvalidValueType($key, $expected, $actual)
    {
        $message = "The configuration key '{$key}' expected a value of type '{$expected}', got '{$actual}' instead.";

        return new self($message);
    }

    /**
     * Instantiate an InvalidConfigurationValue exception for an invalid value type.
     *
     * @param string     $key      Key that was invalid.
     * @param string|int $index    Index of the sub-value that was invalid.
     * @param string     $expected Value type that was expected.
     * @param string     $actual   Value type that was actually provided.
     * @return self
     */
    public static function forInvalidSubValueType($key, $index, $expected, $actual)
    {
        $message = "The configuration value '{$index}' for the key '{$key}' expected a value of type '{$expected}', "
                   . "got '{$actual}' instead.";

        return new self($message);
    }
}
