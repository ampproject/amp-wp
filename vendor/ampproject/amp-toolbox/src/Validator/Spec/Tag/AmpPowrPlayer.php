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
 * Tag class AmpPowrPlayer.
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
final class AmpPowrPlayer extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-POWR-PLAYER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::POWR_PLAYER,
        SpecRule::ATTRS => [
            Attribute::AUTOPLAY => [],
            Attribute::DATA_ACCOUNT => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => '[0-9a-zA-Z-]+',
            ],
            Attribute::DATA_PLAYER => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => '[0-9a-zA-Z-]+',
            ],
            '[data-referrer]' => [],
            Attribute::DATA_TERMS => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_VIDEO,
                    Attribute::DATA_TERMS,
                ],
            ],
            Attribute::DATA_VIDEO => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_VIDEO,
                    Attribute::DATA_TERMS,
                ],
                SpecRule::VALUE_REGEX => '[0-9a-zA-Z-]+',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-powr-player/',
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
            Extension::POWR_PLAYER,
        ],
    ];
}
