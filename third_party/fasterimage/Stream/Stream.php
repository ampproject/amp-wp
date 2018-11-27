<?php namespace WillWashburn\Stream;

use WillWashburn\Stream\Exception\StreamBufferTooSmallException;


/**
 * Class Stream
 *
 * @package FasterImage
 */
class Stream implements StreamableInterface
{
    /**
     * The string that we have downloaded so far
     */
    protected $stream_string = '';

    /**
     * The pointer in the string
     *
     * @var int
     */
    protected $strpos = 0;

    /**
     * Get characters from the string but don't move the pointer
     *
     * @param $characters
     *
     * @return string
     * @throws StreamBufferTooSmallException
     */
    public function peek($characters)
    {
        if ( strlen($this->stream_string) < $this->strpos + $characters ) {
            throw new StreamBufferTooSmallException('Not enough of the stream available.');
        }

        return substr($this->stream_string, $this->strpos, $characters);
    }

    /**
     * Get Characters from the string
     *
     * @param $characters
     *
     * @return string
     * @throws StreamBufferTooSmallException
     */
    public function read($characters)
    {
        $result = $this->peek($characters);

        $this->strpos += $characters;

        return $result;
    }

    /**
     * Resets the pointer to the 0 position
     *
     * @return mixed
     */
    public function resetPointer()
    {
        $this->strpos = 0;
    }

    /**
     * Append to the stream string
     *
     * @param $string
     */
    public function write($string)
    {
        $this->stream_string .= $string;
    }
}