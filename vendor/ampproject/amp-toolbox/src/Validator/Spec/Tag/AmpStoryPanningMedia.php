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
 * Tag class AmpStoryPanningMedia.
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
final class AmpStoryPanningMedia extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-PANNING-MEDIA';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_PANNING_MEDIA,
        SpecRule::ATTRS => [
            Attribute::DATA_X => [
                SpecRule::VALUE_REGEX => '-?(0|[0-9]?\d\.?\d*%|100%)',
            ],
            Attribute::DATA_Y => [
                SpecRule::VALUE_REGEX => '-?(0|[0-9]?\d\.?\d*%|100%)',
            ],
            Attribute::DATA_ZOOM => [
                SpecRule::VALUE_REGEX => '\d+\.?\d*',
            ],
            Attribute::LOCK_BOUNDS => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-story-panning-media',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY_GRID_LAYER,
        SpecRule::CHILD_TAGS => [
            SpecRule::MANDATORY_NUM_CHILD_TAGS => 1,
            SpecRule::CHILD_TAG_NAME_ONEOF => [
                'AMP-IMG',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::STORY_PANNING_MEDIA,
        ],
    ];
}
