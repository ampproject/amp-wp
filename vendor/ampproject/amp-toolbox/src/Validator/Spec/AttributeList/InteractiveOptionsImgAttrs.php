<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Html\Attribute;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class InteractiveOptionsImgAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $option1Image
 * @property-read array $option2Image
 * @property-read array<array<array<string>>> $option3Image
 * @property-read array<array<array<string>>> $option4Image
 * @property-read array<bool> $option1ImageAlt
 * @property-read array<bool> $option2ImageAlt
 * @property-read array<array<array<string>>> $option3ImageAlt
 * @property-read array<array<array<string>>> $option4ImageAlt
 */
final class InteractiveOptionsImgAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'interactive-options-img-attrs';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::OPTION_1_IMAGE => [
            SpecRule::MANDATORY => true,
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTP,
                    Protocol::HTTPS,
                ],
            ],
        ],
        Attribute::OPTION_2_IMAGE => [
            SpecRule::MANDATORY => true,
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTP,
                    Protocol::HTTPS,
                ],
            ],
        ],
        Attribute::OPTION_3_IMAGE => [
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTP,
                    Protocol::HTTPS,
                ],
            ],
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_3_IMAGE_ALT,
                ],
            ],
        ],
        Attribute::OPTION_4_IMAGE => [
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTP,
                    Protocol::HTTPS,
                ],
            ],
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_3_IMAGE,
                    Attribute::OPTION_4_IMAGE_ALT,
                ],
            ],
        ],
        Attribute::OPTION_1_IMAGE_ALT => [
            SpecRule::MANDATORY => true,
        ],
        Attribute::OPTION_2_IMAGE_ALT => [
            SpecRule::MANDATORY => true,
        ],
        Attribute::OPTION_3_IMAGE_ALT => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_3_IMAGE,
                ],
            ],
        ],
        Attribute::OPTION_4_IMAGE_ALT => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_4_IMAGE,
                ],
            ],
        ],
    ];
}
