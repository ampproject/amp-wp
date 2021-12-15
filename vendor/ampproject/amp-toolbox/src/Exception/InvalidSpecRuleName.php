<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid spec rule name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidSpecRuleName extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidSpecRuleName exception for a spec rule that is not found within the spec index.
     *
     * @param string $specRuleName Spec rule name that was requested.
     * @return self
     */
    public static function forSpecRuleName($specRuleName)
    {
        $message = "Invalid spec rule name '{$specRuleName}' was requested from the validator spec.";

        return new self($message);
    }
}
