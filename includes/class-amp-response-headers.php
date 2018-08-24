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
	 * If WP_DEBUG is not enabled and an admin user (who can manage_options) is not logged-in, the Server-Header will not be sent.
	 *
	 * @since 1.0
	 *
	 * @param string $name        Name.
	 * @param float  $duration    Duration. If negative, will be added to microtime( true ). Optional.
	 * @param string $description Description. Optional.
	 * @return bool Return value of send_header call. If WP_DEBUG is not enabled or admin user (who can manage_options) is not logged-in, this will always return false.
	 */
	public static function send_server_timing( $name, $duration = null, $description = null ) {
		if ( ! WP_DEBUG && ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$value = $name;
		if ( isset( $description ) ) {
			$value .= sprintf( ';desc="%s"', str_replace( array( '\\', '"' ), '', substr( $description, 0, 100 ) ) );
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
