<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Layout;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpIframely.
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
final class AmpIframely extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-IFRAMELY';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::IFRAMELY,
        SpecRule::ATTRS => [
            Attribute::DATA_ID => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_ID,
                    Attribute::DATA_URL,
                ],
            ],
            Attribute::DATA_URL => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_ID,
                    Attribute::DATA_URL,
                ],
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_KEY,
                    ],
                ],
            ],
            Attribute::DATA_KEY => [],
            Attribute::DATA_IMG => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::DATA_BORDER => [
                SpecRule::VALUE_REGEX => '(\d+)',
            ],
            Attribute::DATA_DOMAIN => [
                SpecRule::VALUE_REGEX => '^((?:[^\.\/]+\.)?iframe\.ly|if\-cdn\.com|iframely\.net|oembed\.vice\.com|iframe\.nbcnews\.com)$',
            ],
            Attribute::RESIZABLE => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-iframely',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::RESPONSIVE,
                Layout::INTRINSIC,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::IFRAMELY,
        ],
    ];
}
