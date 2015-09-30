<?php

class AMP_Content {
	private $original_content;

	public function __construct( $content ) {
		$this->original_content = $content;
	}

	public function transform() {
		$content = $this->original_content;

		$content = apply_filters( 'the_content', $content );

		// Convert HTML to AMP
		// see https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags)

		$content = $this->img_to_amp_img( $content );

		return $content;
	}

	public function img_to_amp_img( $content ) {
		if ( false === stripos( $content, '<img' ) ) {
			return $content;
		}

		preg_match_all( '#<img[^>]+?[\/]?>#i', $content, $images, PREG_SET_ORDER );
		if ( empty( $images ) ) {
			return $content;
		}

		foreach ( $images as $image ) {
			$old_img = $image[0];
			$new_img = '<amp-img';

			$attributes = wp_kses_hair( $old_img,  array( 'http', 'https' ) );
			foreach ( $attributes as $attribute ) {
				$name = $attribute['name'];
				$value = $attribute['value'];

				// TODO: srcset
				// TODO: handle when width and height are missing

				switch ( $name ) {
					case 'src':
					case 'alt':
					case 'width':
					case 'height':
						$new_img .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
						break;
					default;
						break;
				}

			}

			$new_img .= ' />';

			$old_img_pattern = '#' . preg_quote( $old_img ) . '#';
			$content = preg_replace( $old_img_pattern, $new_img, $content, 1 );
		}

		return $content;
	}
}
