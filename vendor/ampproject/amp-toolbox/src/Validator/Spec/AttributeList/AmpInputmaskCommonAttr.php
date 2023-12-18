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
 * Attribute list class AmpInputmaskCommonAttr.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<array<string>>> $maskOutput
 * @property-read array<array<string>> $type
 * @property-read array $type_binding
 */
final class AmpInputmaskCommonAttr extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-inputmask-common-attr';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::MASK_OUTPUT => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::MASK,
                ],
            ],
        ],
        Attribute::TYPE => [
            SpecRule::VALUE => [
                'text',
                'tel',
                'search',
            ],
        ],
        '[TYPE]' => [],
    ];
}
