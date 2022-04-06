<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class AmpDatePickerStaticModeAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $fullscreen
 */
final class AmpDatePickerStaticModeAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-date-picker-static-mode-attributes';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::FULLSCREEN => [
            SpecRule::VALUE => [
                '',
            ],
        ],
    ];
}
