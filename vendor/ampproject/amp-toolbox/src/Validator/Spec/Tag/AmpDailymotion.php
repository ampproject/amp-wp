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
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpDailymotion.
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
final class AmpDailymotion extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-DAILYMOTION';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::DAILYMOTION,
        SpecRule::ATTRS => [
            Attribute::AUTOPLAY => [],
            Attribute::DATA_ENDSCREEN_ENABLE => [
                SpecRule::VALUE => [
                    'false',
                    'true',
                ],
            ],
            Attribute::DATA_INFO => [
                SpecRule::VALUE => [
                    'false',
                    'true',
                ],
            ],
            Attribute::DATA_MUTE => [
                SpecRule::VALUE => [
                    'false',
                    'true',
                ],
            ],
            Attribute::DATA_SHARING_ENABLE => [
                SpecRule::VALUE => [
                    'false',
                    'true',
                ],
            ],
            Attribute::DATA_START => [
                SpecRule::VALUE_REGEX => '[0-9]+',
            ],
            Attribute::DATA_UI_HIGHLIGHT => [
                SpecRule::VALUE_REGEX_CASEI => '([0-9a-f]{3}){1,2}',
            ],
            Attribute::DATA_UI_LOGO => [
                SpecRule::VALUE => [
                    'false',
                    'true',
                ],
            ],
            Attribute::DATA_VIDEOID => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX_CASEI => '[a-z0-9]+',
            ],
            Attribute::DOCK => [
                SpecRule::REQUIRES_EXTENSION => [
                    Extension::VIDEO_DOCKING,
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-dailymotion/',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::DAILYMOTION,
        ],
    ];
}
