<?php

namespace AmpProject\Optimizer\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid HTML attribute was detected.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidArgument extends InvalidArgumentException implements AmpOptimizerException
{

    /**
     * Instantiate an InvalidArgument exception for an invalid argument type for numeric comparison.
     *
     * @param mixed $argument Argument that was of an invalid type.
     * @return self
     */
    public static function forNumericComparison($argument)
    {
        $type    = is_object($argument) ? get_class($argument) : gettype($argument);
        $message = "Invalid argument type '{$type}' provided for a numeric comparison.";

        return new self($message);
    }
}
