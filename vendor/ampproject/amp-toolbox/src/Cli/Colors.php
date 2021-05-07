<?php

namespace AmpProject\Cli;

use AmpProject\Exception\Cli\InvalidColor;

/**
 * This file is adapted from the splitbrain\php-cli library, which is authored by Andreas Gohr <andi@splitbrain.org> and
 * licensed under the MIT license.
 *
 * Source: https://github.com/splitbrain/php-cli/blob/8c2c001b1b55d194402cf18aad2757049ac6d575/src/Colors.php
 */

/**
 * Handles color output on (Unix) terminals.
 *
 * @package ampproject/amp-toolbox
 */
class Colors
{

    const C_BLACK       = 'black';
    const C_BLUE        = 'blue';
    const C_BROWN       = 'brown';
    const C_CYAN        = 'cyan';
    const C_DARKGRAY    = 'darkgray';
    const C_GREEN       = 'green';
    const C_LIGHTBLUE   = 'lightblue';
    const C_LIGHTCYAN   = 'lightcyan';
    const C_LIGHTGRAY   = 'lightgray';
    const C_LIGHTGREEN  = 'lightgreen';
    const C_LIGHTPURPLE = 'lightpurple';
    const C_LIGHTRED    = 'lightred';
    const C_PURPLE      = 'purple';
    const C_RED         = 'red';
    const C_RESET       = 'reset';
    const C_WHITE       = 'white';
    const C_YELLOW      = 'yellow';

    /**
     * Associative array of known color names.
     *
     * @var array<string>
     */
    const KNOWN_COLORS = [
        self::C_RESET       => "\33[0m",
        self::C_BLACK       => "\33[0;30m",
        self::C_DARKGRAY    => "\33[1;30m",
        self::C_BLUE        => "\33[0;34m",
        self::C_LIGHTBLUE   => "\33[1;34m",
        self::C_GREEN       => "\33[0;32m",
        self::C_LIGHTGREEN  => "\33[1;32m",
        self::C_CYAN        => "\33[0;36m",
        self::C_LIGHTCYAN   => "\33[1;36m",
        self::C_RED         => "\33[0;31m",
        self::C_LIGHTRED    => "\33[1;31m",
        self::C_PURPLE      => "\33[0;35m",
        self::C_LIGHTPURPLE => "\33[1;35m",
        self::C_BROWN       => "\33[0;33m",
        self::C_YELLOW      => "\33[1;33m",
        self::C_LIGHTGRAY   => "\33[0;37m",
        self::C_WHITE       => "\33[1;37m",
    ];

    /**
     * Whether colors should be used.
     *
     * @var bool
     */
    protected $enabled = true;

    /**
     * Constructor.
     *
     * Tries to disable colors for non-terminals.
     */
    public function __construct()
    {
        $this->enabled = getenv('TERM') || (function_exists('posix_isatty') && posix_isatty(STDOUT));
    }

    /**
     * Enable color output.
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable color output.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Check whether color support is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Convenience function to print a line in a given color.
     *
     * @param string   $line    The line to print. A new line is added automatically.
     * @param string   $color   One of the available color names.
     * @param resource $channel Optional. File descriptor to write to. Defaults to STDOUT.
     * @throws InvalidColor If the requested color code is not known.
     */
    public function line($line, $color, $channel = STDOUT)
    {
        $this->set($color);
        fwrite($channel, rtrim($line) . "\n");
        $this->reset();
    }

    /**
     * Returns the given text wrapped in the appropriate color and reset code
     *
     * @param string $text  String to wrap.
     * @param string $color One of the available color names.
     * @return string The wrapped string.
     * @throws InvalidColor If the requested color code is not known.
     */
    public function wrap($text, $color)
    {
        return $this->getColorCode($color) . $text . $this->getColorCode(self::C_RESET);
    }

    /**
     * Gets the appropriate terminal code for the given color.
     *
     * @param string $color One of the available color names.
     * @return string Color code.
     * @throws InvalidColor If the requested color code is not known.
     */
    public function getColorCode($color)
    {
        if (! array_key_exists($color, self::KNOWN_COLORS)) {
            throw InvalidColor::forUnknownColor($color);
        }

        if (! $this->enabled) {
            return '';
        }

        return self::KNOWN_COLORS[$color];
    }

    /**
     * Set the given color for consecutive output.
     *
     * @param string   $color   One of the supported color names.
     * @param resource $channel Optional. File descriptor to write to. Defaults to STDOUT.
     * @throws InvalidColor If the requested color code is not known.
     */
    public function set($color, $channel = STDOUT)
    {
        fwrite($channel, $this->getColorCode($color));
    }

    /**
     * Reset the terminal color.
     *
     * @param resource $channel Optional. File descriptor to write to. Defaults to STDOUT.
     */
    public function reset($channel = STDOUT)
    {
        $this->set(self::C_RESET, $channel);
    }
}
