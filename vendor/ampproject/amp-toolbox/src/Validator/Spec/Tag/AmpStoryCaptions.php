<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryCaptions.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read string $mandatoryAncestor
 * @property-read array<int> $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryCaptions extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-CAPTIONS';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_CAPTIONS,
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-story-captions',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::CONTAINER,
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::FLUID,
                Layout::INTRINSIC,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY,
        SpecRule::CHILD_TAGS => [
            SpecRule::MANDATORY_NUM_CHILD_TAGS => 0,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::STORY_CAPTIONS,
        ],
    ];
}
