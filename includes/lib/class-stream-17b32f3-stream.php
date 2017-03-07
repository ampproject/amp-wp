<?php

require_once( AMP__DIR__ . '/includes/lib/class-stream-17b32f3-stream-buffer-too-small-exception.php' );
require_once( AMP__DIR__ . '/includes/lib/interface-stream-17b32f3-streamable-interface.php' );

/**
 * Class Stream
 *
 * @package FasterImage
 */
class Stream_17b32f3_Stream implements Stream_17b32f3_Streamable_Interface {

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
	public function peek( $characters ) {
		if ( strlen( $this->stream_string ) < $this->strpos + $characters ) {
			throw new Stream_17b32f3_Stream_Buffer_Too_Small_Exception( 'Not enough of the stream available.' );
		}

		return substr( $this->stream_string, $this->strpos, $characters );
	}

	/**
	 * Get Characters from the string
	 *
	 * @param $characters
	 *
	 * @return string
	 * @throws StreamBufferTooSmallException
	 */
	public function read( $characters ) {
		$result = $this->peek( $characters );

		$this->strpos += $characters;

		return $result;
	}

	/**
	 * Resets the pointer to the 0 position
	 *
	 * @return mixed
	 */
	public function reset_pointer() {
		$this->strpos = 0;
	}

	/**
	 * Append to the stream string
	 *
	 * @param $string
	 */
	public function write( $string ) {
		$this->stream_string .= $string;
	}
}
