<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryPage.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $satisfies
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryPage extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-PAGE';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_PAGE,
        SpecRule::MANDATORY_PARENT => Extension::STORY,
        SpecRule::ATTRS => [
            Attribute::AUTO_ADVANCE_AFTER => [],
            Attribute::BACKGROUND_AUDIO => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::NEXT_PAGE_NO_AD => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\MandatoryIdAttr::ID,
        ],
        SpecRule::CHILD_TAGS => [
            SpecRule::CHILD_TAG_NAME_ONEOF => [
                'AMP-ANALYTICS',
                'AMP-PIXEL',
                'AMP-STORY-ANIMATION',
                'AMP-STORY-AUTO-ANALYTICS',
                'AMP-STORY-CTA-LAYER',
                'AMP-STORY-GRID-LAYER',
                'AMP-STORY-PAGE-ATTACHMENT',
                'AMP-STORY-PAGE-OUTLINK',
            ],
            SpecRule::MANDATORY_MIN_NUM_CHILD_TAGS => 1,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::SATISFIES => [
            'amp-story-page',
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::STORY,
        ],
    ];
}
