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
	 * CSS.
	 *
	 * @var string
	 */
	protected $captured_css;

	/**
	 * AMP_MeetUp_Embed_Handler constructor.
	 *
	 * @param array $args Args.
	 */
	public function __construct( array $args = array() ) {
		parent::__construct( $args );
		add_filter( 'amp_custom_styles', array( $this, 'filter_amp_custom_styles' ) );
		add_action( 'amp_post_template_css', array( $this, 'do_action_amp_post_template_css' ) );
	}

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'embed_oembed_html', array( $this, 'filter_embed_oembed_html' ), 10 );
	}

	/**
	 * Filter amp_custom_styles.
	 *
	 * @param string $css Amend CSS.
	 * @return string CSS.
	 */
	public function filter_amp_custom_styles( $css ) {
		if ( $this->captured_css ) {
			$css .= wp_strip_all_tags( $this->captured_css );
		}
		return $css;
	}

	/**
	 * Add styles for AMP post template.
	 */
	public function do_action_amp_post_template_css() {
		if ( $this->captured_css ) {
			echo wp_strip_all_tags( $this->captured_css ); // XSS OK.
		}
	}

	/**
	 * Filter oEmbed HTML for SoundCloud to convert to AMP.
	 *
	 * @param string $cache Cache for oEmbed.
	 * @param string $url   Embed URL.
	 * @return string Embed.
	 */
	public function filter_embed_oembed_html( $cache, $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( false === strpos( $parsed_url['host'], 'meetup.com' ) ) {
			return $cache;
		}
		if ( preg_match( ':<style[^>]*>(.+)</style>:s', $cache, $matches ) ) {
			$this->captured_css = $matches[1];

			// Eliminate illegal CSS.
			$this->captured_css = str_replace( '!important', '', $this->captured_css );

			// Remove CSS from embed.
			$cache = str_replace( $matches[0], '', $cache );
		}
		return $cache;
	}
}

