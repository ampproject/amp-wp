<?php
/**
 * Validated URL data.
 *
 * @package AMP
 * @since 2.2
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Style_Sanitizer;
use AMP_Validated_URL_Post_Type;
use WP_Error;
use WP_Post;

/**
 * ValidatedUrlData class.
 *
 * @since 2.2
 * @internal
 */
final class ValidatedUrlData {

	/**
	 * Validated URL post.
	 *
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Validated URL stylesheets data parsed from the JSON string in post meta.
	 *
	 * @var array|null
	 */
	private $stylesheets = null;

	/**
	 * ValidatedUrlDataProvider constructor.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function __construct( $post ) {
		$this->post = $post;
	}

	/**
	 * Get validated URL ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		if ( ! $this->post->ID ) {
			return null;
		}

		return $this->post->ID;
	}

	/**
	 * Get the URL that was validated.
	 *
	 * @return string|null
	 */
	public function get_url() {
		if ( ! $this->post ) {
			return null;
		}

		return AMP_Validated_URL_Post_Type::get_url_from_post( $this->post );
	}

	/**
	 * Get the date that the URL was validated.
	 *
	 * @return string|null
	 */
	public function get_date() {
		if ( ! $this->post->post_date ) {
			return null;
		}

		return $this->post->post_date;
	}

	/**
	 * Get the user that last validated the URL.
	 *
	 * @return int|null
	 */
	public function get_author() {
		if ( ! $this->post->post_author ) {
			return null;
		}

		return (int) $this->post->post_author;
	}

	/**
	 * Get the validated URL stylesheets data.
	 *
	 * @return array|WP_Error
	 */
	public function get_stylesheets() {
		if ( null !== $this->stylesheets ) {
			return $this->stylesheets;
		}

		$stylesheets = get_post_meta( $this->get_id(), AMP_Validated_URL_Post_Type::STYLESHEETS_POST_META_KEY, true );

		if ( empty( $stylesheets ) ) {
			return new WP_Error(
				'amp_validated_url_stylesheets_no_longer_available',
				__( 'Stylesheet information for this URL is no longer available. Such data is automatically deleted after a week to reduce database storage. It is of little value to store long-term given that it becomes stale as themes and plugins are updated. To obtain the latest stylesheet information, recheck this URL.', 'amp' )
			);
		}

		$stylesheets = json_decode( $stylesheets, true );

		if ( ! is_array( $stylesheets ) ) {
			return new WP_Error(
				'amp_validated_url_stylesheets_missing',
				__( 'Unable to retrieve stylesheets data for this URL.', 'amp' )
			);
		}

		foreach ( $stylesheets as $key => $stylesheet ) {
			$stylesheets[ $key ]['original_tag_abbr'] = $this->format_stylesheet_original_tag_abbreviation( $stylesheet['origin'] );
			$stylesheets[ $key ]['original_tag']      = $this->format_stylesheet_original_tag( $stylesheet['element']['name'], $stylesheet['element']['attributes'] );
		}

		$this->stylesheets = $stylesheets;

		return $this->stylesheets;
	}

	/**
	 * Get validated environment information.
	 *
	 * @return array
	 */
	public function get_environment() {
		return get_post_meta( $this->get_id(), AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY, true );
	}

	/**
	 * Format stylesheet original tag abbreviation.
	 *
	 * @param string $origin Original element.
	 *
	 * @return string Formatted tag abbreviation.
	 */
	private function format_stylesheet_original_tag_abbreviation( $origin ) {
		if ( 'link_element' === $origin ) {
			return '<link …>'; // @todo Consider adding the basename of the CSS file.
		}

		if ( 'style_element' === $origin ) {
			return '<style>';
		}

		if ( 'style_attribute' === $origin ) {
			return 'style="…"';
		}

		return '?';
	}

	/**
	 * Construct stylesheet original tag name based on the attributes.
	 *
	 * @param string $name       Original element name.
	 * @param array  $attributes Original element attributes.
	 *
	 * @return string
	 */
	private function format_stylesheet_original_tag( $name, $attributes ) {
		$result = '<' . $name;

		if ( ! empty( $attributes ) ) {
			if ( ! empty( $attributes['class'] ) ) {
				$attributes['class'] = trim( preg_replace( '/(^|\s)amp-wp-\w+(\s|$)/', ' ', $attributes['class'] ) );
				if ( empty( $attributes['class'] ) ) {
					unset( $attributes['class'] );
				}
			}
			if ( isset( $attributes[ AMP_Style_Sanitizer::ORIGINAL_STYLE_ATTRIBUTE_NAME ] ) ) {
				$attributes['style'] = $attributes[ AMP_Style_Sanitizer::ORIGINAL_STYLE_ATTRIBUTE_NAME ];
				unset( $attributes[ AMP_Style_Sanitizer::ORIGINAL_STYLE_ATTRIBUTE_NAME ] );
			}
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $attribute_name => $attribute_value ) {
					if ( '' === $attribute_value ) {
						$result .= ' ' . sprintf( '%s', esc_html( $attribute_name ) );
					} else {
						$result .= ' ' . sprintf( '%s="%s"', esc_html( $attribute_name ), esc_attr( $attribute_value ) );
					}
				}
			}
		}

		$result .= '>';

		return $result;
	}
}
