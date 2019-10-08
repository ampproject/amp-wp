<?php
/**
 * Class AMP_WordPress_Embed_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_WordPress_Embed_Handler
 *
 * @since 1.3.1
 */
class AMP_WordPress_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Default height.
	 *
	 * @todo What is the default?
	 *
	 * @var int
	 */
	protected $DEFAULT_HEIGHT = 400;

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 100, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', [ $this, 'filter_embed_oembed_html' ], 100 );
	}

	/**
	 * Filter oEmbed HTML for WordPress to convert to AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		/*
		 * Example WordPress embed HTML that would be present in $cache.
		 *
		 * <blockquote class="wp-embedded-content" data-secret="gTfBfCdvMg">
		 *  <a href="https://wordpressdev.lndo.site/2019/02/01/hello-world-2/">Hello world!!</a>
		 * </blockquote>
		 * <iframe title="&#8220;Hello world!!&#8221; &#8212; WordPressDevs" class="wp-embedded-content" sandbox="allow-scripts" security="restricted" style="position: absolute; clip: rect(1px, 1px, 1px, 1px);" src="https://wordpressdev.lndo.site/2019/02/01/hello-world-2/embed/#?secret=gTfBfCdvMg" data-secret="gTfBfCdvMg" width="600" height="338" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>
		 */
		if ( ! preg_match( '#<blockquote class="wp-embedded-content" data-secret="\w+">(.+?)</blockquote>#s', $cache, $matches ) ) {
			return $cache;
		}
		$fallback = sprintf( '<blockquote fallback>%s</blockquote>', $matches[1] );

		$attributes = [
			'height' => $this->args['height'],
			'title'  => '',
		];
		if ( preg_match( '#<iframe[^>]*?height="(?P<height>\d+)"#s', $cache, $matches ) ) {
			$attributes['height'] = (int) $matches['height'];
		}
		if ( preg_match( '#<iframe[^>]*?title="(?P<title>[^"]+?)"#s', $cache, $matches ) ) {
			$attributes['title'] = $matches['title'];
		}

		return sprintf(
			'<amp-wordpress-embed layout="fixed-height" height="%d" title="%s" data-url="%s">%s</amp-wordpress-embed>',
			esc_attr( $attributes['height'] ),
			esc_attr( $attributes['title'] ),
			esc_url( $url ),
			$fallback
		);
	}
}
