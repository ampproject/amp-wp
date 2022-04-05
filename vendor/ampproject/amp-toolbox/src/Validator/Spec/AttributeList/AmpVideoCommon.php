<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class AmpVideoCommon.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $album
 * @property-read array $alt
 * @property-read array $artist
 * @property-read array $artwork
 * @property-read array $attribution
 * @property-read array<array<string>> $autoplay
 * @property-read array<array<string>> $controls
 * @property-read array $controlslist
 * @property-read array $crossorigin
 * @property-read array<array<string>> $disableremoteplayback
 * @property-read array<array<string>> $dock
 * @property-read array<array<string>> $loop
 * @property-read array<array<string>> $muted
 * @property-read array<array<string>> $noaudio
 * @property-read array $objectFit
 * @property-read array $objectPosition
 * @property-read array $placeholder
 * @property-read array<array<string>> $preload
 * @property-read array<array<string>> $rotateToFullscreen
 * @property-read array $src
 * @property-read array $album_binding
 * @property-read array $alt_binding
 * @property-read array $artist_binding
 * @property-read array $artwork_binding
 * @property-read array $attribution_binding
 * @property-read array $controls_binding
 * @property-read array $controlslist_binding
 * @property-read array $loop_binding
 * @property-read array $poster_binding
 * @property-read array $preload_binding
 * @property-read array $src_binding
 * @property-read array $title_binding
 */
final class AmpVideoCommon extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-video-common';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ALBUM => [],
        Attribute::ALT => [],
        Attribute::ARTIST => [],
        Attribute::ARTWORK => [],
        Attribute::ATTRIBUTION => [],
        Attribute::AUTOPLAY => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::CONTROLS => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::CONTROLSLIST => [],
        Attribute::CROSSORIGIN => [],
        Attribute::DISABLEREMOTEPLAYBACK => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::DOCK => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::VIDEO_DOCKING,
            ],
        ],
        Attribute::LOOP => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::MUTED => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::NOAUDIO => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::OBJECT_FIT => [],
        Attribute::OBJECT_POSITION => [],
        Attribute::PLACEHOLDER => [],
        Attribute::PRELOAD => [
            SpecRule::VALUE => [
                'auto',
                'metadata',
                'none',
                '',
            ],
        ],
        Attribute::ROTATE_TO_FULLSCREEN => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::SRC => [
            SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTPS,
                ],
                SpecRule::ALLOW_RELATIVE => true,
            ],
        ],
        '[ALBUM]' => [],
        '[ALT]' => [],
        '[ARTIST]' => [],
        '[ARTWORK]' => [],
        '[ATTRIBUTION]' => [],
        '[CONTROLS]' => [],
        '[CONTROLSLIST]' => [],
        '[LOOP]' => [],
        '[POSTER]' => [],
        '[PRELOAD]' => [],
        '[SRC]' => [],
        '[TITLE]' => [],
    ];
}
