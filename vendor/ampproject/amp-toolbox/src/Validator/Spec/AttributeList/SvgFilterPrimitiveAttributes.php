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
 * Attribute list class SvgFilterPrimitiveAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $height
 * @property-read array $result
 * @property-read array $width
 * @property-read array $x
 * @property-read array $y
 */
final class SvgFilterPrimitiveAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'svg-filter-primitive-attributes';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::HEIGHT => [],
        Attribute::RESULT => [],
        Attribute::WIDTH => [],
        Attribute::X => [],
        Attribute::Y => [],
    ];
}
