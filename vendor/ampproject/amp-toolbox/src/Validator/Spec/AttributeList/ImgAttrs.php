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
 * Attribute list class ImgAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $align
 * @property-read array $alt
 * @property-read array $border
 * @property-read array<array<string>> $crossorigin
 * @property-read array $height
 * @property-read array $hspace
 * @property-read array<array<string>> $importance
 * @property-read array $ismap
 * @property-read array<array<string>> $loading
 * @property-read array $name
 * @property-read array<array<string>> $referrerpolicy
 * @property-read array $usemap
 * @property-read array $vspace
 * @property-read array $width
 */
final class ImgAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'img-attrs';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ALIGN => [
            SpecRule::VALUE_CASEI => [
                'top',
                'middle',
                'bottom',
                'left',
                'right',
            ],
        ],
        Attribute::ALT => [],
        Attribute::BORDER => [],
        Attribute::CROSSORIGIN => [
            SpecRule::VALUE_CASEI => [
                'anonymous',
                'use-credentials',
            ],
        ],
        Attribute::HEIGHT => [],
        Attribute::HSPACE => [],
        Attribute::IMPORTANCE => [
            SpecRule::VALUE_CASEI => [
                'high',
                'low',
                'auto',
            ],
        ],
        Attribute::ISMAP => [],
        Attribute::LOADING => [
            SpecRule::VALUE_CASEI => [
                'lazy',
            ],
        ],
        Attribute::NAME => [],
        Attribute::REFERRERPOLICY => [
            SpecRule::VALUE_CASEI => [
                'no-referrer',
                'no-referrer-when-downgrade',
                'origin',
                'origin-when-cross-origin',
                'same-origin',
                'strict-origin',
                'strict-origin-when-cross-origin',
                'unsafe-url',
            ],
        ],
        Attribute::USEMAP => [],
        Attribute::VSPACE => [],
        Attribute::WIDTH => [],
    ];
}
