<?php

namespace AmpProject\Exception\Cli;

use AmpProject\Exception\AmpCliException;
use DomainException;

/**
 * Exception thrown when an invalid argument was provided to the CLI.
 *
 * @package ampproject/amp-toolbox
 */
final class MissingArgument extends DomainException implements AmpCliException
{

    /**
     * Instantiate a MissingArgument exception for an argument that is required but missing.
     *
     * @param string $option Option for which the argument is missing.
     *
     * @return self
     */
    public static function forNoArgument($option)
    {
        $message = "Option '{$option}' requires an argument.";

        return new self($message, AmpCliException::E_OPT_ARG_REQUIRED);
    }

    /**
     * Instantiate a MissingArgument exception for when too few arguments were passed.
     *
     * @return self
     */
    public static function forNotEnoughArguments()
    {
        $message = 'Not enough arguments provided.';

        return new self($message, AmpCliException::E_OPT_ARG_REQUIRED);
    }
}
