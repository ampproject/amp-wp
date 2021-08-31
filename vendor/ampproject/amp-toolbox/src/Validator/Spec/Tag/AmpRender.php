<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpRender.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpRender extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-RENDER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::RENDER,
        SpecRule::ATTRS => [
            Attribute::BINDING => [
                SpecRule::VALUE => [
                    'always',
                    'never',
                    'no',
                    'refresh',
                ],
            ],
            Attribute::CREDENTIALS => [],
            Attribute::DATA_AMP_BIND_SRC => [
                SpecRule::MANDATORY_ANYOF => [
                    Attribute::SRC,
                    '[src]',
                    Attribute::DATA_AMP_BIND_SRC,
                ],
            ],
            Attribute::KEY => [],
            Attribute::SRC => [
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::AMP_SCRIPT,
                        Protocol::AMP_STATE,
                        Protocol::HTTPS,
                    ],
                ],
                SpecRule::MANDATORY_ANYOF => [
                    Attribute::SRC,
                    '[src]',
                    Attribute::DATA_AMP_BIND_SRC,
                ],
            ],
            Attribute::XSSI_PREFIX => [],
            '[src]' => [
                SpecRule::MANDATORY_ANYOF => [
                    Attribute::SRC,
                    '[src]',
                    Attribute::DATA_AMP_BIND_SRC,
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-render/',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::NODISPLAY,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::RENDER,
        ],
    ];
}
