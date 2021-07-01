<?php

namespace AmpProject\Exception\Cli;

use AmpProject\Exception\AmpCliException;
use InvalidArgumentException;

/**
 * Exception thrown when an invalid argument was provided to the CLI.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidArgument extends InvalidArgumentException implements AmpCliException
{

    /**
     * Instantiate an InvalidArgument exception when arguments could not be read.
     *
     * @return self
     */
    public static function forUnreadableArguments()
    {
        $message = 'Could not read command arguments. Is register_argc_argv off?';

        return new self($message, AmpCliException::E_ARG_READ);
    }

    /**
     * Instantiate an InvalidArgument exception when a short option is too long.
     *
     * @return self
     */
    public static function forMultiCharacterShortOption()
    {
        $message = 'Short options should be exactly one ASCII character.';

        return new self($message, AmpCliException::E_OPT_ARG_DENIED);
    }

    /**
     * Instantiate an InvalidArgument exception for file that could not be read.
     *
     * @param string $file File that could not be read.
     * @return self
     */
    public static function forUnreadableFile($file)
    {
        $message = "Could not read file: '{$file}'.";

        return new self($message, AmpCliException::E_OPT_ARG_DENIED);
    }
}
