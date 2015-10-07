<?php

require_once( dirname( __FILE__ ) . '/class-amp-kses.php' );
require_once( dirname( __FILE__ ) . '/class-amp-img.php' );
require_once( dirname( __FILE__ ) . '/class-amp-iframe.php' );

class AMP_Content {
	private $original_content;
	private $queued_scripts;

	public function __construct( $content ) {
		$this->original_content = $content;
	}

	public function transform() {
		$content = $this->original_content;

		$content = apply_filters( 'the_content', $content );

		// We run kses before AMP conversion due to a kses bug which doesn't allow hyphens (#34105-core).
		// Our custom kses handler strips out all not-allowed stuff and leaves in stuff that will be converted (like iframe, img, audio, video).
		// Technically, conversion should catch the tags so we shouldn't need to run it after anyway.
		$content = AMP_KSES::strip( $content );

		// Convert HTML to AMP
		// see https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags)
		$scripts = array();

		$img_converter = new AMP_Img_Converter;
		$content = $img_converter->convert( $content, array(
			'layout' => 'responsive',
		) );

		$converter = new AMP_Iframe_Converter;
		$content = $converter->convert( $content, array(
			'layout' => 'responsive',
		) );

		$this->queued_scripts = $scripts;

		return $content;
	}

	public function get_queued_scripts() {
		return $this->queued_scripts;
	}
}
