<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid format is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidFormat extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidFormat exception when an invalid AMP format is being requested.
     *
     * @param string $format Format that was requested.
     * @return self
     */
    public static function forFormat($format)
    {
        $message = "Invalid format '{$format}' was requested from the validator spec.";

        return new self($message);
    }
}
