<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid spec name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidSpecName extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidSpecName exception for a spec that is not found within the spec name index.
     *
     * @param string $specName Spec name that was requested.
     * @return self
     */
    public static function forSpecName($specName)
    {
        $message = "Invalid spec name '{$specName}' was requested from the validator spec.";

        return new self($message);
    }
}
