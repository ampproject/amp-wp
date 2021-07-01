<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpTwitter.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpTwitter extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-TWITTER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::TWITTER,
        SpecRule::ATTRS => [
            Attribute::DATA_CARDS => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TWEETID,
                    ],
                ],
            ],
            Attribute::DATA_CONVERSATION => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TWEETID,
                    ],
                ],
            ],
            Attribute::DATA_LIMIT => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_MOMENTID,
                    ],
                ],
            ],
            Attribute::DATA_MOMENTID => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_MOMENTID,
                    Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    Attribute::DATA_TWEETID,
                ],
                SpecRule::VALUE_REGEX => '\d+',
            ],
            Attribute::DATA_TIMELINE_ID => [
                SpecRule::VALUE_REGEX => '\d+',
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    ],
                ],
            ],
            Attribute::DATA_TIMELINE_OWNER_SCREEN_NAME => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    ],
                ],
            ],
            Attribute::DATA_TIMELINE_SLUG => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    ],
                ],
            ],
            Attribute::DATA_TIMELINE_SOURCE_TYPE => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_MOMENTID,
                    Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    Attribute::DATA_TWEETID,
                ],
            ],
            Attribute::DATA_TIMELINE_SCREEN_NAME => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    ],
                ],
            ],
            Attribute::DATA_TIMELINE_URL => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_RELATIVE => false,
                ],
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    ],
                ],
            ],
            Attribute::DATA_TIMELINE_USER_ID => [
                SpecRule::VALUE_REGEX => '\d+',
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    ],
                ],
            ],
            Attribute::DATA_TWEETID => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_MOMENTID,
                    Attribute::DATA_TIMELINE_SOURCE_TYPE,
                    Attribute::DATA_TWEETID,
                ],
            ],
            '[data-tweetid]' => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FILL,
                Layout::FIXED,
                Layout::FIXED_HEIGHT,
                Layout::FLEX_ITEM,
                Layout::INTRINSIC,
                Layout::NODISPLAY,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::TWITTER,
        ],
    ];
}
