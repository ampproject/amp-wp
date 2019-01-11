<?php namespace WillWashburn\Stream;

/**
 * Interface StreamableInterface
 *
 * @package FasterImage
 */
interface StreamableInterface {

    /**
     * Append to the stream string
     *
     * @param $string
     */
    public function write($string);

    /**
     * Get Characters from the string
     *
     * @param int $characters Number of characters to read.
     * @param bool $check_length Throw exception if there are not enough bytes left in the stream.
     */
    public function read($characters, $check_length=false);

    /**
     * Get characters from the string but don't move the pointer
     *
     * @param int $characters Number of characters to read.
     * @param bool $check_length Throw exception if there are not enough bytes left in the stream.
     *
     * @return mixed
     */
    public function peek($characters, $check_length=false);

    /**
     * Resets the pointer to the 0 position
     * @return mixed
     */
    public function resetPointer();

}
