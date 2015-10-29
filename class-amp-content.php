<?php

require_once( dirname( __FILE__ ) . '/class-amp-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/class-amp-img.php' );
require_once( dirname( __FILE__ ) . '/class-amp-iframe.php' );
require_once( dirname( __FILE__ ) . '/class-amp-video.php' );
require_once( dirname( __FILE__ ) . '/class-amp-audio.php' );

class AMP_Content {
	private $original_content;
	private $scripts;

	public function __construct( $content ) {
		$this->original_content = $content;
		$this->scripts = array();
	}

	public function transform() {
		$content = $this->original_content;

		$content = apply_filters( 'the_content', $content );

		$content = AMP_Sanitizer::strip( $content );

		// Convert HTML to AMP
		// see https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags)
		$content = $this->convert( new AMP_Img_Converter( $content ), array(
			'layout' => 'responsive',
		) );

		$content = $this->convert( new AMP_Video_Converter( $content ), array(
			'layout' => 'responsive',
		) );

		$content = $this->convert( new AMP_Audio_Converter( $content ), array(
			'layout' => 'responsive',
		) );

		$content = $this->convert( new AMP_Iframe_Converter( $content ), array(
			'layout' => 'responsive',
		) );

		return $content;
	}

	public function add_scripts( $scripts ) {
		$this->scripts = array_merge( $this->scripts, $scripts );
	}

	public function get_scripts() {
		return $this->scripts;
	}

	private function convert( $converter, $attributes ) {
		$converted = $converter->convert( $attributes );
		$this->add_scripts( $converter->get_scripts() );
		return $converted;
	}
}
