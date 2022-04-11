<?php

namespace AmpProject\Html\Parser;

use AmpProject\Str;

/**
 * Helper for determining the line/column information for SAX events that are being received by a HtmlSaxHandler.
 *
 * @package ampproject/amp-toolbox
 */
final class DocLocator
{
    /**
     * Line mapped to a given position.
     *
     * @var array
     */
    private $lineByPosition = [];

    /**
     * Column mapped to a given position.
     *
     * @var array
     */
    private $columnByPosition = [];

    /**
     * Size of the document in bytes.
     *
     * @var int
     */
    private $documentByteSize;

    /**
     * The current position in the htmlText.
     *
     * @var int
     */
    private $position = 0;

    /**
     * The previous position in the htmlText.
     *
     * We need this to know where a tag or attribute etc. started.
     *
     * @var int
     */
    private $previousPosition = 0;

    /**
     * Line within the document.
     *
     * @var int
     */
    private $line = 1;

    /**
     * Column within the document.
     *
     * @var int
     */
    private $column = 0;

    /**
     * DocLocator constructor.
     *
     * @param string $htmlText String of HTML.
     */
    public function __construct($htmlText)
    {
        /*
         * Precomputes a mapping from positions within htmlText to line / column numbers.
         *
         * TODO: This uses a fair amount of space and we can probably do better, but it's also quite simple so here we
         * are for now.
         */

        $currentLine   = 1;
        $currentColumn = 0;
        $length        = Str::length($htmlText);
        for ($index = 0; $index < $length; ++$index) {
            $this->lineByPosition[$index]   = $currentLine;
            $this->columnByPosition[$index] = $currentColumn;
            $character                      = Str::substring($htmlText, $index, 1);
            if ($character === "\n") {
                ++$currentLine;
                $currentColumn = 0;
            } else {
                ++$currentColumn;
            }
        }

        $this->documentByteSize = Str::length($htmlText);
    }

    /**
     * Advances the internal position by the characters in $tokenText.
     *
     * This method is to be called only from within the parser.
     *
     * @param string $tokenText The token text which we examine to advance the line / column location within the doc.
     */
    public function advancePosition($tokenText)
    {
        $this->previousPosition = $this->position;
        $this->position += Str::length($tokenText);
    }

    /**
     * Snapshots the previous internal position so that getLine / getCol will return it.
     *
     * These snapshots happen as the parser enter / exits a tag.
     *
     * This method is to be called only from within the parser.
     */
    public function snapshotPosition()
    {
        if ($this->previousPosition < count($this->lineByPosition)) {
            $this->line   = $this->lineByPosition[$this->previousPosition];
            $this->column = $this->columnByPosition[$this->previousPosition];
        }
    }

    /**
     * Get the current line in the HTML source from which the most recent SAX event was generated. This value is only
     * sensible once an event has been generated, that is, in practice from within the context of the HtmlSaxHandler
     * methods - e.g., startTag(), pcdata(), etc.
     *
     * @return int The current line.
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Get the current column in the HTML source from which the most recent SAX event was generated. This value is only
     * sensible once an event has been generated, that is, in practice from within the context of the HtmlSaxHandler
     * methods - e.g., startTag(), pcdata(), etc.
     *
     * @return int The current column.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Get the size of the document in bytes.
     *
     * @return int The size of the document in bytes.
     */
    public function getDocumentByteSize()
    {
        return $this->documentByteSize;
    }
}
