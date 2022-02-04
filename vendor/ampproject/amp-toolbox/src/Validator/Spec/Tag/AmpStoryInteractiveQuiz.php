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
 * Tag class AmpStoryInteractiveQuiz.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryInteractiveQuiz extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-INTERACTIVE-QUIZ';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_INTERACTIVE_QUIZ,
        SpecRule::ATTRS => [
            Attribute::OPTION_1_CORRECT => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::OPTION_1_CORRECT,
                    Attribute::OPTION_2_CORRECT,
                    Attribute::OPTION_3_CORRECT,
                    Attribute::OPTION_4_CORRECT,
                ],
            ],
            Attribute::OPTION_2_CORRECT => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::OPTION_1_CORRECT,
                    Attribute::OPTION_2_CORRECT,
                    Attribute::OPTION_3_CORRECT,
                    Attribute::OPTION_4_CORRECT,
                ],
            ],
            Attribute::OPTION_3_CORRECT => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::OPTION_1_CORRECT,
                    Attribute::OPTION_2_CORRECT,
                    Attribute::OPTION_3_CORRECT,
                    Attribute::OPTION_4_CORRECT,
                ],
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_3_TEXT,
                    ],
                ],
            ],
            Attribute::OPTION_4_CORRECT => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::OPTION_1_CORRECT,
                    Attribute::OPTION_2_CORRECT,
                    Attribute::OPTION_3_CORRECT,
                    Attribute::OPTION_4_CORRECT,
                ],
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_4_TEXT,
                    ],
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\InteractiveOptionsTextAttrs::ID,
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
