<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryInteractiveResults.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStoryInteractiveResults extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-INTERACTIVE-RESULTS';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY_INTERACTIVE_RESULTS,
        SpecRule::ATTRS => [
            Attribute::OPTION_1_RESULTS_CATEGORY => [
                SpecRule::MANDATORY => true,
            ],
            Attribute::OPTION_2_RESULTS_CATEGORY => [
                SpecRule::MANDATORY => true,
            ],
            Attribute::OPTION_3_RESULTS_CATEGORY => [],
            Attribute::OPTION_4_RESULTS_CATEGORY => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_3_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_1_IMAGE => [],
            Attribute::OPTION_2_IMAGE => [],
            Attribute::OPTION_3_IMAGE => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_3_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_4_IMAGE => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_4_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_1_TEXT => [],
            Attribute::OPTION_2_TEXT => [],
            Attribute::OPTION_3_TEXT => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_3_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_4_TEXT => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_4_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_1_RESULTS_THRESHOLD => [
                SpecRule::VALUE_REGEX => '\d+(\.\d+)?',
            ],
            Attribute::OPTION_2_RESULTS_THRESHOLD => [
                SpecRule::VALUE_REGEX => '\d+(\.\d+)?',
            ],
            Attribute::OPTION_3_RESULTS_THRESHOLD => [
                SpecRule::VALUE_REGEX => '\d+(\.\d+)?',
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_3_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::OPTION_4_RESULTS_THRESHOLD => [
                SpecRule::VALUE_REGEX => '\d+(\.\d+)?',
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::OPTION_4_RESULTS_CATEGORY,
                    ],
                ],
            ],
            Attribute::PROMPT_TEXT => [],
            Attribute::THEME => [
                SpecRule::VALUE => [
                    'light',
                    'dark',
                ],
            ],
            Attribute::CHIP_STYLE => [
                SpecRule::VALUE => [
                    'flat',
                    'transparent',
                ],
            ],
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
