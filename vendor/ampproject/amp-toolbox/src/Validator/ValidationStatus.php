<?php

namespace AmpProject\Validator;

use AmpProject\FakeEnum;

/**
 * Status of the validation run.
 *
 * @method static ValidationStatus UNKNOWN()
 * @method static ValidationStatus PASS()
 * @method static ValidationStatus FAIL()
 *
 * @package ampproject/amp-toolbox
 */
final class ValidationStatus extends FakeEnum
{
    const UNKNOWN = 0;
    const PASS    = 1;
    const FAIL    = 2;

    /**
     * Get the status as an integer.
     *
     * @return int
     */
    public function asInt()
    {
        return (int)$this->value;
    }

    /**
     * Get the status as a string.
     *
     * @return string
     */
    public function asString()
    {
        return (string)$this->getKey();
    }

    /**
     * Get the string representation of the status.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }
}
