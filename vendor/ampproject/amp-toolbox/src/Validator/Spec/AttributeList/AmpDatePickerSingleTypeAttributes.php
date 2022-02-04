<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;

/**
 * Attribute list class AmpDatePickerSingleTypeAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $date
 * @property-read array $inputSelector
 */
final class AmpDatePickerSingleTypeAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-date-picker-single-type-attributes';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::DATE => [],
        Attribute::INPUT_SELECTOR => [],
    ];
}
