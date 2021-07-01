<?php

namespace AmpProject\Cli;

use AmpProject\Exception\AmpCliException;
use AmpProject\Exception\Cli\InvalidSapi;
use Exception;

/**
 * Abstract class with the individual log levels.
 *
 * @package ampproject/amp-toolbox
 */
abstract class LogLevel
{

    /**
     * Detailed debug information.
     *
     * @var string
     */
    const DEBUG = 'debug';

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @var string
     */
    const INFO = 'info';

    /**
     * Normal but significant events.
     *
     * @var string
     */
    const NOTICE = 'notice';

    /**
     * Normal, positive outcome.
     *
     * @var string
     */
    const SUCCESS = 'success';

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
     *
     * @var string
     */
    const WARNING = 'warning';

    /**
     * Runtime errors that do not require immediate action but should typically be logged and monitored.
     *
     * @var string
     */
    const ERROR = 'error';

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @var string
     */
    const CRITICAL = 'critical';

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
     *
     * @var string
     */
    const ALERT = 'alert';

    /**
     * System is unusable.
     *
     * @var string
     */
    const EMERGENCY = 'emergency';

    /**
     * Ordering to use for log levels.
     *
     * @var string[]
     */
    const ORDER = [
        self::DEBUG,
        self::INFO,
        self::NOTICE,
        self::SUCCESS,
        self::WARNING,
        self::ERROR,
        self::CRITICAL,
        self::ALERT,
        self::EMERGENCY,
    ];

    /**
     * Test whether a given log level matches the currently set threshold.
     *
     * @param string $logLevel Log level to check.
     * @param string $threshold Log level threshold to check against.
     * @return bool Whether the provided log level matches the threshold.
     */
    public static function matches($logLevel, $threshold)
    {
        return array_search($logLevel, self::ORDER, true) >= array_search($threshold, self::ORDER, true);
    }
}
