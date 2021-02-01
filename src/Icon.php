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
 * @since 2.0
 * @internal
 */
final class Icon {

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
	 * Indicates being on an AMP page.
	 */
	const LOGO = 'amp-logo';

	/**
	 * Icon class name.
	 *
	 * @var string
	 */
	private $icon;

	/**
	 * Constructor.
	 *
	 * @param string $icon Icon class name.
	 */
	private function __construct( $icon ) {
		$this->icon = $icon;
	}

	/**
	 * Invalid icon.
	 *
	 * @return Icon
	 */
	public static function invalid() {
		return new self( self::INVALID );
	}

	/**
	 * Link icon
	 *
	 * @return Icon
	 */
	public static function link() {
		return new self( self::LINK );
	}

	/**
	 * Valid icon
	 *
	 * @return Icon
	 */
	public static function valid() {
		return new self( self::VALID );
	}

	/**
	 * Warning icon
	 *
	 * @return Icon
	 */
	public static function warning() {
		return new self( self::WARNING );
	}

	/**
	 * Logo icon
	 *
	 * @return Icon
	 */
	public static function logo() {
		return new self( self::LOGO );
	}

	/**
	 * Get color for current icon.
	 *
	 * @return string Hex color for icon.
	 */
	public function get_color() {
		// When updating the colors here, also do so for 'assets/css/src/amp-icons.css'.
		switch ( $this->icon ) {
			case self::INVALID:
				return '#dc3232';
			case self::LINK:
				return '#00a0d2';
			case self::VALID:
				return '#46b450';
			case self::WARNING:
				return '#ffc733';
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
	public function to_html( $attributes = [] ) {
		$icon_class = 'amp-icon ' . $this->icon;

		$attributes['class'] = ! empty( $attributes['class'] )
			? $attributes['class'] . ' ' . $icon_class
			: $icon_class;

		$attributes_string = implode(
			' ',
			array_map(
				static function ( $key, $value ) {
					return sprintf(
						'%s="%s"',
						esc_attr( sanitize_key( $key ) ),
						esc_attr( $value )
					);
				},
				array_keys( $attributes ),
				$attributes
			)
		);

		return wp_kses_post( sprintf( '<span %s></span>', $attributes_string ) );
	}
}
