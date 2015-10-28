<?php

require_once( dirname( __FILE__ ) . '/class-amp-converter.php' );

class AMP_Video_Converter extends AMP_Converter {
	public static $tag = 'video';

	public function convert( $amp_attributes = array() ) {
		if ( ! $this->has_tag( self::$tag ) ) {
			return $this->content;
		}

		$matches = $this->get_tags( self::$tag );
		if ( empty( $matches ) ) {
			return $this->content;
		}

		$content = $this->content;
		foreach ( $matches as $match ) {
			$old_video = $match[0];
			$old_attr = isset( $match[2] ) ? $match[2] : '';
			$new_video = '';

			$attributes = wp_kses_hair( $old_attr,  array( 'http', 'https' ) );

			$attributes = $this->filter_attributes( $attributes );
			$attributes = array_merge( $attributes, $amp_attributes );
			// TODO: limit child nodes too (only allowed source, div+fallback, and div+placeholder)
			$child_nodes = isset( $match[4] ) ? $match[4] : '';

			$new_video .= sprintf(
				'<amp-video %s>%s</amp-video>',
				$this->build_attributes_string( $attributes ),
				$child_nodes
			);

			$old_pattern = '~' . preg_quote( $old_video, '~' ) . '~';
			$content = preg_replace( $old_pattern, $new_video, $content, 1 );
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
				case 'poster':
				case 'width':
				case 'height':
				case 'class':
				case 'controls':
				case 'loop':
				case 'muted':
					$out[ $name ] = $value;
					break;
				case 'autoplay':
					$out[ $name ] = 'desktop tablet mobile';
				default;
					break;
			}
		}

		// TODO: default width/height

		return $out;
	}
}
