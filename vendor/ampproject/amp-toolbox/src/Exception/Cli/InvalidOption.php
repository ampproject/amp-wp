<?php

namespace AmpProject\Exception\Cli;

use AmpProject\Exception\AmpCliException;
use OutOfBoundsException;

/**
 * Exception thrown when an invalid option was provided to the CLI.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidOption extends OutOfBoundsException implements AmpCliException
{
    /**
     * Instantiate an InvalidOption exception for an unknown option that was passed to the CLI.
     *
     * @param string $option Unknown option that was passed to the CLI.
     * @return self
     */
    public static function forUnknownOption($option)
    {
        $message = "Unknown option: '{$option}'.";

        return new self($message, AmpCliException::E_UNKNOWN_OPT);
    }
}
