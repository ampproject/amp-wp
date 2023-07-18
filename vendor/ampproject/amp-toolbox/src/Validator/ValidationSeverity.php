<?php

namespace AmpProject\Validator;

use AmpProject\FakeEnum;

/**
 * Severity of a validation error.
 *
 * @method static ValidationSeverity UNKNOWN_SEVERITY()
 * @method static ValidationSeverity ERROR()
 * @method static ValidationSeverity WARNING()
 *
 * @package ampproject/amp-toolbox
 *
 * @method static ValidationSeverity UNKNOWN_SEVERITY()
 * @method static ValidationSeverity ERROR()
 * @method static ValidationSeverity WARNING()
 */
final class ValidationSeverity extends FakeEnum
{
    const UNKNOWN_SEVERITY = 0;
    const ERROR            = 1;
    const WARNING          = 4;

    /**
     * Get the severity as an integer.
     *
     * @return int
     */
    public function asInt()
    {
        return (int)$this->value;
    }

    /**
     * Get the severity as a string.
     *
     * @return string
     */
    public function asString()
    {
        return (string)$this->getKey();
    }

    /**
     * Get the string representation of the severity.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }
}
