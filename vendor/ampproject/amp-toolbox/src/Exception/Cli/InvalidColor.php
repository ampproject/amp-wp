<?php

namespace AmpProject\Exception\Cli;

use AmpProject\Exception\AmpCliException;
use OutOfBoundsException;

/**
 * Exception thrown when an invalid color was provided to the CLI.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidColor extends OutOfBoundsException implements AmpCliException
{
    /**
     * Instantiate an InvalidColor exception for an unknown color that was passed to the CLI.
     *
     * @param string $color Unknown color that was passed to the CLI.
     * @return self
     */
    public static function forUnknownColor($color)
    {
        $message = "Unknown color: '{$color}'.";

        return new self($message, AmpCliException::E_ANY);
    }
}
