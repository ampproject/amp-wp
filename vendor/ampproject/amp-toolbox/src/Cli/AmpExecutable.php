<?php

namespace AmpProject\Cli;

use AmpProject\Exception\Cli\InvalidCommand;

/**
 * Executable that assembles all of the commands.
 *
 * @package ampproject/amp-toolbox
 */
final class AmpExecutable extends Executable
{
    /**
     * Array of command classes to register.
     *
     * @var string[]
     */
    const COMMAND_CLASSES = [
        Command\Optimize::class,
        Command\Validate::class,
    ];

    /**
     * Array of command object instances.
     *
     * @var Command[]
     */
    private $commandInstances = [];

    /**
     * Register options and arguments on the given $options object.
     *
     * @param Options $options Options instance to register the commands with.
     * @return void
     */
    protected function setup(Options $options)
    {
        foreach (self::COMMAND_CLASSES as $commandClass) {
            /** @var Command $command */
            $command = new $commandClass($this);

            $command->register($options);

            $this->commandInstances[$command->getName()] = $command;
        }
    }

    /**
     * Your main program.
     *
     * Arguments and options have been parsed when this is run.
     *
     * @param Options $options Options instance to register the commands with.
     * @return void
     */
    protected function main(Options $options)
    {
        $commandName = $options->getCommand();

        if (empty($commandName)) {
            echo $this->options->help();
            exit(1);
        }

        if (! array_key_exists($commandName, $this->commandInstances)) {
            throw InvalidCommand::forUnregisteredCommand($commandName);
        }

        $command = $this->commandInstances[$commandName];

        $command->process($options);
    }
}
