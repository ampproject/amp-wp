<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class InteractiveSharedConfigsAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<bool> $id
 * @property-read array $promptText
 * @property-read array $endpoint
 * @property-read array<array<string>> $theme
 * @property-read array<array<string>> $chipStyle
 * @property-read array<array<string>> $promptSize
 */
final class InteractiveSharedConfigsAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'interactive-shared-configs-attrs';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ID => [
            SpecRule::MANDATORY => true,
        ],
        Attribute::PROMPT_TEXT => [],
        Attribute::ENDPOINT => [
            SpecRule::MANDATORY => true,
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTPS,
                ],
                SpecRule::ALLOW_RELATIVE => false,
                SpecRule::ALLOW_EMPTY => false,
            ],
        ],
        Attribute::THEME => [
            SpecRule::VALUE => [
                'light',
                'dark',
            ],
        ],
        Attribute::CHIP_STYLE => [
            SpecRule::VALUE => [
                'shadow',
                'flat',
                'transparent',
            ],
        ],
        Attribute::PROMPT_SIZE => [
            SpecRule::VALUE => [
                'small',
                'medium',
                'large',
            ],
        ],
    ];
}
