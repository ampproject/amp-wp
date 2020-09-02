<?php
/**
 * Class ExtraThemeAndPluginHeaders.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Service;
use Exception;

/**
 * Produces an error page similar to wp_die().
 *
 * The actual wp_die() function cannot be used within the AMP response
 * preparation code, as its 'exit' argument is only usable from WP 5.1 onwards.
 *
 * @package AmpProject\AmpWP
 * @since   2.0.1
 * @internal
 */
final class ErrorPage implements Service {

	/**
	 * Title of the error page.
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * Message of the error page.
	 *
	 * @var string
	 */
	private $message = '';

	/**
	 * Exception of the error page.
	 *
	 * @var Exception
	 */
	private $exception;

	/**
	 * Response code of the error page.
	 *
	 * @var int
	 */
	private $response_code = 500;

	/**
	 * Set the title of the error page.
	 *
	 * @param string $title Title to use.
	 * @return self
	 */
	public function with_title( $title ) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Set the message of the error page.
	 *
	 * @param string $message Message to use.
	 * @return self
	 */
	public function with_message( $message ) {
		$this->message = $message;
		return $this;
	}

	/**
	 * Set the exception of the error page.
	 *
	 * @param Exception $exception Exception to use.
	 * @return self
	 */
	public function with_exception( Exception $exception ) {
		$this->exception = $exception;
		return $this;
	}

	/**
	 * Set the response_code of the error page.
	 *
	 * @param string $response_code Response code to use.
	 * @return self
	 */
	public function with_response_code( $response_code ) {
		$this->response_code = $response_code;
		return $this;
	}

	/**
	 * Render the error page.
	 *
	 * This first sets the required headers and then returns the HTML to send as
	 * output.
	 *
	 * @return string
	 */
	public function render() {
		$this->send_to_error_log();

		$this->set_headers();

		$text_direction = function_exists( 'is_rtl' ) && is_rtl()
			? 'rtl'
			: 'ltr';

		$styles = $this->get_styles( $text_direction );
		$html   = $this->get_html( $styles, $text_direction );

		return $html;
	}

	/**
	 * Send the exception that was caught to the error log.
	 */
	private function send_to_error_log() {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			sprintf(
				"%s - %s (%s) [%s]\n%s",
				$this->message,
				$this->exception->getMessage(),
				$this->exception->getCode(),
				get_class( $this->exception ),
				$this->exception->getTraceAsString()
			)
		);
	}

	/**
	 * Sets the required headers.
	 *
	 * This will only adapt the headers if they haven't yet been sent.
	 */
	private function set_headers() {
		if ( ! headers_sent() ) {
			// Define the content type for the error page.
			header( 'Content-Type: text/html; charset=utf-8' );

			// Mark the page as a server failure so it won't get indexed.
			status_header( $this->response_code );

			// Let the browser know this result shouldn't get cached.
			nocache_headers();
		}
	}

	/**
	 * Get the HTML output for the error page.
	 *
	 * @param string $styles         CSS styles to use.
	 * @param string $text_direction Text direction. Can be 'ltr' or 'rtl'.
	 * @return string HTML output.
	 */
	private function get_html( $styles, $text_direction ) {
		$no_robots = function_exists( 'wp_no_robots' )
			? wp_no_robots()
			: '';

		return <<<HTML
<!DOCTYPE html>
<html dir="{$text_direction}">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width">
		{$no_robots}
		<title>{$this->title}</title>
		{$styles}
	</head>
	<body id="error-page">
		<h1>{$this->title}</h1>
		<p>{$this->message}</p>
		{$this->render_exception()}
	</body>
</html>
HTML;
	}

	/**
	 * Render the exception of the error page.
	 *
	 * The exception details are only rendered if both WP_DEBUG and
	 * WP_DEBUG_DISPLAY are true.
	 *
	 * @return string HTML describing the exception that was thrown.
	 */
	private function render_exception() {
		if ( null === $this->exception ) {
			return '';
		}

		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return '';
		}

		if ( ! defined( 'WP_DEBUG_DISPLAY' ) || ! WP_DEBUG_DISPLAY ) {
			return '';
		}

		return sprintf(
			'<code><strong>%s</strong> (%s) [<em>%s</em>]<br><br><small>%s</small></code>',
			$this->exception->getMessage(),
			$this->exception->getCode(),
			get_class( $this->exception ),
			str_replace( "\n", '<br>', $this->exception->getTraceAsString() )
		);
	}

	/**
	 * Get the CSS styles to use for the error page.
	 *
	 * @param string $text_direction Text direction. Can be 'ltr' or 'rtl'.
	 * @return string CSS styles to use.
	 */
	private function get_styles( $text_direction ) {
		$rtl_font_tweak = 'rtl' === $text_direction
			? 'body { font-family: Tahoma, Arial; }'
			: '';

		return <<<STYLES
<style type="text/css">
	html {
		background: #f1f1f1;
	}
	body {
		background: #fff;
		color: #444;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
		margin: 2em auto;
		padding: 1em 2em;
		max-width: 700px;
		-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
	}
	h1 {
		border-bottom: 1px solid #dadada;
		clear: both;
		color: #666;
		font-size: 24px;
		margin: 30px 0 0 0;
		padding: 0;
		padding-bottom: 7px;
	}
	#error-page {
		margin-top: 50px;
	}
	#error-page p,
	#error-page .wp-die-message {
		font-size: 14px;
		line-height: 1.5;
		margin: 25px 0 20px;
	}
	#error-page code {
		font-family: Consolas, Monaco, monospace;
	}
	ul li {
		margin-bottom: 10px;
		font-size: 14px ;
	}
	a {
		color: #0073aa;
	}
	a:hover,
	a:active {
		color: #006799;
	}
	a:focus {
		color: #124964;
		-webkit-box-shadow:
			0 0 0 1px #5b9dd9,
			0 0 2px 1px rgba(30, 140, 190, 0.8);
		box-shadow:
			0 0 0 1px #5b9dd9,
			0 0 2px 1px rgba(30, 140, 190, 0.8);
		outline: none;
	}
	.button {
		background: #f7f7f7;
		border: 1px solid #ccc;
		color: #555;
		display: inline-block;
		text-decoration: none;
		font-size: 13px;
		line-height: 2;
		height: 28px;
		margin: 0;
		padding: 0 10px 1px;
		cursor: pointer;
		-webkit-border-radius: 3px;
		-webkit-appearance: none;
		border-radius: 3px;
		white-space: nowrap;
		-webkit-box-sizing: border-box;
		-moz-box-sizing:    border-box;
		box-sizing:         border-box;

		-webkit-box-shadow: 0 1px 0 #ccc;
		box-shadow: 0 1px 0 #ccc;
		vertical-align: top;
	}

	.button.button-large {
		height: 30px;
		line-height: 2.15384615;
		padding: 0 12px 2px;
	}

	.button:hover,
	.button:focus {
		background: #fafafa;
		border-color: #999;
		color: #23282d;
	}

	.button:focus {
		border-color: #5b9dd9;
		-webkit-box-shadow: 0 0 3px rgba(0, 115, 170, 0.8);
		box-shadow: 0 0 3px rgba(0, 115, 170, 0.8);
		outline: none;
	}

	.button:active {
		background: #eee;
		border-color: #999;
		-webkit-box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, 0.5);
		box-shadow: inset 0 2px 5px -3px rgba(0, 0, 0, 0.5);
	}

	{$rtl_font_tweak}
</style>
STYLES;
	}
}
