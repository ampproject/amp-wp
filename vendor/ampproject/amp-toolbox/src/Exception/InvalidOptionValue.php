<?php

namespace AmpProject\Exception;

use DomainException;

/**
 * Exception thrown when an invalid option value was provided.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidOptionValue extends DomainException implements AmpException
{
    /**
     * Instantiate an InvalidOptionValue exception for an invalid option value.
     *
     * @param string        $option   Name of the option.
     * @param array<string> $accepted Array of acceptable values.
     * @param string        $actual   Value that was actually provided.
     * @return self
     */
    public static function forValue($option, $accepted, $actual)
    {
        $acceptedString = implode(', ', $accepted);
        $message = "The value for the option '{$option}' expected the value to be one of "
                   . "[{$acceptedString}], got '{$actual}' instead.";

        return new self($message);
    }
}
