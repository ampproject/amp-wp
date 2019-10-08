<?php
/**
 * Class AMP_SoundCloud_Embed_Handler
 *
 * @package AMP
 * @since 1.3.1
 */

/**
 * Class AMP_Scribd_Embed_Handler
 */
class AMP_Scribd_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Registers embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10, 2 );
	}

	/**
	 * Unregisters embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 10 );
	}

	/**
	 * Filter oEmbed HTML for Scribd to to be AMP compatible.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		if ( false === strpos( wp_parse_url( $url, PHP_URL_HOST ), 'scribd.com' ) ) {
			return $cache;
		}

		$embed = $this->remove_script( $cache );
		$embed = $this->inject_sandbox_attribute( $embed );

		return $embed;
	}

	/**
	 * Remove the 'script' element from the provided HTML if there is any.
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	private function remove_script( $html ) {
		$html_without_script = preg_replace( '#<script[^>].+?</script>#s', '', $html );

		if ( null !== $html_without_script ) {
			return $html_without_script;
		}

		return $html;
	}

	/**
	 * Add the sandbox attribute with 'allow-popups' & 'allow-scripts' permissions set so that the
	 * full screen button works.
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	private function inject_sandbox_attribute( $html ) {
		if ( false === strpos( $html, 'sandbox="allow-scripts allow-popups"' ) ) {
			preg_match( '#<iframe(?P<attributes>.+?)>#s', $html, $matches );
			return "<iframe sandbox='allow-scripts allow-popups' ${matches['attributes']}></iframe>";
		}

		return $html;
	}

}
