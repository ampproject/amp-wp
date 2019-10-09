<?php
/**
 * Class AMP_Scribd_Embed_Handler
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
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ] );
	}

	/**
	 * Filter oEmbed HTML for Scribd to be AMP compatible.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		if ( ! in_array( wp_parse_url( $url, PHP_URL_HOST ), [ 'scribd.com', 'www.scribd.com' ], true ) ) {
			return $cache;
		}

		$embed = $this->remove_script( $cache );
		$embed = $this->inject_sandbox_permissions( $embed );

		return $embed;
	}

	/**
	 * Remove the 'script' element from the provided HTML if there is any.
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	private function remove_script( $html ) {
		$html_without_script = preg_replace( '#<script(?:\s.*?)?>.+?</script>#s', '', $html );

		if ( null !== $html_without_script ) {
			return $html_without_script;
		}

		return $html;
	}

	/**
	 * Injects the 'allow-popups' & 'allow-scripts' permissions into the sandbox attribute so that
	 * the 'Fullscreen' button works as intended.
	 *
	 * @param string $html HTML string.
	 * @return string
	 */
	private function inject_sandbox_permissions( $html ) {
		if ( preg_match( '#<iframe.+?sandbox="(?P<sandbox_attr>.+?)"#s', $html, $matches ) ) {
			$permissions = [
				'allow-popups',
				'allow-scripts',
			];

			foreach ( $permissions as $permission ) {
				if ( false === strpos( $matches['sandbox_attr'], $permission ) ) {
					$matches['sandbox_attr'] .= " ${permission}";
				}
			}

			return preg_replace(
				'#(<iframe.+?sandbox=")(.+?)(".+</iframe>)#s',
				"$1${matches['sandbox_attr']}$3",
				$html,
				1
			);
		} else {
			return str_replace( '<iframe ', '<iframe sandbox="allow-scripts allow-popups" ', $html );
		}
	}

}
