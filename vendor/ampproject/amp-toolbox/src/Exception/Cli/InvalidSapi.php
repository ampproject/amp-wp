<?php

namespace AmpProject\Exception\Cli;

use AmpProject\Exception\AmpCliException;
use OutOfBoundsException;

/**
 * Exception thrown when an invalid option was provided to the CLI.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidSapi extends OutOfBoundsException implements AmpCliException
{
    /**
     * Instantiate an InvalidSapi exception for a SAPI other than 'cli'.
     *
     * @param string $sapi Invalid SAPI that was detected.
     * @return self
     */
    public static function forSapi($sapi)
    {
        $message = "This has to be run from the command line (detected SAPI '{$sapi}').";

        return new self($message, AmpCliException::E_ANY);
    }
}
