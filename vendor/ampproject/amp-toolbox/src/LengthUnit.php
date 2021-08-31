<?php

namespace AmpProject;

/**
 * Unit of a length.
 *
 * This interface defines the available units that can be recognized in HTML and/or CSS dimensions.
 *
 * @see https://developer.mozilla.org/en-US/docs/Learn/CSS/Building_blocks/Values_and_units
 *
 * @package ampproject/amp-toolbox
 */
final class LengthUnit
{

    /**
     * Centimeters.
     *
     * 1cm = 96px/2.54.
     *
     * @var string
     */
    const CM = 'cm';

    /**
     * Millimeters.
     *
     * 1mm = 1/10th of 1cm.
     *
     * @var string
     */
    const MM = 'mm';

    /**
     * Quarter-millimeters.
     *
     * 1Q = 1/40th of 1cm.
     *
     * @var string
     */
    const Q = 'q';

    /**
     * Inches.
     *
     * 1in = 2.54cm = 96px.
     *
     * @var string
     */
    const IN = 'in';

    /**
     * Picas.
     *
     * 1pc = 1/6th of 1in.
     *
     * @var string
     */
    const PC = 'pc';

    /**
     * Points.
     *
     * 1pt = 1/72th of 1in.
     *
     * @var string
     */
    const PT = 'pt';

    /**
     * Pixels.
     *
     * 1px = 1/96th of 1in.
     *
     * @var string
     */
    const PX = 'px';

    /**
     * Font size of the parent, in the case of typographical properties like font-size, and font size of the element
     * itself, in the case of other properties like width.
     *
     * @var string
     */
    const EM = 'em';

    /**
     * The x-height of the element's font.
     *
     * @var string
     */
    const EX = 'ex';

    /**
     * The advance measure (width) of the glyph "0" of the element's font.
     *
     * @var string
     */
    const CH = 'ch';

    /**
     * Font size of the root element.
     *
     * @var string
     */
    const REM = 'rem';

    /**
     * Line height of the element.
     *
     * @var string
     */
    const LH = 'lh';

    /**
     * 1% of the viewport's width.
     *
     * @var string
     */
    const VW = 'vw';

    /**
     * 1% of the viewport's height.
     *
     * @var string
     */
    const VH = 'vh';

    /**
     * 1% of the viewport's smaller dimension.
     *
     * @var string
     */
    const VMIN = 'vmin';

    /**
     * 1% of the viewport's larger dimension.
     *
     * @var string
     */
    const VMAX = 'vmax';

    /**
     * Set of known absolute units.
     *
     * @var string[]
     */
    const ABSOLUTE_UNITS = [
        self::CM,
        self::MM,
        self::Q,
        self::IN,
        self::PC,
        self::PT,
        self::PX,
    ];

    /**
     * Set of known relative units.
     *
     * @var string[]
     */
    const RELATIVE_UNITS = [
        self::EM,
        self::EX,
        self::CH,
        self::REM,
        self::LH,
        self::VW,
        self::VH,
        self::VMIN,
        self::VMAX,
    ];

    /**
     * Pixels per inch resolution to use for conversions.
     *
     * @var int
     */
    const PPI = 96;

    /**
     * Centimeters per inch.
     *
     * @var float
     */
    const CM_PER_IN = 2.54;

    /**
     * Convert a unit-based length into a number of pixels.
     *
     * @param int|float $value Value to convert.
     * @param string    $unit  Unit of the value.
     * @return int|float|false Converted value, or false if it could not be converted.
     */
    public static function convertIntoPixels($value, $unit)
    {
        if (0 === $value) {
            return 0;
        }
        switch ($unit) {
            case self::CM:
                return $value * self::PPI / self::CM_PER_IN;
            case self::MM:
                return $value * self::PPI / self::CM_PER_IN / 10;
            case self::Q:
                return $value * self::PPI / self::CM_PER_IN / 40;
            case self::IN:
                return $value * self::PPI;
            case self::PC:
                return $value * self::PPI / 6;
            case self::PT:
                return $value * self::PPI / 72;
            case self::PX:
                // No conversion needed for pixel values.
                return $value;
            default:
                return false;
        }
    }
}
