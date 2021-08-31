<?php

namespace AmpProject\Cli;

use AmpProject\Exception\Cli\InvalidColumnFormat;

/**
 * This file is adapted from the splitbrain\php-cli library, which is authored by Andreas Gohr <andi@splitbrain.org> and
 * licensed under the MIT license.
 *
 * Source: https://github.com/splitbrain/php-cli/blob/8c2c001b1b55d194402cf18aad2757049ac6d575/src/TableFormatter.php
 */

/**
 * Output text in multiple columns.
 *
 * @package ampproject/amp-toolbox
 */
class TableFormatter
{

    /**
     * Border between columns.
     *
     * @var string
     */
    protected $border = ' ';

    /**
     * The terminal width in characters.
     *
     * Falls back to 74 characters if it cannot be detected.
     *
     * @var int
     */
    protected $maxWidth = 74;

    /**
     * Instance of the Colors helper object.
     *
     * @var Colors
     */
    protected $colors;

    /**
     * TableFormatter constructor.
     *
     * @param Colors|null $colors Optional. Instance of the Colors helper object.
     */
    public function __construct(Colors $colors = null)
    {
        // Try to get terminal width.
        $width = $this->getTerminalWidth();

        if ($width) {
            $this->maxWidth = $width - 1;
        }

        $this->colors = $colors instanceof Colors ? $colors : new Colors();
    }

    /**
     * The currently set border.
     *
     * Defaults to ' '.
     *
     * @return string
     */
    public function getBorder()
    {
        return $this->border;
    }

    /**
     * Set the border.
     *
     * The border is set between each column. Its width is added to the column widths.
     *
     * @param string $border Border to set.
     */
    public function setBorder($border)
    {
        $this->border = $border;
    }

    /**
     * Width of the terminal in characters.
     *
     * Initially auto-detected, with a fallback of 74 characters.
     *
     * @return int
     */
    public function getMaxWidth()
    {
        return $this->maxWidth;
    }

    /**
     * Set the width of the terminal to assume (in characters).
     *
     * @param int $maxWidth Terminal width in characters.
     */
    public function setMaxWidth($maxWidth)
    {
        $this->maxWidth = $maxWidth;
    }

    /**
     * Displays text in multiple word wrapped columns.
     *
     * @param array<int|string> $columns List of column widths (in characters, percent or '*').
     * @param array<string>     $texts   List of texts for each column.
     * @param array<string>     $colors  Optional. A list of color names to use for each column. Use empty string within
     *                                   the array for default. Defaults to an empty array.
     * @return string Adapted text.
     */
    public function format($columns, $texts, $colors = [])
    {
        $columns    = $this->calculateColumnWidths($columns);
        $wrapped    = [];
        $maxLength  = 0;

        foreach ($columns as $column => $width) {
            $wrapped[$column] = explode("\n", $this->wordwrap($texts[$column], $width, "\n", true));
            $length           = count($wrapped[$column]);
            if ($length > $maxLength) {
                $maxLength = $length;
            }
        }

        $last   = count($columns) - 1;
        $output = '';
        for ($index = 0; $index < $maxLength; $index++) {
            foreach ($columns as $column => $width) {
                if (isset($wrapped[$column][$index])) {
                    $value = $wrapped[$column][$index];
                } else {
                    $value = '';
                }
                $chunk = $this->pad($value, $width);
                if (isset($colors[$column]) && $colors[$column]) {
                    $chunk = $this->colors->wrap($chunk, $colors[$column]);
                }
                $output .= $chunk;

                // Add border in-between columns.
                if ($column != $last) {
                    $output .= $this->border;
                }
            }
            $output .= "\n";
        }

        return $output;
    }

    /**
     * Tries to figure out the width of the terminal.
     *
     * @return int Terminal width, 0 if unknown.
     */
    protected function getTerminalWidth()
    {
        // From environment.
        if (isset($_SERVER['COLUMNS'])) {
            return (int)$_SERVER['COLUMNS'];
        }

        // Via tput.
        $process = proc_open(
            'tput cols',
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        $width = (int)stream_get_contents($pipes[1]);

        proc_close($process);

        return $width;
    }

    /**
     * Takes an array with dynamic column width and calculates the correct widths.
     *
     * Column width can be given as fixed char widths, percentages and a single * width can be given
     * for taking the remaining available space. When mixing percentages and fixed widths, percentages
     * refer to the remaining space after allocating the fixed width.
     *
     * @param array $columns Columns to calculate the widths for.
     * @return int[] Array of calculated column widths.
     * @throws InvalidColumnFormat If the column format is not valid.
     */
    protected function calculateColumnWidths($columns)
    {
        $index  = 0;
        $border = $this->strlen($this->border);
        $fixed  = (count($columns) - 1) * $border; // Borders are used already.
        $fluid  = -1;

        // First pass for format check and fixed columns.
        foreach ($columns as $index => $column) {
            // Handle fixed columns.
            if ((string)intval($column) === (string)$column) {
                $fixed += $column;
                continue;
            }
            // Check if other columns are using proper units.
            if (substr($column, -1) === '%') {
                continue;
            }
            if ($column === '*') {
                // Only one fluid.
                if ($fluid < 0) {
                    $fluid = $index;
                    continue;
                } else {
                    throw InvalidColumnFormat::forMultipleFluidColumns();
                }
            }
            throw InvalidColumnFormat::forUnknownColumnFormat($column);
        }

        $allocated  = $fixed;
        $remain     = $this->maxWidth - $allocated;

        // Second pass to handle percentages.
        foreach ($columns as $index => $column) {
            if (substr($column, -1) !== '%') {
                continue;
            }
            $percent = floatval($column);

            $real = (int)floor(($percent * $remain) / 100);

            $columns[$index] = $real;
            $allocated       += $real;
        }

        $remain = $this->maxWidth - $allocated;
        if ($remain < 0) {
            throw InvalidColumnFormat::forExceededMaxWidth();
        }

        // Assign remaining space.
        if ($fluid < 0) {
            $columns[$index] += ($remain); // Add to last column.
        } else {
            $columns[$fluid] = $remain;
        }

        return $columns;
    }

    /**
     * Pad the given string to the correct length.
     *
     * @param string $string String to pad.
     * @param int    $length Length to pad the string to.
     * @return string Padded string.
     */
    protected function pad($string, $length)
    {
        $strlen = $this->strlen($string);

        if ($strlen > $length) {
            return $string;
        }

        $pad = $length - $strlen;

        return $string . str_pad('', $pad, ' ');
    }

    /**
     * Measures character length in UTF-8 when possible.
     *
     * @param string $string String to measure the character length of.
     * @return int Count of characters.
     */
    protected function strlen($string)
    {
        // Don't count color codes.
        $string = preg_replace("/\33\\[\\d+(;\\d+)?m/", '', $string);

        if (function_exists('mb_strlen')) {
            return mb_strlen($string, 'utf-8');
        }

        return strlen($string);
    }

    /**
     * Extract a substring in UTF-8 if possible.
     * @param string   $string String to extract a substring out of.
     * @param int      $start  Optional. Starting index to extract from. Defaults to 0.
     * @param int|null $length Optional. Length to extract. Set to null to use the remainder of the string (default).
     * @return string Extracted substring.
     */
    protected function substr($string, $start = 0, $length = null)
    {
        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length);
        }

        // mb_substr() treats $length differently than substr().
        if ($length) {
            return substr($string, $start, $length);
        }

        return substr($string, $start);
    }

    /**
     * Wrap words of a string into a requested width.
     *
     * @param string $string String to wrap.
     * @param int    $width  Optional. Width to warp the string into. Defaults to 75.
     * @param string $break  Optional. Character to use for wrapping. Defaults to a newline character. Defaults to the
     *                       newline character.
     * @param bool   $cut    Optional. Whether to cut longer words to enforce the width. Defaults to false.
     * @return string Word-wrapped string.
     * @link http://stackoverflow.com/a/4988494
     */
    protected function wordwrap($string, $width = 75, $break = "\n", $cut = false)
    {
        $lines = explode($break, $string);
        foreach ($lines as &$line) {
            $line = rtrim($line);
            if ($this->strlen($line) <= $width) {
                continue;
            }
            $words  = explode(' ', $line);
            $line   = '';
            $actual = '';
            foreach ($words as $word) {
                if ($this->strlen($actual . $word) <= $width) {
                    $actual .= $word . ' ';
                } else {
                    if ($actual != '') {
                        $line .= rtrim($actual) . $break;
                    }
                    $actual = $word;
                    if ($cut) {
                        while ($this->strlen($actual) > $width) {
                            $line   .= $this->substr($actual, 0, $width) . $break;
                            $actual = $this->substr($actual, $width);
                        }
                    }
                    $actual .= ' ';
                }
            }
            $line .= trim($actual);
        }

        return implode($break, $lines);
    }
}
