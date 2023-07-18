<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Layout;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpSoundcloud.
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
final class AmpSoundcloud extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-SOUNDCLOUD';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::SOUNDCLOUD,
        SpecRule::ATTRS => [
            Attribute::DATA_COLOR => [
                SpecRule::VALUE_REGEX_CASEI => '([0-9a-f]{3}){1,2}',
            ],
            Attribute::DATA_PLAYLISTID => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_TRACKID,
                    Attribute::DATA_PLAYLISTID,
                ],
                SpecRule::VALUE_REGEX => '[0-9]+',
            ],
            Attribute::DATA_SECRET_TOKEN => [
                SpecRule::VALUE_REGEX => '[A-Za-z0-9_-]+',
            ],
            Attribute::DATA_TRACKID => [
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_TRACKID,
                    Attribute::DATA_PLAYLISTID,
                ],
                SpecRule::VALUE_REGEX => '[0-9]+',
            ],
            Attribute::DATA_VISUAL => [
                SpecRule::VALUE_CASEI => [
                    'false',
                    'true',
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
                Layout::INTRINSIC,
                Layout::NODISPLAY,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::SOUNDCLOUD,
        ],
    ];
}
