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
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpJwplayer.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpJwplayer extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-JWPLAYER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::JWPLAYER,
        SpecRule::ATTRS => [
            Attribute::AUTOPLAY => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::DATA_MEDIA_ID => [
                SpecRule::VALUE_REGEX_CASEI => '[0-9a-z]{8}|outstream',
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_MEDIA_ID,
                    Attribute::DATA_PLAYLIST_ID,
                ],
            ],
            Attribute::DATA_PLAYER_ID => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX_CASEI => '[0-9a-z]{8}',
            ],
            Attribute::DATA_PLAYLIST_ID => [
                SpecRule::VALUE_REGEX_CASEI => '[0-9a-z]{8}',
                SpecRule::MANDATORY_ONEOF => [
                    Attribute::DATA_MEDIA_ID,
                    Attribute::DATA_PLAYLIST_ID,
                ],
            ],
            Attribute::DOCK => [
                SpecRule::REQUIRES_EXTENSION => [
                    Extension::VIDEO_DOCKING,
                ],
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-jwplayer/',
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
            Extension::JWPLAYER,
        ],
    ];
}
