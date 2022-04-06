<?php

namespace AmpProject\Validator;

/**
 * Position into a file, in line and column coordinates.
 *
 * @package ampproject/amp-toolbox
 */
final class FilePosition
{
    /**
     * Line position into the file.
     *
     * @var int
     */
    private $line;

    /**
     * Column position into the file.
     *
     * @var int
     */
    private $column;

    /**
     * Instantiate a FilePosition object.
     *
     * @param int $line   Line position into the file.
     * @param int $column Column position into the file.
     */
    public function __construct($line, $column)
    {
        $this->line   = $line;
        $this->column = $column;
    }

    /**
     * Get the line position into the file.
     *
     * @return int Line position into the file.
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Get the column position into the file.
     *
     * @return int Column position into the file.
     */
    public function getColumn()
    {
        return $this->column;
    }
}
