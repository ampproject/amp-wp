<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryInteractivePoll.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<string> $attrLists
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryInteractivePoll extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-INTERACTIVE-POLL';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_INTERACTIVE_POLL,
        SpecRule::ATTR_LISTS => [
            AttributeList\InteractiveOptionsTextAttrs::ID,
            AttributeList\InteractiveOptionsConfettiAttrs::ID,
            AttributeList\InteractiveOptionsResultsCategoryAttrs::ID,
            AttributeList\InteractiveSharedConfigsAttrs::ID,
        ],
        SpecRule::MANDATORY_ANCESTOR => Extension::STORY_GRID_LAYER,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::STORY_INTERACTIVE,
        ],
    ];
}
