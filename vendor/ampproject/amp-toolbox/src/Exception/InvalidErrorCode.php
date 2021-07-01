<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid error code is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidErrorCode extends OutOfRangeException implements AmpException
{

    /**
     * Instantiate an InvalidErrorCode exception for an unknown error code.
     *
     * @param string $errorCode Error code that was requested.
     * @return self
     */
    public static function forErrorCode($errorCode)
    {
        $message = "Invalid error code '{$errorCode}' was requested from the validator spec.";

        return new self($message);
    }
}
