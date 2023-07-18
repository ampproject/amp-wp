<?php

namespace AmpProject;

/**
 * Flexible unit of measure for CSS dimensions.
 *
 * Adapted from the `amp.validator.CssLength` class found in `validator.js` from the `ampproject/amphtml` project on
 * GitHub.
 *
 * @version 1911070201440
 * @link    https://github.com/ampproject/amphtml/blob/1911070201440/validator/engine/validator.js#L3351
 *
 * @package ampproject/amp-toolbox
 */
final class CssLength
{
    // Special attribute values.
    const AUTO  = 'auto';
    const FLUID = 'fluid';

    /**
     * Whether the value or unit is invalid. Note that passing an empty value as `$attr_value` is considered valid.
     *
     * @var bool
     */
    protected $isValid = false;

    /**
     * Whether the attribute value is set.
     *
     * @var bool
     */
    protected $isDefined = false;

    /**
     * Whether the attribute value is 'auto'. This is a special value that indicates that the value gets derived from
     * the context. In practice that's only ever the case for a width.
     *
     * @var bool
     */
    protected $isAuto = false;

    /**
     * Whether the attribute value is 'fluid'.
     *
     * @var bool
     */
    protected $isFluid = false;

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
     * Value of attribute.
     *
     * @var string
     */
    protected $attrValue;

    /**
     * Instantiate a CssLength object.
     *
     * @param string|null $attrValue Attribute value to be parsed.
     */
    public function __construct($attrValue)
    {
        if (null === $attrValue) {
            $this->isValid = true;
            return;
        }

        $this->attrValue = $attrValue;
        $this->isDefined = true;
    }

    /**
     * Validate the attribute value.
     *
     * @param bool $allowAuto  Whether or not to allow the 'auto' value as a value.
     * @param bool $allowFluid Whether or not to allow the 'fluid' value as a value.
     */
    public function validate($allowAuto, $allowFluid)
    {
        if ($this->isValid()) {
            return;
        }

        if (self::AUTO === $this->attrValue) {
            $this->isAuto  = true;
            $this->isValid = $allowAuto;
            return;
        }

        if (self::FLUID === $this->attrValue) {
            $this->isFluid = true;
            $this->isValid = $allowFluid;
        }

        $pattern = '/^(?<numeral>\d+(?:\.\d+)?)(?<unit>px|em|rem|vh|vw|vmin|vmax)?$/';
        if (preg_match($pattern, $this->attrValue, $match)) {
            $this->isValid = true;
            $this->numeral = isset($match['numeral']) ? (float)$match['numeral'] : $this->numeral;
            $this->unit    = isset($match['unit']) ? $match['unit'] : $this->unit;
        }
    }

    /**
     * Whether or not the attribute value is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Whether the attribute value is set.
     *
     * @return bool
     */
    public function isDefined()
    {
        return $this->isDefined;
    }

    /**
     * Whether the attribute value is 'fluid'.
     *
     * @return bool
     */
    public function isFluid()
    {
        return $this->isFluid;
    }

    /**
     * Whether the attribute value is 'auto'.
     *
     * @return bool
     */
    public function isAuto()
    {
        return $this->isAuto;
    }

    /**
     * The unit of the attribute.
     *
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * The numeral of the attribute.
     *
     * @return float
     */
    public function getNumeral()
    {
        return $this->numeral;
    }
}
