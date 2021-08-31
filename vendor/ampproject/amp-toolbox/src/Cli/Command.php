<?php

namespace AmpProject\Cli;

/**
 * A command that is registered with the amp executable.
 *
 * @package AmpProject\Cli
 */
abstract class Command
{

    /**
     * Name of the command.
     *
     * This needs to be overridden in extending commands.
     *
     * @var string
     */
    const NAME = '<unknown>';

    /**
     * Get the name of the command.
     *
     * @return string Name of the command.
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * Register the command.
     *
     * @param Options $options Options instance to register the command with.
     */
    abstract public function register(Options $options);

    /**
     * Process the command.
     *
     * Arguments and options have been parsed when this is run.
     *
     * @param Options $options Options instance to process the command with.
     */
    abstract public function process(Options $options);
}
