<?php

namespace AmpProject\Optimizer;

use AmpProject\Html\Attribute;
use AmpProject\Dom\Element;
use AmpProject\Layout;
use AmpProject\Html\LengthUnit;

final class ImageDimensions
{
    /**
     * Regular expression pattern to match the trailing unit of a dimension.
     *
     * @var string
     */
    const UNIT_REGEX_PATTERN = '/[0-9]+(?<unit>(?:[a-z]+|%))$/i';

    /**
     * Images smaller than 150px are considered tiny.
     *
     * @var int
     */
    const TINY_THRESHOLD = 150;

    /**
     * Image for which this represents the dimensions.
     *
     * @var Element
     */
    private $image;

    /**
     * Width of the image.
     *
     * @var int|float|string|null
     */
    private $width;

    /**
     * Height of the image.
     *
     * @var int|float|string|null
     */
    private $height;

    /**
     * Unit of the width of the image.
     *
     * @var int|float|string|null
     */
    private $widthUnit;

    /**
     * Unit of the height of the image.
     *
     * @var int|float|string|null
     */
    private $heightUnit;

    /**
     * Layout of the image.
     *
     * @var string|null
     */
    private $layout;

    /**
     * ImageDimensions constructor.
     *
     * @param Element $image Image to represent the dimensions of.
     */
    public function __construct(Element $image)
    {
        $this->image = $image;
    }

    /**
     * Get the dimensions to use from an element's parent(s).
     *
     * @return int[] Array containing the width and the height.
     */
    public function getDimensionsFromParent()
    {
        $level   = 0;
        $element = $this->image;
        while ($element->parentNode && ++$level < 3) {
            $element = $element->parentNode;

            if (! $element instanceof Element) {
                continue;
            }

            $width = $element->hasAttribute(Attribute::WIDTH)
                ? $element->getAttribute(Attribute::WIDTH)
                : -1;

            $height = $element->hasAttribute(Attribute::HEIGHT)
                ? $element->getAttribute(Attribute::HEIGHT)
                : -1;

            if (empty($width)) {
                $width = -1;
            }

            if (empty($height)) {
                $height = -1;
            }

            // Skip elements that don't provide any dimensions.
            if ($width === -1 && $height === -1) {
                continue;
            }

            // If layout is responsive, consider dimensions to be unbounded.
            if (Layout::RESPONSIVE === $element->getAttribute(Attribute::LAYOUT)) {
                return [PHP_INT_MAX, PHP_INT_MAX];
            }

            return [(int)$width, (int)$height];
        }

        return [-1, -1];
    }

    /**
     * Check whether the image is to be considered tiny and should be ignored.
     *
     * A tiny image is any image with width or height less than 150 pixels and a non-responsive layout.
     *
     * @param int|null $threshold Optional. Threshold to use. Defaults to 150 pixels.
     * @return bool Whether the image is tiny.
     */
    public function isTiny($threshold = self::TINY_THRESHOLD)
    {
        // Make sure we have a valid threshold to compare against.
        if ($threshold === null) {
            $threshold = self::TINY_THRESHOLD;
        }

        // For the 'fill' layout, we need to look at the parent container's dimensions.
        if (! $this->hasWidth() && ! $this->hasHeight()) {
            if ($this->getLayout() === Layout::FILL) {
                list($this->width, $this->height) = $this->getDimensionsFromParent();
            } else {
                return true;
            }
        }

        $width  = $this->getWidth();
        $height = $this->getHeight();

        // If one or both of the dimensions are missing, we cannot deduce an aspect ratio.
        if ($width === null || $height === null) {
            return true;
        }

        // If one or both of the dimensions are zero, the entire image will be invisible.
        if (
            (is_numeric($width) && $width <= 0)
            || (is_numeric($height) && $height <= 0)
        ) {
            return true;
        }

        $widthUnit  = $this->getWidthUnit();
        $heightUnit = $this->getHeightUnit();

        // Try to convert absolute units into their equivalent pixel value.
        if (!empty($widthUnit)) {
            $numericWidth = $this->getNumericWidth();
            if (false !== $numericWidth) {
                $width     = $numericWidth;
                $widthUnit = '';
            }
        }
        if (!empty($heightUnit)) {
            $numericHeight = $this->getNumericHeight();
            if (false !== $numericHeight) {
                $height     = $numericHeight;
                $heightUnit = '';
            }
        }

        // If only relative units are in use, we cannot assume much about the final dimensions.
        if (
            in_array($widthUnit, LengthUnit::RELATIVE_UNITS, true)
            && in_array($heightUnit, LengthUnit::RELATIVE_UNITS, true)
        ) {
            return false;
        }

        // If only one of the units is relative, compare the other against the threshold.
        if (in_array($widthUnit, LengthUnit::RELATIVE_UNITS, true)) {
            return is_numeric($height) && $height < $threshold;
        } elseif (in_array($heightUnit, LengthUnit::RELATIVE_UNITS, true)) {
            return is_numeric($width) && $width < $threshold;
        }

        switch ($this->getLayout()) {
            // For 'responsive' layout, the image adapts to the container and can grow beyond its dimensions.
            case Layout::RESPONSIVE:
                return false;

            // For 'fixed-height' layout, the width can grow and shrink, so we only compare the height.
            case Layout::FIXED_HEIGHT:
                return is_numeric($height) && $height < $threshold;

            // By default, we compare the dimensions against the provided threshold.
            default:
                return (is_numeric($width) && $width < $threshold)
                    || (is_numeric($height) && $height < $threshold);
        }
    }

    /**
     * Check whether the image has a width.
     *
     * @return bool Whether the image has a width.
     */
    public function hasWidth()
    {
        return $this->getWidth() !== null;
    }

    /**
     * Check whether the image has a height.
     *
     * @return bool Whether the image has a height.
     */
    public function hasHeight()
    {
        return $this->getHeight() !== null;
    }

    /**
     * Check whether the image has a layout.
     *
     * @return bool Whether the image has a layout.
     */
    public function hasLayout()
    {
        return $this->getLayout() !== '';
    }

    /**
     * Get the width of the image.
     *
     * @return int|float|string|null Width of the image, or null if the image has no width.
     */
    public function getWidth()
    {
        if ($this->width === null) {
            $this->width = -1;
            $width       = $this->image->getAttribute(Attribute::WIDTH);
            if (trim($width) !== '') {
                if (is_numeric($width)) {
                    $intWidth    = (int)$width;
                    $floatWidth  = (float)$width;
                    $this->width = $intWidth == $floatWidth ? $intWidth : $floatWidth;
                } else {
                    $this->width = $width;
                }
            }
        }

        return $this->width !== -1 ? $this->width : null;
    }

    /**
     * Get the height of the image.
     *
     * @return int|float|string|null Height of the image, or null if the image has no width.
     */
    public function getHeight()
    {
        if ($this->height === null) {
            $this->height = -1;
            $height       = $this->image->getAttribute(Attribute::HEIGHT);
            if (trim($height) !== '') {
                if (is_numeric($height)) {
                    $intHeight    = (int)$height;
                    $floatHeight  = (float)$height;
                    $this->height = $intHeight == $floatHeight ? $intHeight : $floatHeight;
                } else {
                    $this->height = $height;
                }
            }
        }

        return $this->height !== -1 ? $this->height : null;
    }

    /**
     * Get the numeric width of the image.
     *
     * This automatically converts some of the units into numeric pixel values.
     *
     * @return int|float|false Numeric width of the image, or false if the width is not numeric.
     */
    public function getNumericWidth()
    {
        $width     = $this->getWidth();
        $widthUnit = $this->getWidthUnit();

        if (is_numeric($width)) {
            return $width;
        }

        if (!is_string($width) || empty($widthUnit)) {
            return false;
        }

        $width = trim(str_replace($widthUnit, '', $width));

        if (!is_numeric($width)) {
            return false;
        }

        $intWidth   = (int)$width;
        $floatWidth = (float)$width;
        $width      = $intWidth == $floatWidth ? $intWidth : $floatWidth;

        return LengthUnit::convertIntoPixels($width, $widthUnit);
    }

    /**
     * Get the numeric height of the image.
     *
     * This automatically converts some of the units into numeric pixel values.
     *
     * @return int|float|false Numeric height of the image, or false if the height is not numeric.
     */
    public function getNumericHeight()
    {
        $height     = $this->getHeight();
        $heightUnit = $this->getHeightUnit();

        if (is_numeric($height)) {
            return $height;
        }

        if (!is_string($height) || empty($heightUnit)) {
            return false;
        }

        $height = trim(str_replace($heightUnit, '', $height));

        if (!is_numeric($height)) {
            return false;
        }

        $intHeight   = (int)$height;
        $floatHeight = (float)$height;
        $height      = $intHeight == $floatHeight ? $intHeight : $floatHeight;

        return LengthUnit::convertIntoPixels($height, $heightUnit);
    }

    /**
     * Get the unit of the width.
     *
     * @return string Unit of the width, or an empty string if none found.
     */
    public function getWidthUnit()
    {
        if ($this->widthUnit !== null) {
            return $this->widthUnit;
        }
        $width = $this->getWidth();

        if (!is_string($width)) {
            $this->widthUnit = '';
            return $this->widthUnit;
        }

        $matches = [];

        if (!preg_match(self::UNIT_REGEX_PATTERN, $width, $matches)) {
            $this->widthUnit = '';
            return $this->widthUnit;
        }

        $this->widthUnit = strtolower(trim($matches['unit']));
        return $this->widthUnit;
    }


    /**
     * Get the unit of the height.
     *
     * @return string Unit of the height, or an empty string if none found.
     */
    public function getHeightUnit()
    {
        if ($this->heightUnit !== null) {
            return $this->heightUnit;
        }
        $height = $this->getHeight();

        if (!is_string($height)) {
            $this->heightUnit = '';
            return $this->heightUnit;
        }

        $matches = [];

        if (!preg_match(self::UNIT_REGEX_PATTERN, $height, $matches)) {
            $this->heightUnit = '';
            return $this->heightUnit;
        }

        $this->heightUnit = strtolower(trim($matches['unit']));
        return $this->heightUnit;
    }

    /**
     * Get the layout of the image.
     *
     * @return string Layout of the image, or an empty string if the image has no layout.
     */
    public function getLayout()
    {
        if ($this->layout === null) {
            $this->layout = $this->image->hasAttribute(Attribute::LAYOUT)
                ? (string)$this->image->getAttribute(Attribute::LAYOUT)
                : '';
        }

        return $this->layout;
    }
}
