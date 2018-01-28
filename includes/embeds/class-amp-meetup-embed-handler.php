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
		wp_embed_register_handler( 'meetup', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		wp_embed_unregister_handler( 'meetup', -1 );
	}

	/**
	 * Embed found with matching URL callback.
	 *
	 * @param array $matches URL regex matches.
	 * @param array $attr    Additional parameters.
	 * @param array $url     URL.
	 * @return string Embed.
	 */
	public function oembed( $matches, $attr, $url ) {
		return $this->render( array( 'url' => $url ) );
	}

	/**
	 * Extract the MeetUp CSS and output in header (otherwise stripped).
	 * Output the MeetUp oEmbed as usual.
	 *
	 * @param array $args parameters used for output.
	 * @return string Rendered content.
	 */
	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
		) );

		if ( empty( $args['url'] ) ) {
			return '';
		}

		$content = wp_oembed_get( $args['url'] );

		// Strip AMP-illegal style from response.
		$content = preg_replace( '#<style.+</style>#', '', $content );

		return $content;
	}
}

