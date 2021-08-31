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
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStory.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requires
 * @property-read array<string> $requiresExtension
 * @property-read bool $siblingsDisallowed
 */
final class AmpStory extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STORY,
        SpecRule::MANDATORY_PARENT => Element::BODY,
        SpecRule::ATTRS => [
            Attribute::BACKGROUND_AUDIO => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::ENTITY => [],
            Attribute::ENTITY_LOGO_SRC => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::ENTITY_URL => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::POSTER_LANDSCAPE_SRC => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::POSTER_PORTRAIT_SRC => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::POSTER_SQUARE_SRC => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::PUBLISHER => [
                SpecRule::MANDATORY => true,
            ],
            Attribute::PUBLISHER_LOGO_SRC => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::STANDALONE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::SUPPORTS_LANDSCAPE => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::TITLE => [
                SpecRule::MANDATORY => true,
            ],
            Attribute::LIVE_STORY => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::LIVE_STORY_DISABLED => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
        ],
        SpecRule::CHILD_TAGS => [
            SpecRule::CHILD_TAG_NAME_ONEOF => [
                'AMP-ANALYTICS',
                'AMP-CONSENT',
                'AMP-GEO',
                'AMP-PIXEL',
                'AMP-SIDEBAR',
                'AMP-STORY-AUTO-ADS',
                'AMP-STORY-AUTO-ANALYTICS',
                'AMP-STORY-BOOKEND',
                'AMP-STORY-PAGE',
                'AMP-STORY-SOCIAL-SHARE',
            ],
            SpecRule::MANDATORY_MIN_NUM_CHILD_TAGS => 1,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES => [
            'amp-story-page',
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::STORY,
        ],
        SpecRule::SIBLINGS_DISALLOWED => true,
    ];
}
