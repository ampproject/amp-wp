<?php

namespace AmpProject\Cli;

use AmpProject\Exception\AmpCliException;
use AmpProject\Exception\Cli\InvalidSapi;
use Exception;

/**
 * This file is adapted from the splitbrain\php-cli library, which is authored by Andreas Gohr <andi@splitbrain.org> and
 * licensed under the MIT license.
 *
 * Source: https://github.com/splitbrain/php-cli/blob/fb4f888866d090b10e3e68292d197ca274cea626/src/CLI.php
 */

/**
 * Your commandline script should inherit from this class and implement the abstract methods.
 *
 * @package ampproject/amp-toolbox
 */
abstract class Executable
{

    /**
     * Instance of the Colors helper object.
     *
     * @var Colors
     */
    public $colors;

    /**
     * The executable script itself.
     *
     * @var string
     */
    protected $bin;

    /**
     * Instance of the options parser to use.
     *
     * @var Options
     */
    protected $options;

    /**
     * PSR-3 compatible log levels and their prefix, color, output channel.
     *
     * @var array<array>
     */
    protected $loglevels = [
        LogLevel::DEBUG     => ['', Colors::C_RESET, STDOUT],
        LogLevel::INFO      => ['ℹ ', Colors::C_CYAN, STDOUT],
        LogLevel::NOTICE    => ['☛ ', Colors::C_CYAN, STDOUT],
        LogLevel::SUCCESS   => ['✓ ', Colors::C_GREEN, STDOUT],
        LogLevel::WARNING   => ['⚠ ', Colors::C_BROWN, STDERR],
        LogLevel::ERROR     => ['✗ ', Colors::C_RED, STDERR],
        LogLevel::CRITICAL  => ['☠ ', Colors::C_LIGHTRED, STDERR],
        LogLevel::ALERT     => ['✖ ', Colors::C_LIGHTRED, STDERR],
        LogLevel::EMERGENCY => ['✘ ', Colors::C_LIGHTRED, STDERR],
    ];

    /**
     * Default log level.
     *
     * @var string
     */
    protected $loglevel = 'info';

    /**
     * Constructor.
     *
     * Initialize the arguments, set up helper classes and set up the CLI environment.
     *
     * @param bool         $autocatch Optional. Whether exceptions should be caught and handled automatically. Defaults
     *                                to true.
     * @param Options|null $options   Optional. Instance of the Options object to use. Defaults to null to instantiate a
     *                                new one.
     * @param Colors|null  $colors    Optional. Instance of the Colors object to use. Defaults to null to instantiate a
     *                                new one.
     */
    public function __construct($autocatch = true, Options $options = null, Colors $colors = null)
    {
        if ($autocatch) {
            set_exception_handler([$this, 'fatal']);
        }

        $this->colors  = $colors instanceof Colors ? $colors : new Colors();
        $this->options = $options instanceof Options ? $options : new Options($this->colors);
    }

    /**
     * Execute the CLI program.
     *
     * Executes the setup() routine, adds default options, initiate the options parsing and argument checking
     * and finally executes main() - Each part is split into their own protected function below, so behaviour
     * can easily be overwritten.
     *
     * @param bool $exitOnCompletion Optional. Whether to exit on completion. Defaults to true.
     * @throws InvalidSapi If a SAPI other than 'cli' is detected.
     */
    public function run($exitOnCompletion = true)
    {
        $sapi = php_sapi_name();

        if ('cli' !== $sapi) {
            throw InvalidSapi::forSapi($sapi);
        }

        $this->setup($this->options);
        $this->registerDefaultOptions();
        $this->parseOptions();
        $this->handleDefaultOptions();
        $this->setupLogging();
        $this->checkArguments();
        $this->execute();

        if ($exitOnCompletion) {
            exit(0);
        }
    }

    /**
     * Exits the program on a fatal error.
     *
     * @param Exception|string $error   Either an exception or an error message.
     * @param array            $context Optional. Associative array of contextual information. Defaults to an empty
     *                                  array.
     */
    public function fatal($error, array $context = [])
    {
        $code = 0;

        if ($error instanceof Exception) {
            $this->debug(get_class($error) . ' caught in ' . $error->getFile() . ':' . $error->getLine());
            $this->debug($error->getTraceAsString());
            $code  = $error->getCode();
            $error = $error->getMessage();
        }

        if (! $code) {
            $code = AmpCliException::E_ANY;
        }

        $this->critical($error, $context);

        exit($code);
    }

    /**
     * System is unusable.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal, positive outcome.
     *
     * @param string $string
     * @param array  $context
     * @return void
     */
    public function success($string, array $context = [])
    {
        $this->log(LogLevel::SUCCESS, $string, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log a message of a given log level to the logs.
     *
     * @param string $level   Log level to use.
     * @param string $message Log message.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if (! LogLevel::matches($level, $this->options->getOption('loglevel', $this->loglevel))) {
            return;
        }

        list($prefix, $color, $channel) = $this->loglevels[$level];

        if (! $this->colors->isEnabled()) {
            $prefix = '';
        }

        $message = $this->interpolate($message, $context);

        $this->colors->line($prefix . $message, $color, $channel);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message Message to interpolate.
     * @param array  $context Optional. Contextual information. Defaults to an empty array.
     * @return string Interpolated string.
     */
    protected function interpolate($message, array $context = [])
    {
        // Build a replacement array with braces around the context keys.
        $replace = [];
        foreach ($context as $key => $val) {
            // Check that the value can be cast to string.
            if (
                ! is_array($val)
                &&
                (
                    ! is_object($val)
                    || method_exists($val, '__toString')
                )
            ) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // Interpolate replacement values into the message and return.
        return strtr($message, $replace);
    }

    /**
     * Add the default help, color and log options.
     */
    protected function registerDefaultOptions()
    {
        $this->options->registerOption(
            'help',
            'Display this help screen and exit immediately.',
            'h'
        );
        $this->options->registerOption(
            'no-colors',
            'Do not use any colors in output. Useful when piping output to other tools or files.'
        );
        $this->options->registerOption(
            'loglevel',
            "Minimum level of messages to display. Default is {$this->colors->wrap($this->loglevel, Colors::C_CYAN)}."
            . ' Valid levels are: debug, info, notice, success, warning, error, critical, alert, emergency.',
            null,
            'level'
        );
    }

    /**
     * Handle the default options.
     */
    protected function handleDefaultOptions()
    {
        if ($this->options->getOption('no-colors')) {
            $this->colors->disable();
        }
        if ($this->options->getOption('help')) {
            echo $this->options->help();
            exit(0);
        }
    }

    /**
     * Handle the logging options.
     */
    protected function setupLogging()
    {
        $this->loglevel = $this->options->getOption('loglevel', $this->loglevel);

        if (! in_array($this->loglevel, LogLevel::ORDER)) {
            $this->fatal('Unknown log level');
        }
    }

    /**
     * Wrapper around the option parsing.
     */
    protected function parseOptions()
    {
        $this->options->parseOptions();
    }

    /**
     * Wrapper around the argument checking.
     */
    protected function checkArguments()
    {
        $this->options->checkArguments();
    }

    /**
     * Wrapper around main.
     */
    protected function execute()
    {
        $this->main($this->options);
    }

    /**
     * Register options and arguments on the given $options object.
     *
     * @param Options $options
     * @return void
     */
    abstract protected function setup(Options $options);

    /**
     * Main program routine.
     *
     * Arguments and options have been parsed when this is run.
     *
     * @param Options $options
     * @return void
     */
    abstract protected function main(Options $options);
}
