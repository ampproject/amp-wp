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
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpNexxtvPlayer.
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
final class AmpNexxtvPlayer extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-NEXXTV-PLAYER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::NEXXTV_PLAYER,
        SpecRule::ATTRS => [
            Attribute::DATA_CLIENT => [
                SpecRule::MANDATORY => true,
            ],
            Attribute::DATA_MEDIAID => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => '[^=/?:]+',
            ],
            Attribute::DATA_MODE => [
                SpecRule::VALUE => [
                    'api',
                    'static',
                ],
            ],
            Attribute::DATA_STREAMTYPE => [
                SpecRule::VALUE => [
                    'album',
                    'audio',
                    'audioalbum',
                    'collection',
                    'live',
                    'playlist',
                    'playlist-marked',
                    'radio',
                    'set',
                    'video',
                ],
            ],
            Attribute::DATA_EXIT_MODE => [
                SpecRule::VALUE => [
                    'load',
                    'loop',
                    'replay',
                ],
            ],
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
                Layout::NODISPLAY,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::NEXXTV_PLAYER,
        ],
    ];
}
