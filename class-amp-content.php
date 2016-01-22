<?php

require_once( dirname( __FILE__ ) . '/includes/class-amp-dom-utils.php' );

require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-base-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-blacklist-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-img-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-video-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-iframe-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-audio-sanitizer.php' );

require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-base-embed-handler.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-twitter-embed.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-youtube-embed.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-gallery-embed.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-instagram-embed.php' );

class AMP_Content {
	private $content;
	private $scripts = array();
	private $args = array();

	public function __construct( $content, $args = array() ) {
		$this->content = $content;
		$this->args = $args;
	}

	public function transform() {
		$content = $this->content;

		// First, embeds + the_content filter
		$embed_handlers = $this->register_embed_handlers();
		$content = apply_filters( 'the_content', $content );
		$this->unregister_embed_handlers( $embed_handlers );

		// Then, sanitize to strip and/or convert non-amp content
		$dom = AMP_DOM_Utils::get_dom_from_content( $content );
		$this->sanitize( $dom );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		return $content;
	}

	public function get_scripts() {
		return $this->scripts;
	}

	private function add_scripts( $scripts ) {
		$this->scripts = array_merge( $this->scripts, $scripts );
	}

	private function register_embed_handlers() {
		$embed_handlers = array();
		$embed_handler_classes = apply_filters( 'amp_content_embed_handlers', array( 'AMP_Twitter_Embed_Handler', 'AMP_YouTube_Embed_Handler', 'AMP_Gallery_Embed_Handler', 'AMP_Instagram_Embed_Handler' ) );

		foreach ( $embed_handler_classes as $embed_handler_class ) {
			$embed_handler = new $embed_handler_class( $this->args );

			if ( ! is_subclass_of( $embed_handler, 'AMP_Base_Embed_Handler' ) ) {
				_doing_it_wrong( sprintf( '%s::%s', __CLASS__, __METHOD__ ), __( 'Embed Handler must extend `AMP_Embed_Handler`', 'amp' ), '0.1' );
				continue;
			}

			$embed_handler->register_embed();
			$embed_handlers[] = $embed_handler;
		}

		return $embed_handlers;
	}

	private function unregister_embed_handlers( $embed_handlers ) {
		foreach ( $embed_handlers as $embed_handler ) {
			 $this->add_scripts( $embed_handler->get_scripts() );
			 $embed_handler->unregister_embed();
		}
	}

	private function sanitize( $dom ) {
		$sanitizer_classes = apply_filters( 'amp_content_sanitizers', array( 'AMP_Blacklist_Sanitizer', 'AMP_Img_Sanitizer', 'AMP_Video_Sanitizer', 'AMP_Audio_Sanitizer', 'AMP_Iframe_Sanitizer' ) );

		foreach ( $sanitizer_classes as $sanitizer_class ) {
			$sanitizer = new $sanitizer_class( $dom, $this->args );

			if ( ! is_subclass_of( $sanitizer, 'AMP_Base_Sanitizer' ) ) {
				_doing_it_wrong( sprintf( '%s::%s', __CLASS__, __METHOD__ ), __( 'Sanitizer must extend `AMP_Base_Sanitizer`', 'amp' ), '0.1' );
				continue;
			}

			$sanitizer->sanitize();
			$this->add_scripts( $sanitizer->get_scripts() );
		}
	}
}
