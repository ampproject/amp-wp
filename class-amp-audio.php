<?php

require_once( dirname( __FILE__ ) . '/class-amp-converter.php' );

class AMP_Audio_Converter extends AMP_Converter {
	public static $tag = 'audio';

	private static $script_slug = 'amp-audio';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-audio-0.1.js';

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
			$old_element = $match[0];
			$old_attr = isset( $match[2] ) ? $match[2] : '';
			$new_element = '';

			$attributes = wp_kses_hair( $old_attr,  array( 'http', 'https' ) );

			$attributes = $this->filter_attributes( $attributes );
			$attributes = array_merge( $attributes, $amp_attributes );
			// TODO: limit child nodes too (only allowed source, div+fallback, and div+placeholder)
			$child_nodes = isset( $match[4] ) ? $match[4] : '';

			$new_element .= sprintf(
				'<amp-audio %s>%s</amp-audio>',
				$this->build_attributes_string( $attributes ),
				$child_nodes
			);

			$old_pattern = '~' . preg_quote( $old_element, '~' ) . '~';
			$content = preg_replace( $old_pattern, $new_element, $content, 1 );
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
				case 'width':
				case 'height':
				case 'class':
				case 'loop':
				case 'muted':
					$out[ $name ] = $value;
					break;
				case 'autoplay':
					$out[ $name ] = 'desktop tablet mobile';
					break;
				default;
					break;
			}
		}

		return $out;
	}
}
