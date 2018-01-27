<?php
/**
 * Class AMP_MeetUp_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_MeetUp_Embed_Handler
 */
class AMP_MeetUp_Embed_Handler extends AMP_Base_Embed_Handler {
	/**
	 * Regex matched.
	 *
	 * @const string
	 */
	const URL_PATTERN = '#https?://(www\.)?meetu(\.ps|p\.com)/.*#i';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		wp_embed_register_handler( 'meetup', self::URL_PATTERN, array( $this, 'oembed' ) );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'meetup' );
	}

	/**
	 * Embed found with matching URL callback.
	 *
	 * @param array $matches URL regex matches.
	 * @param array $attr    Additional parameters.
	 * @param array $url     URL.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( array( 'url' => $url ) );
	}

	/**
	 * Extract the MeetUp CSS and output in header (otherwise stripped).
	 * Output the MeetUp oembed as usual.
	 *
	 * @param array $args parameters used for output.
	 */
	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
		) );

		if ( empty( $args['url'] ) ) {
			return '';
		}

		$content = wp_oembed_get( $args['url'] );
		preg_match( '#<style.+</style>#', $content, $result );

		// Outlying style tag from oembed.
		if ( isset( $result[0] ) ) {
			$css = str_replace( '<style type="text/css">', '<style amp-meetup>', $result[0] );
			echo $css; // WPCS: XSS ok.
		}

		return $content;
	}
}

