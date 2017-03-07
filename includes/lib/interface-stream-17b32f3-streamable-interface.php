<?php

/**
 * Interface StreamableInterface
 *
 * @package FasterImage
 */
interface Stream_17b32f3_Streamable_Interface {

	/**
	 * Append to the stream string
	 *
	 * @param $string
	 */
	public function write( $string);

	/**
	 * Get Characters from the string
	 *
	 * @param $characters
	 */
	public function read( $characters);

	/**
	 * Get characters from the string but don't move the pointer
	 *
	 * @param $characters
	 *
	 * @return mixed
	 */
	public function peek( $characters);

	/**
	 * Resets the pointer to the 0 position
	 * @return mixed
	 */
	public function reset_pointer();

}
