<?php

namespace AmpProject\Exception\Cli;

use AmpProject\Exception\AmpCliException;
use InvalidArgumentException;

/**
 * Exception thrown when an invalid command was provided to the CLI.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidCommand extends InvalidArgumentException implements AmpCliException
{

    /**
     * Instantiate an InvalidCommand exception for an unregistered command that is being referenced.
     *
     * @param string $command Unregistered command that is being referenced.
     * @return self
     */
    public static function forUnregisteredCommand($command)
    {
        $message = "Command not registered: '{$command}'.";

        return new self($message, AmpCliException::E_INVALID_CMD);
    }


    /**
     * Instantiate an InvalidCommand exception for an already registered command that is to be re-registered.
     *
     * @param string $command Already registered command that is supposed to be registered.
     * @return self
     */
    public static function forAlreadyRegisteredCommand($command)
    {
        $message = "Command already registered: '{$command}'.";

        return new self($message, AmpCliException::E_INVALID_CMD);
    }
}
