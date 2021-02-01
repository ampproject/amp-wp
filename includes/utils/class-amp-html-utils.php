<?php
/**
 * Class AMP_HTML_Utils
 *
 * @package AMP
 */

/**
 * Class with static HTML utility methods.
 *
 * @internal
 */
class AMP_HTML_Utils {

	/**
	 * Generates HTML markup for a given tag, attributes and content.
	 *
	 * @param string $tag_name   Tag name.
	 * @param array  $attributes Associative array of $attribute => $value pairs.
	 * @param string $content    Inner content for the generated node.
	 * @return string HTML markup.
	 */
	public static function build_tag( $tag_name, $attributes = [], $content = '' ) {
		$attr_string = self::build_attributes_string( $attributes );
		return sprintf( '<%1$s %2$s>%3$s</%1$s>', sanitize_key( $tag_name ), $attr_string, $content );
	}

	/**
	 * Generates a HTML attributes string from given attributes.
	 *
	 * @param array $attributes Associative array of $attribute => $value pairs.
	 * @return string HTML attributes string.
	 */
	public static function build_attributes_string( $attributes ) {
		$string = [];
		foreach ( $attributes as $name => $value ) {
			if ( '' === $value ) {
				$string[] = sprintf( '%s', sanitize_key( $name ) );
			} else {
				$string[] = sprintf( '%s="%s"', sanitize_key( $name ), esc_attr( $value ) );
			}
		}
		return implode( ' ', $string );
	}

	/**
	 * Checks whether the given string is valid JSON.
	 *
	 * @param string $data String hopefully containing JSON.
	 * @return bool True if the string is valid JSON, false otherwise.
	 */
	public static function is_valid_json( $data ) {
		json_decode( $data );
		return ( json_last_error() === JSON_ERROR_NONE );
	}
}
