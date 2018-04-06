<?php
/**
 * Class AMP_Response_Headers
 *
 * @since 1.0
 * @package AMP
 */

/**
 * Class AMP_Response_Headers
 */
class AMP_Response_Headers {

	/**
	 * Headers sent (or attempted to be sent).
	 *
	 * @since 1.0
	 * @see AMP_Theme_Support::send_header()
	 * @var array[]
	 */
	public static $headers_sent = array();

	/**
	 * Send an HTTP response header.
	 *
	 * This largely exists to facilitate unit testing but it also provides a better interface for sending headers.
	 *
	 * @since 0.7.0
	 *
	 * @param string $name  Header name.
	 * @param string $value Header value.
	 * @param array  $args {
	 *     Args to header().
	 *
	 *     @type bool $replace     Whether to replace a header previously sent. Default true.
	 *     @type int  $status_code Status code to send with the sent header.
	 * }
	 * @return bool Whether the header was sent.
	 */
	public static function send_header( $name, $value, $args = array() ) {
		$args = array_merge(
			array(
				'replace'     => true,
				'status_code' => null,
			),
			$args
		);

		self::$headers_sent[] = array_merge( compact( 'name', 'value' ), $args );
		if ( headers_sent() ) {
			return false;
		}

		header(
			sprintf( '%s: %s', $name, $value ),
			$args['replace'],
			$args['status_code']
		);
		return true;
	}

	/**
	 * Send Server-Timing header.
	 *
	 * @since 1.0
	 * @todo What is the ordering in Chrome dev tools? What are the colors about?
	 * @todo Is there a better name standardization?
	 * @todo Is there a way to indicate nested server timings, so an outer method's own time can be seen separately from the inner method's time?
	 *
	 * @param string $name        Name.
	 * @param float  $duration    Duration. If negative, will be added to microtime( true ). Optional.
	 * @param string $description Description. Optional.
	 * @return bool Return value of send_header call.
	 */
	public static function send_server_timing( $name, $duration = null, $description = null ) {
		$value = $name;
		if ( isset( $description ) ) {
			$value .= sprintf( ';desc=%s', wp_json_encode( $description ) );
		}
		if ( isset( $duration ) ) {
			if ( $duration < 0 ) {
				$duration = microtime( true ) + $duration;
			}
			$value .= sprintf( ';dur=%f', $duration * 1000 );
		}
		return self::send_header( 'Server-Timing', $value, array( 'replace' => false ) );
	}
}
