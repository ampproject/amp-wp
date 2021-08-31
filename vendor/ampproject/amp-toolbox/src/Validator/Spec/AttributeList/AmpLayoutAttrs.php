<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;

/**
 * Attribute list class AmpLayoutAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $disableInlineWidth
 * @property-read array $height
 * @property-read array $heights
 * @property-read array $layout
 * @property-read array $sizes
 * @property-read array $width
 * @property-read array $height_binding
 * @property-read array $width_binding
 */
final class AmpLayoutAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = '$AMP_LAYOUT_ATTRS';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::DISABLE_INLINE_WIDTH => [],
        Attribute::HEIGHT => [],
        Attribute::HEIGHTS => [],
        Attribute::LAYOUT => [],
        Attribute::SIZES => [],
        Attribute::WIDTH => [],
        '[HEIGHT]' => [],
        '[WIDTH]' => [],
    ];
}
