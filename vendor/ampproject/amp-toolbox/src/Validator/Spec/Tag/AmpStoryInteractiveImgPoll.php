<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryInteractiveImgPoll.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array<array<array<string>>>> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryInteractiveImgPoll extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-INTERACTIVE-IMG-POLL';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_INTERACTIVE_IMG_POLL,
        SpecRule::ATTRS => [
            Attribute::OPTION_1_RESULTS_CATEGORY => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_2_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_2_RESULTS_CATEGORY => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_1_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_3_RESULTS_CATEGORY => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_2_RESULTS_CATEGORY,
                        Attribute::OPTION_3_IMAGE,
                    ],
                ],
            ],
            Attribute::OPTION_4_RESULTS_CATEGORY => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_3_RESULTS_CATEGORY,
                        Attribute::OPTION_4_IMAGE,
                    ],
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\InteractiveOptionsImgAttrs::ID,
            AttributeList\InteractiveOptionsConfettiAttrs::ID,
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
