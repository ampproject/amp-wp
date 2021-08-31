<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class AmpVideoIframeCommon.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $album
 * @property-read array $alt
 * @property-read array $artist
 * @property-read array $artwork
 * @property-read array $attribution
 * @property-read array<array<string>> $autoplay
 * @property-read array<array<string>> $dock
 * @property-read array<array<string>> $implementsMediaSession
 * @property-read array<array<string>> $implementsRotateToFullscreen
 * @property-read array $poster
 * @property-read array $referrerpolicy
 * @property-read array<array<string>> $rotateToFullscreen
 * @property-read array $src
 * @property-read array $src_binding
 */
final class AmpVideoIframeCommon extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-video-iframe-common';

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
        Attribute::DOCK => [
            SpecRule::REQUIRES_EXTENSION => [
                Extension::VIDEO_DOCKING,
            ],
        ],
        Attribute::IMPLEMENTS_MEDIA_SESSION => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::IMPLEMENTS_ROTATE_TO_FULLSCREEN => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::POSTER => [],
        Attribute::REFERRERPOLICY => [],
        Attribute::ROTATE_TO_FULLSCREEN => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::SRC => [
            SpecRule::MANDATORY => true,
            SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTPS,
                ],
            ],
        ],
        '[SRC]' => [],
    ];
}
