<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid document ruleset name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidDocRulesetName extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidDocRulesetName exception for a document ruleset that is not found within the document
     * rulesets index.
     *
     * @param string $docRulesetName document ruleset name that was requested.
     * @return self
     */
    public static function forDocRulesetName($docRulesetName)
    {
        $message = "Invalid document ruleset name '{$docRulesetName}' was requested from the validator spec.";

        return new self($message);
    }
}
