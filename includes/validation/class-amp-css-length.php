<?php
/**
 * Class AMP_CSS_Length
 *
 * @package AMP
 */

/**
 * Class AMP_CSS_Length
 *
 * @since 1.5
 */
class AMP_CSS_Length {

	/**
	 * Whether the value or unit is invalid. Note that passing
	 * an empty value as `$attr_value` is considered valid.
	 *
	 * @var bool
	 */
	protected $is_valid = false;

	/**
	 * Whether the attribute value is set.
	 *
	 * @var bool
	 */
	protected $is_set = false;

	/**
	 * Whether the attribute value is 'auto'. This is a special value that
	 * indicates that the value gets derived from the context. In practice
	 * that's only ever the case for a width.
	 *
	 * @var bool
	 */
	protected $is_auto = false;

	/**
	 * Whether the attribute value is 'fluid'.
	 *
	 * @var bool
	 */
	protected $is_fluid = false;

	/**
	 * The numeric value.
	 *
	 * @var float
	 */
	protected $numeral = 0;

	/**
	 * The unit, 'px' being the default in case it's absent.
	 *
	 * @var string
	 */
	protected $unit = 'px';

	/**
	 * AMP_CSS_Length constructor.
	 *
	 * @param string $attr_value  Attribute value to be parsed.
	 * @param bool   $allow_auto  Whether or not to allow the 'auto' value as a value.
	 * @param bool   $allow_fluid Whether or not to allow the 'fluid' value as a value.
	 */
	public function __construct( $attr_value, $allow_auto, $allow_fluid ) {
		if ( ! isset( $attr_value ) || '' === $attr_value ) {
			$this->is_valid = true;
			return;
		}

		$this->is_set = true;

		if ( 'auto' === $attr_value ) {
			$this->is_auto  = true;
			$this->is_valid = $allow_auto;
			return;
		} elseif ( 'fluid' === $attr_value ) {
			$this->is_fluid = true;
			$this->is_valid = $allow_fluid;
		}

		$pattern = '/^(?<numeral>\d+(?:\.\d+)?)(?<unit>px|em|rem|vh|vw|vmin|vmax)?$/';
		if ( preg_match( $pattern, $attr_value, $match ) ) {
			$this->is_valid = true;
			$this->numeral  = isset( $match['numeral'] ) ? (float) $match['numeral'] : $this->numeral;
			$this->unit     = isset( $match['unit'] ) ? $match['unit'] : $this->unit;
		}
	}

	/**
	 * Whether or not the attribute value is valid.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return $this->is_valid;
	}

	/**
	 * Whether the attribute value is set.
	 *
	 * @return bool
	 */
	public function is_set() {
		return $this->is_set;
	}

	/**
	 * Whether the attribute value is 'fluid'.
	 *
	 * @return bool
	 */
	public function is_fluid() {
		return $this->is_fluid;
	}

	/**
	 * Whether the attribute value is 'auto'.
	 *
	 * @return bool
	 */
	public function is_auto() {
		return $this->is_auto;
	}

	/**
	 * The unit of the attribute.
	 *
	 * @return string
	 */
	public function get_unit() {
		return $this->unit;
	}
}
