<?php

require_once( dirname( __FILE__ ) . '/includes/class-amp-dom-utils.php' );

require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-base-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-base-embed-handler.php' );

class AMP_Content {
	private $content;
	private $scripts = array();
	private $args = array();

	public function __construct( $content, $embed_handler_classes, $sanitizer_classes, $args = array() ) {
		$this->content = $content;
		$this->args = $args;
		$this->embed_handler_classes = $embed_handler_classes;
		$this->sanitizer_classes = $sanitizer_classes;
	}

	public function transform() {
		$content = $this->content;

		// First, embeds + the_content filter
		$embed_handlers = $this->register_embed_handlers();
		$content = apply_filters( 'the_content', $content );
		$this->unregister_embed_handlers( $embed_handlers );

		// Then, sanitize to strip and/or convert non-amp content
		$content = $this->sanitize( $content );

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

		foreach ( $this->embed_handler_classes as $embed_handler_class ) {
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

	private function sanitize( $content ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $content );

		foreach ( $this->sanitizer_classes as $sanitizer_class ) {
			$sanitizer = new $sanitizer_class( $dom, $this->args );

			if ( ! is_subclass_of( $sanitizer, 'AMP_Base_Sanitizer' ) ) {
				_doing_it_wrong( sprintf( '%s::%s', __CLASS__, __METHOD__ ), __( 'Sanitizer must extend `AMP_Base_Sanitizer`', 'amp' ), '0.1' );
				continue;
			}

			$sanitizer->sanitize();
			$this->add_scripts( $sanitizer->get_scripts() );
		}

		return AMP_DOM_Utils::get_content_from_dom( $dom );
	}
}
