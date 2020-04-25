<?php
/**
 * Class Icon.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * Icons used to visually represent the state of a validation error.
 *
 * @package AmpProject\AmpWP
 */
class Icon {

	/**
	 * Indicates there are validation errors for the AMP page.
	 */
	const INVALID = 'amp-invalid';

	/**
	 * Indicate an AMP version of the page is available.
	 */
	const LINK = 'amp-link';

	/**
	 * Indicates the page is valid AMP.
	 */
	const VALID = 'amp-valid';

	/**
	 * Indicates there are validation errors which have not been explicitly accepted.
	 */
	const WARNING = 'amp-warning';

	/**
	 * Current icon.
	 *
	 * @var string
	 */
	private static $icon;

	/**
	 * Invalid icon.
	 *
	 * @return Icon
	 */
	public static function invalid() {
		self::$icon = self::INVALID;
		return new self();
	}

	/**
	 * Link icon
	 *
	 * @return Icon
	 */
	public static function link() {
		self::$icon = self::LINK;
		return new self();
	}

	/**
	 * Valid icon
	 *
	 * @return Icon
	 */
	public static function valid() {
		self::$icon = self::VALID;
		return new self();
	}

	/**
	 * Warning icon
	 *
	 * @return Icon
	 */
	public static function warning() {
		self::$icon = self::WARNING;
		return new self();
	}

	/**
	 * Get color for current icon.
	 *
	 * @return string Hex color for icon.
	 */
	public static function get_color() {
		// When updating the colors here, also do so for 'assets/css/src/amp-icons.css'.
		switch ( self::$icon ) {
			case self::INVALID:
				return '#dc3232';
			case self::LINK:
				return '#00a0d2';
			case self::VALID:
				return '#46b450';
			case self::WARNING:
				return '#f56e28';
			default:
				return '';
		}
	}

	/**
	 * Render icon as HTML.
	 *
	 * @param array $attributes List of attributes to add to HTML output.
	 * @return string Rendered HTML.
	 */
	public static function to_html( $attributes = [] ) {
		if ( self::$icon ) {
			$icon_class = ' amp-icon ' . self::$icon;

			$attributes['class'] = isset( $attributes['class'] )
				? $attributes['class'] . $icon_class
				: $icon_class;
		}

		$attributes_string = array_reduce(
			array_keys( $attributes ),
			static function ( $attributes_string, $attribute ) use ( $attributes ) {
				if ( ! $attribute ) {
					return '';
				}

				return $attributes_string . sprintf( '%s="%s" ', $attribute, $attributes[ $attribute ] );
			},
			''
		);

		self::$icon = null;

		return wp_kses_post( sprintf( '<span %s></span>', $attributes_string ) );
	}
}
