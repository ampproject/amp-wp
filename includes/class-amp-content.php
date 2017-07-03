<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php' );
require_once( AMP__DIR__ . '/includes/filters/class-amp-base-filter.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-base-embed-handler.php' );

class AMP_Content {
	private $content;
	private $amp_content = '';
	private $amp_scripts = array();
	private $amp_styles = array();
	private $args = array();
	private $embed_handler_classes = array();
	private $filter_classes = array();

	public function __construct( $content, $embed_handler_classes, $filter_classes, $args = array() ) {
		$this->content               = $content;
		$this->args                  = $args;
		$this->embed_handler_classes = $embed_handler_classes;
		$this->filter_classes        = $filter_classes;

		$this->transform();
	}

	public function get_amp_content() {
		return $this->amp_content;
	}

	public function get_amp_scripts() {
		return $this->amp_scripts;
	}

	public function get_amp_styles() {
		return $this->amp_styles;
	}

	private function transform() {
		$content = $this->content;

		// First, embeds + the_content filter
		$embed_handlers = $this->register_embed_handlers();
		$content = apply_filters( 'the_content', $content );
		$this->unregister_embed_handlers( $embed_handlers );

		// Then, filter to strip and/or convert non-amp content
		$content = $this->filter( $content );

		$this->amp_content = $content;
	}

	private function add_scripts( $scripts ) {
		$this->amp_scripts = array_merge( $this->amp_scripts, $scripts );
	}

	private function add_styles( $styles ) {
		$this->amp_styles = array_merge( $this->amp_styles, $styles );
	}

	private function register_embed_handlers() {
		$embed_handlers = array();

		foreach ( $this->embed_handler_classes as $embed_handler_class => $args ) {
			$embed_handler = new $embed_handler_class( array_merge( $this->args, $args ) );

			if ( ! is_subclass_of( $embed_handler, 'AMP_Base_Embed_Handler' ) ) {
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Embed Handler (%s) must extend `AMP_Embed_Handler`', 'amp' ), $embed_handler_class ), '0.1' );
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

	private function filter( $content ) {
		list( $filtered_content, $scripts, $styles ) = AMP_Content_Filter::filter( $content, $this->filter_classes, $this->args );

		$this->add_scripts( $scripts );
		$this->add_styles( $styles );

		return $filtered_content;
	}
}

class AMP_Content_Filter {
	public static function filter( $content, $filter_classes, $global_args = array() ) {
		$scripts = array();
		$styles = array();
		$dom = AMP_DOM_Utils::get_dom_from_content( $content );

		foreach ( $filter_classes as $filter_class => $args ) {
			if ( ! class_exists( $filter_class ) ) {
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Filter (%s) class does not exist', 'amp' ), esc_html( $filter_class ) ), '0.4.1' );
				continue;
			}

			$filter = new $filter_class( $dom, array_merge( $global_args, $args ) );

			if ( ! is_subclass_of( $filter, 'AMP_Base_Filter' ) ) {
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Filter (%s) must extend `AMP_Base_Filter`', 'amp' ), esc_html( $filter_class ) ), '0.1' );
				continue;
			}

			$filter->filter();

			$scripts = array_merge( $scripts, $filter->get_scripts() );
			$styles = array_merge( $styles, $filter->get_styles() );
		}

		$filtered_content = AMP_DOM_Utils::get_content_from_dom( $dom );

		return array( $filtered_content, $scripts, $styles );
	}
}
