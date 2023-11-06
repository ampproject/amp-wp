<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid CSS ruleset name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidCssRulesetName extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidCssRulesetName exception for a CSS ruleset that is not found within the CSS rulesets index.
     *
     * @param string $cssRulesetName CSS ruleset name that was requested.
     * @return self
     */
    public static function forCssRulesetName($cssRulesetName)
    {
        $message = "Invalid CSS ruleset name '{$cssRulesetName}' was requested from the validator spec.";

        return new self($message);
    }
}
