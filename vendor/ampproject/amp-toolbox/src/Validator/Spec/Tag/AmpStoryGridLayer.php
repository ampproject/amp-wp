<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Validator\Spec\DescendantTagList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryGridLayer.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array<array<string>> $referencePoints
 * @property-read array<string> $htmlFormat
 * @property-read string $descendantTagList
 */
final class AmpStoryGridLayer extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-GRID-LAYER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_GRID_LAYER,
        SpecRule::ATTRS => [
            Attribute::ANCHOR => [
                SpecRule::VALUE_REGEX => 'top|bottom|left|right|(top|bottom)[ -](left|right)|(left|right)[ -](top|bottom)',
            ],
            Attribute::ASPECT_RATIO => [
                SpecRule::VALUE_REGEX => '\d+:\d+',
            ],
            Attribute::POSITION => [
                SpecRule::VALUE => [
                    'landscape-half-left',
                    'landscape-half-right',
                ],
            ],
            Attribute::PRESET => [
                SpecRule::VALUE => [
                    '2021-background',
                    '2021-foreground',
                ],
            ],
            Attribute::TEMPLATE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'fill',
                    'horizontal',
                    'thirds',
                    'vertical',
                ],
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY_PAGE,
        SpecRule::REFERENCE_POINTS => [
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-STORY-GRID-LAYER default',
            ],
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-STORY-GRID-LAYER animate-in',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DESCENDANT_TAG_LIST => DescendantTagList\AmpStoryGridLayerAllowedDescendants::ID,
    ];
}
