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
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStory360.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read string $mandatoryAncestor
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStory360 extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-360';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_360,
        SpecRule::ATTRS => [
            Attribute::CONTROLS => [
                SpecRule::VALUE => [
                    'gyroscope',
                ],
            ],
            Attribute::DURATION => [
                SpecRule::VALUE_REGEX => '([0-9\.]+)\s*(s|ms)',
            ],
            Attribute::HEADING_END => [
                SpecRule::VALUE_REGEX => '-?\d+\.?\d*',
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DURATION,
                    ],
                ],
            ],
            Attribute::HEADING_START => [
                SpecRule::VALUE_REGEX => '-?\d+\.?\d*',
            ],
            Attribute::PITCH_END => [
                SpecRule::VALUE_REGEX => '-?\d+\.?\d*',
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DURATION,
                    ],
                ],
            ],
            Attribute::PITCH_START => [
                SpecRule::VALUE_REGEX => '-?\d+\.?\d*',
            ],
            Attribute::SCENE_HEADING => [
                SpecRule::VALUE_REGEX => '-?\d+\.?\d*',
            ],
            Attribute::SCENE_PITCH => [
                SpecRule::VALUE_REGEX => '-?\d+\.?\d*',
            ],
            Attribute::SCENE_ROLL => [
                SpecRule::VALUE_REGEX => '-?\d+\.?\d*',
            ],
            Attribute::ZOOM_END => [
                SpecRule::VALUE_REGEX => '\d+\.?\d*',
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DURATION,
                    ],
                ],
            ],
            Attribute::ZOOM_START => [
                SpecRule::VALUE_REGEX => '\d+\.?\d*',
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-story-360',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::NODISPLAY,
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY,
        SpecRule::CHILD_TAGS => [
            SpecRule::MANDATORY_NUM_CHILD_TAGS => 1,
            SpecRule::CHILD_TAG_NAME_ONEOF => [
                'AMP-IMG',
                'AMP-VIDEO',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::STORY_360,
        ],
    ];
}
