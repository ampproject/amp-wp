<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Html\Attribute;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class AmpAudioCommon.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $album
 * @property-read array $artist
 * @property-read array $artwork
 * @property-read array $controls
 * @property-read array $controlslist
 * @property-read array<array<string>> $loop
 * @property-read array<array<string>> $muted
 * @property-read array $src
 * @property-read array $album_binding
 * @property-read array $artist_binding
 * @property-read array $artwork_binding
 * @property-read array $controlslist_binding
 * @property-read array $loop_binding
 * @property-read array $src_binding
 * @property-read array $title_binding
 */
final class AmpAudioCommon extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-audio-common';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ALBUM => [],
        Attribute::ARTIST => [],
        Attribute::ARTWORK => [],
        Attribute::CONTROLS => [],
        Attribute::CONTROLSLIST => [],
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
        '[ARTIST]' => [],
        '[ARTWORK]' => [],
        '[CONTROLSLIST]' => [],
        '[LOOP]' => [],
        '[SRC]' => [],
        '[TITLE]' => [],
    ];
}
