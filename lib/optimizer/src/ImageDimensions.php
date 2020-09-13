<?php

namespace AmpProject\Optimizer;

use AmpProject\Attribute;
use AmpProject\Layout;
use DOMElement;

final class ImageDimensions
{

    /**
     * Images smaller than 150px are considered tiny.
     *
     * @var int
     */
    const TINY_THRESHOLD = 150;

    /**
     * Image that this represents the dimensions of.
     *
     * @var DOMElement
     */
    private $image;

    /**
     * Width of the image.
     *
     * @var int|null
     */
    private $width;

    /**
     * Height of the image.
     *
     * @var int|null
     */
    private $height;

    /**
     * Layout of the image.
     *
     * @var string|null
     */
    private $layout;

    /**
     * ImageDimensions constructor.
     *
     * @param DOMElement $image Image to represent the dimensions of.
     */
    public function __construct(DOMElement $image)
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

            if (! $element instanceof DOMElement) {
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

            return [(int)$width, (int)$height];
        }

        return [-1, -1];
    }

    /**
     * Check whether the image is to be considered tiny and should be ignored.
     *
     * A tiny image is any image with width or height less than 150 pixels and a non-responsive layout.
     *
     * @param int $threshold Optional. Threshold to use. Defaults to 150 pixels.
     * @return bool Whether the image is tiny.
     */
    public function isTiny($threshold = self::TINY_THRESHOLD)
    {
        if ($threshold === null) {
            $threshold = self::TINY_THRESHOLD;
        }

        if (! $this->hasWidth() && ! $this->hasHeight()) {
            if ($this->getLayout() === Layout::FILL) {
                list($this->width, $this->height) = $this->getDimensionsFromParent();
            } else {
                return true;
            }
        }

        if ($this->getWidth() <= 0 || $this->getHeight() <= 0) {
            return true;
        }

        switch ($this->getLayout()) {
            case Layout::INTRINSIC:
            case Layout::RESPONSIVE:
                return false;
            case Layout::FIXED_HEIGHT:
                return true;
            default:
                return $this->getWidth() < $threshold || $this->getHeight() < $threshold;
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
     * @return int|null Width of the image, or null if the image has no width.
     */
    public function getWidth()
    {
        if ($this->width === null) {
            $this->width = -1;
            if ($this->image->hasAttribute(Attribute::WIDTH)) {
                $width = $this->image->getAttribute(Attribute::WIDTH);
                if (! empty($width)) {
                    $this->width = (int)$width;
                }
            }
        }

        return $this->width !== -1 ? $this->width : null;
    }

    /**
     * Get the height of the image.
     *
     * @return int|null Height of the image, or null if the image has no width.
     */
    public function getHeight()
    {
        if ($this->height === null) {
            $this->height = -1;
            if ($this->image->hasAttribute(Attribute::HEIGHT)) {
                $height = $this->image->getAttribute(Attribute::HEIGHT);
                if (! empty($height)) {
                    $this->height = (int)$height;
                }
            }
        }

        return $this->height !== -1 ? $this->height : null;
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
