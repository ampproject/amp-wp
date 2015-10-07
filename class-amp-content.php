<?php

require_once( dirname( __FILE__ ) . '/class-amp-kses.php' );

class AMP_Content {
	private $original_content;

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
		$img_converter = new AMP_Img_Converter;
		$content = $img_converter->convert( $content, array(
			'layout' => 'responsive',
		) );

		return $content;
	}
}

abstract class AMP_Converter {
	abstract public function convert( $content, $amp_attributes = array() );

	public function has_tag( $content, $tag ) {
		return false !== stripos( $content, sprintf( '<%s', $tag ) );
	}

	public function get_tags( $content, $tag ) {
		preg_match_all( '#<' . $tag . '([^>]+?)(></' . $tag . '>|[\/]?>)#i', $content, $tags, PREG_SET_ORDER );
		return $tags;
	}

	protected function build_attributes_string( $attributes ) {
		$string = '';
		foreach ( $attributes as $name => $value ) {
			$string .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
		}
		return $string;
	}
}

class AMP_Img_Converter extends AMP_Converter {
	public static $tag = 'img';

	public function convert( $content, $amp_attributes = array() ) {
		if ( ! $this->has_tag( $content, self::$tag ) ) {
			return $content;
		}

		$images = $this->get_tags( $content, self::$tag );
		if ( empty( $images ) ) {
			return $content;
		}

		foreach ( $images as $image ) {
			$old_img = $image[0];
			$old_img_attr = isset( $image[1] ) ? $image[1] : '';
			$new_img = '';

			$attributes = wp_kses_hair( $old_img_attr,  array( 'http', 'https' ) );

			if ( ! empty( $attributes['src'] ) ) {
				$attributes = $this->filter_attributes( $attributes );
				$attributes = array_merge( $attributes, $amp_attributes );

				// Workaround for https://github.com/Automattic/amp-wp/issues/20
				// responsive + float don't mix
				if ( isset( $attributes['class'] )
					&& (
						false !== strpos( $attributes['class'], 'alignleft' )
						|| false !== strpos( $attributes['class'], 'alignright' )
					)
				) {
					unset( $attributes['layout'] );
				}

				$new_img .= sprintf( '<amp-img%s></amp-img>', $this->build_attributes_string( $attributes ) );
			}

			$old_img_pattern = '~' . preg_quote( $old_img, '~' ) . '~';
			$content = preg_replace( $old_img_pattern, $new_img, $content, 1 );
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
				case 'alt':
				case 'width':
				case 'height':
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

class AMP_Img_Dimension_Extractor {
	static public function extract( $url ) {
		$dimensions = self::extract_from_filename( parse_url( $url, PHP_URL_PATH ) );
		if ( $dimensions ) {
			return $dimensions;
		}

		$dimensions = self::extract_from_attachment_metadata( $url );
		if ( $dimensions ) {
			return $dimensions;
		}

		return false;
	}

	static private function extract_from_filename( $path ) {
		$filename = basename( $path );
		if ( ! $filename ) {
			return false;
		}

		$result = preg_match( '~(\d+)x(\d+)\.~', $filename, $matches );
		if ( ! $result ) {
			return false;
		}

		return array( $matches[1], $matches[2] );
	}

	public static function extract_from_attachment_metadata( $url ) {
		$url = strtok( $url, '?' );
		$attachment_id = attachment_url_to_postid( $url );
		if ( empty( $attachment_id ) ) {
			return false;
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		if ( ! $metadata ) {
			return false;
		}

		return array( $metadata['width'], $metadata['height'] );
	}
}
