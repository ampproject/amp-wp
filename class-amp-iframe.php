<?php

require_once( dirname( __FILE__ ) . '/class-amp-converter.php' );

class AMP_Iframe_Converter extends AMP_Converter {
	public static $tag = 'iframe';

	private static $script_slug = 'amp-iframe';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-iframe-0.1.js';

	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}

		return array( self::$script_slug => self::$script_src );
	}

	public function convert( $amp_attributes = array() ) {
		if ( ! $this->has_tag( self::$tag ) ) {
			return $this->content;
		}

		$matches = $this->get_tags( self::$tag );
		if ( empty( $matches ) ) {
			return $this->content;
		}

		$this->did_convert_elements = true;

		$content = $this->content;
		foreach ( $matches as $match ) {
			$old_iframe = $match[0];
			$old_iframe_attr = isset( $match[2] ) ? $match[2] : '';
			$new_iframe = '';

			$attributes = wp_kses_hair( $old_iframe_attr,  array( 'http', 'https' ) );

			if ( ! empty( $attributes['src'] ) ) {
				$attributes = $this->filter_attributes( $attributes );
				$attributes = array_merge( $attributes, $amp_attributes );

				$new_iframe .= sprintf( '<amp-iframe %s></amp-iframe>', $this->build_attributes_string( $attributes ) );
			}

			$old_iframe_pattern = '~' . preg_quote( $old_iframe, '~' ) . '~';
			$content = preg_replace( $old_iframe_pattern, $new_iframe, $content, 1 );
		}

		return $content;
	}

	private function filter_attributes( $attributes ) {
		$out = array();

		foreach ( $attributes as $attribute ) {
			$name = $attribute['name'];
			$value = $attribute['value'];

			switch ( $name ) {
				case 'src':
				case 'sandbox':
				case 'width':
				case 'height':
				case 'frameborder':
				case 'allowfullscreen':
				case 'allowtransparency':
				case 'class':
					$out[ $name ] = $value;
					break;
				default;
					break;
			}
		}

		if ( ! isset( $out['width'] ) || ! isset( $out['height'] ) ) {
			list( $width, $height ) = AMP_Img_Dimension_Extractor::extract( $out['src'] );
			if ( $width && $height ) {
				$out['width'] = $width;
				$out['height'] = $height;
			}
		}

		return $out;
	}
}
