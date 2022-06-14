<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid attribute name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidAttributeName extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidAttributeName exception for an attribute that is not found within name index.
     *
     * @param string $attribute Name of the attribute that was requested.
     * @return self
     */
    public static function forAttribute($attribute)
    {
        $message = "Invalid attribute '{$attribute}' was requested from the validator spec.";

        return new self($message);
    }
}
