<?php

namespace AmpProject\Cli;

use AmpProject\Exception\Cli\InvalidArgument;
use AmpProject\Exception\Cli\InvalidCommand;
use AmpProject\Exception\Cli\InvalidOption;
use AmpProject\Exception\Cli\MissingArgument;

/**
 * This file is adapted from the splitbrain\php-cli library, which is authored by Andreas Gohr <andi@splitbrain.org> and
 * licensed under the MIT license.
 *
 * Source: https://github.com/splitbrain/php-cli/blob/8c2c001b1b55d194402cf18aad2757049ac6d575/src/Options.php
 */

/**
 * Parses command line options passed to the CLI script. Allows CLI scripts to easily register all accepted options and
 * commands and even generates a help text from this setup.
 *
 * @package ampproject/amp-toolbox
 */
class Options
{
    /**
     * List of options to parse.
     *
     * @var array
     */
    protected $setup;

    /**
     * Storage for parsed options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Currently parsed command if any.
     *
     * @var string
     */
    protected $command = '';

    /**
     * Passed non-option arguments.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Name of the executed script.
     *
     * @var string
     */
    protected $bin;

    /**
     * Instance of the Colors helper object.
     *
     * @var Colors
     */
    protected $colors;

    /**
     * Newline used for spacing help texts.
     *
     * @var string
     */
    protected $newline = "\n";

    /**
     * Constructor.
     *
     * @param Colors $colors Optional. Configured color object.
     * @throws InvalidArgument When arguments can't be read.
     */
    public function __construct(Colors $colors = null)
    {
        $this->colors = $colors instanceof Colors ? $colors : new Colors();

        $this->setup = [
            '' => [
                'options'     => [],
                'arguments'   => [],
                'help'        => '',
                'commandHelp' => 'This tool accepts a command as first parameter as outlined below:',
            ],
        ]; // Default command.

        $this->arguments = $this->readPHPArgv();
        $this->bin       = basename(array_shift($this->arguments));

        $this->options = [];
    }

    /**
     * Gets the name of the binary that was executed.
     *
     * @return string Name of the binary that was executed.
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Sets the help text for the tool itself.
     *
     * @param string $help Help text to set.
     */
    public function setHelp($help)
    {
        $this->setup['']['help'] = $help;
    }

    /**
     * Sets the help text for the tools commands itself.
     *
     * @param string $help Help text to set.
     */
    public function setCommandHelp($help)
    {
        $this->setup['']['commandHelp'] = $help;
    }

    /**
     * Use a more compact help screen with less new lines.
     *
     * @param bool $set Optional. Whether to set compact help or not. Defaults to true.
     */
    public function useCompactHelp($set = true)
    {
        $this->newline = $set ? '' : "\n";
    }

    /**
     * Register the names of arguments for help generation and number checking.
     *
     * This has to be called in the order arguments are expected.
     *
     * @param string $name     Name of the argument.
     * @param string $help     Help text.
     * @param bool   $required Optional. Whether this argument is required. Defaults to true.
     * @param string $command  Optional. Command this argument applies to. Empty string (default) for global arguments.
     * @throws InvalidCommand If the referenced command is not registered.
     */
    public function registerArgument($name, $help, $required = true, $command = '')
    {
        if (! isset($this->setup[$command])) {
            throw InvalidCommand::forUnregisteredCommand($command);
        }

        $this->setup[$command]['arguments'][] = [
            'name'     => $name,
            'help'     => $help,
            'required' => $required,
        ];
    }

    /**
     * Register a sub command.
     *
     * Sub commands have their own options and use their own function (not main()).
     *
     * @param string $name Name of the command to register.
     * @param string $help Help text of the command.
     * @throws InvalidCommand If the referenced command is already registered.
     */
    public function registerCommand($name, $help)
    {
        if (isset($this->setup[$name])) {
            throw InvalidCommand::forAlreadyRegisteredCommand($name);
        }

        $this->setup[$name] = [
            'options'   => [],
            'arguments' => [],
            'help'      => $help,
        ];
    }

    /**
     * Register an option for option parsing and help generation.
     *
     * @param string      $long          Multi character option (specified with --).
     * @param string      $help          Help text for this option.
     * @param string|null $short         Optional. One character option (specified with -). Disable with null (default).
     * @param bool|string $needsArgument Optional. Whether this option requires an argument. Use a boolean value, or
     *                                   provide a string to require a specific argument by name. Defaults to false.
     * @param string      $command       Optional. Name of the command this option applies to. Use an empty string for
     *                                   none (default).
     * @throws InvalidCommand  If the referenced command is not registered.
     * @throws InvalidArgument If the short option is too long.
     */
    public function registerOption($long, $help, $short = null, $needsArgument = false, $command = '')
    {
        if (! isset($this->setup[$command])) {
            throw InvalidCommand::forUnregisteredCommand($command);
        }

        $this->setup[$command]['options'][$long] = [
            'needsArgument' => $needsArgument,
            'help'          => $help,
            'short'         => $short,
        ];

        if ($short) {
            if (strlen($short) > 1) {
                throw InvalidArgument::forMultiCharacterShortOption();
            }

            $this->setup[$command]['short'][$short] = $long;
        }
    }

    /**
     * Checks the actual number of arguments against the required number.
     *
     * This is run from CLI automatically and usually does not need to be called directly.
     *
     * @throws MissingArgument If not enough arguments were provided.
     */
    public function checkArguments()
    {
        $argumentCount = count($this->arguments);

        $required = 0;
        foreach ($this->setup[$this->command]['arguments'] as $argument) {
            if (! $argument['required']) {
                break;
            } // Last required arguments seen.
            $required++;
        }

        if ($required > $argumentCount) {
            throw MissingArgument::forNotEnoughArguments();
        }
    }

    /**
     * Parses the given arguments for known options and command.
     *
     * The given $arguments array should NOT contain the executed file as first item anymore! The $arguments
     * array is stripped from any options and possible command. All found options can be accessed via the
     * getOptions() function.
     *
     * Note that command options will overwrite any global options with the same name.
     *
     * This is run from CLI automatically and usually does not need to be called directly.
     *
     * @throws InvalidOption   If an unknown option was provided.
     * @throws MissingArgument If an argument is missing.
     */
    public function parseOptions()
    {
        $nonOptions = [];

        $argumentCount = count($this->arguments);
        for ($index = 0; $index < $argumentCount; $index++) {
            $argument = $this->arguments[$index];

            // The special element '--' means explicit end of options. Treat the rest of the arguments as non-options
            // and end the loop.
            if ($argument == '--') {
                $nonOptions = array_merge($nonOptions, array_slice($this->arguments, $index + 1));
                break;
            }

            // '-' is stdin - a normal argument.
            if ($argument == '-') {
                $nonOptions = array_merge($nonOptions, array_slice($this->arguments, $index));
                break;
            }

            // First non-option.
            if ($argument[0] != '-') {
                $nonOptions = array_merge($nonOptions, array_slice($this->arguments, $index));
                break;
            }

            // Long option.
            if (strlen($argument) > 1 && $argument[1] === '-') {
                $argument = explode('=', substr($argument, 2), 2);
                $option   = array_shift($argument);
                $value    = array_shift($argument);

                if (! isset($this->setup[$this->command]['options'][$option])) {
                    throw InvalidOption::forUnknownOption($option);
                }

                // Argument required?
                if ($this->setup[$this->command]['options'][$option]['needsArgument']) {
                    if (
                        is_null($value) && $index + 1 < $argumentCount && ! preg_match(
                            '/^--?[\w]/',
                            $this->arguments[$index + 1]
                        )
                    ) {
                        $value = $this->arguments[++$index];
                    }
                    if (is_null($value)) {
                        throw MissingArgument::forNoArgument($option);
                    }
                    $this->options[$option] = $value;
                } else {
                    $this->options[$option] = true;
                }

                continue;
            }

            // Short option.
            $option = substr($argument, 1);
            if (! isset($this->setup[$this->command]['short'][$option])) {
                throw InvalidOption::forUnknownOption($option);
            } else {
                $option = $this->setup[$this->command]['short'][$option]; // Store it under long name.
            }

            // Argument required?
            if ($this->setup[$this->command]['options'][$option]['needsArgument']) {
                $value = null;
                if ($index + 1 < $argumentCount && ! preg_match('/^--?[\w]/', $this->arguments[$index + 1])) {
                    $value = $this->arguments[++$index];
                }
                if (is_null($value)) {
                    throw MissingArgument::forNoArgument($option);
                }
                $this->options[$option] = $value;
            } else {
                $this->options[$option] = true;
            }
        }

        // Parsing is now done, update arguments array.
        $this->arguments = $nonOptions;

        // If not done yet, check if first argument is a command and re-execute argument parsing if it is.
        if (! $this->command && $this->arguments && isset($this->setup[$this->arguments[0]])) {
            // It is a command!
            $this->command = array_shift($this->arguments);
            $this->parseOptions(); // Second pass.
        }
    }

    /**
     * Get the value of the given option.
     *
     * Please note that all options are accessed by their long option names regardless of how they were
     * specified on commandline.
     *
     * Can only be used after parseOptions() has been run.
     *
     * @param string      $option  Option to get.
     * @param bool|string $default Optional. Default value to return if the option is not set. Defaults to false.
     * @return bool|string Value of the option.
     */
    public function getOption($option, $default = false)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return $default;
    }

    /**
     * Get all options.
     *
     * Please note that all options are accessed by their long option names regardless of how they were
     * specified on commandline.
     *
     * Can only be used after parseOptions() has been run.
     *
     * @return string[] Associative array of all options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return the found command, if any.
     *
     * @return string Name of the command that was found.
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get all the arguments passed to the script.
     *
     * This will not contain any recognized options or the script name itself.
     *
     * @return array Associative array of arguments.
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Builds a help screen from the available options.
     *
     * You may want to call it from -h or on error.
     *
     * @return string Help screen text.
     */
    public function help()
    {
        $tableFormatter = new TableFormatter($this->colors);
        $text           = '';

        $hasCommands = (count($this->setup) > 1);
        $commandHelp = $this->setup['']['commandHelp'];

        foreach ($this->setup as $command => $config) {
            $hasOptions   = (bool)$this->setup[$command]['options'];
            $hasArguments = (bool)$this->setup[$command]['arguments'];

            // Usage or command syntax line.
            if (! $command) {
                $text        .= $this->colors->wrap('USAGE:', Colors::C_BROWN);
                $text        .= "\n";
                $text        .= '   ' . $this->bin;
                $indentation = 2;
            } else {
                $text        .= $this->newline;
                $text        .= $this->colors->wrap('   ' . $command, Colors::C_PURPLE);
                $indentation = 4;
            }

            if ($hasOptions) {
                $text .= ' ' . $this->colors->wrap('<OPTIONS>', Colors::C_GREEN);
            }

            if (! $command && $hasCommands) {
                $text .= ' ' . $this->colors->wrap('<COMMAND> ...', Colors::C_PURPLE);
            }

            foreach ($this->setup[$command]['arguments'] as $argument) {
                $output = $this->colors->wrap('<' . $argument['name'] . '>', Colors::C_CYAN);

                if (! $argument['required']) {
                    $output = '[' . $output . ']';
                }
                $text .= ' ' . $output;
            }
            $text .= $this->newline;

            // Usage or command intro.
            if ($this->setup[$command]['help']) {
                $text .= "\n";
                $text .= $tableFormatter->format(
                    [$indentation, '*'],
                    ['', $this->setup[$command]['help'] . $this->newline]
                );
            }

            // Option description.
            if ($hasOptions) {
                if (! $command) {
                    $text .= "\n";
                    $text .= $this->colors->wrap('OPTIONS:', Colors::C_BROWN);
                }
                $text .= "\n";
                foreach ($this->setup[$command]['options'] as $long => $option) {
                    $name = '';
                    if ($option['short']) {
                        $name .= '-' . $option['short'];
                        if ($option['needsArgument']) {
                            $name .= ' <' . $option['needsArgument'] . '>';
                        }
                        $name .= ', ';
                    }
                    $name .= "--$long";
                    if ($option['needsArgument']) {
                        $name .= ' <' . $option['needsArgument'] . '>';
                    }

                    $text .= $tableFormatter->format(
                        [$indentation, '30%', '*'],
                        ['', $name, $option['help']],
                        ['', 'green', '']
                    );
                    $text .= $this->newline;
                }
            }

            // Argument description.
            if ($hasArguments) {
                if (! $command) {
                    $text .= "\n";
                    $text .= $this->colors->wrap('ARGUMENTS:', Colors::C_BROWN);
                }
                $text .= $this->newline;
                foreach ($this->setup[$command]['arguments'] as $argument) {
                    $name = '<' . $argument['name'] . '>';

                    $text .= $tableFormatter->format(
                        [$indentation, '30%', '*'],
                        ['', $name, $argument['help']],
                        ['', 'cyan', '']
                    );
                }
            }

            // Headline and intro for following command documentation.
            if (! $command && $hasCommands) {
                $text .= "\n";
                $text .= $this->colors->wrap('COMMANDS:', Colors::C_BROWN);
                $text .= "\n";
                $text .= $tableFormatter->format(
                    [$indentation, '*'],
                    ['', $commandHelp]
                );
                $text .= $this->newline;
            }
        }

        return $text;
    }

    /**
     * Safely read the $argv PHP array across different PHP configurations.
     * Will take care of register_globals and register_argc_argv ini directives.
     *
     * @return array The $argv PHP array.
     * @throws InvalidArgument If the $argv array could not be read.
     */
    private function readPHPArgv()
    {
        global $argv;

        if (is_array($argv)) {
            return $argv;
        }

        if (
            is_array($_SERVER)
            &&
            array_key_exists('argv', $_SERVER)
            &&
            is_array($_SERVER['argv'])
        ) {
            return $_SERVER['argv'];
        }

        if (
            array_key_exists('HTTP_SERVER_VARS', $GLOBALS)
            &&
            is_array($GLOBALS['HTTP_SERVER_VARS'])
            &&
            array_key_exists('argv', $GLOBALS['HTTP_SERVER_VARS'])
            &&
            is_array($GLOBALS['HTTP_SERVER_VARS']['argv'])
        ) {
            return $GLOBALS['HTTP_SERVER_VARS']['argv'];
        }

        throw InvalidArgument::forUnreadableArguments();
    }
}
