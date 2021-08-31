<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class TrackAttrsNoSubtitles.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $default
 * @property-read array<array<string>> $kind
 * @property-read array $label
 * @property-read array $src
 * @property-read array $srclang
 */
final class TrackAttrsNoSubtitles extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'track-attrs-no-subtitles';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::DEFAULT_ => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::KIND => [
            SpecRule::VALUE => [
                'captions',
                'chapters',
                'descriptions',
                'metadata',
            ],
        ],
        Attribute::LABEL => [],
        Attribute::SRC => [
            SpecRule::MANDATORY => true,
            SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTPS,
                ],
                SpecRule::ALLOW_RELATIVE => false,
            ],
        ],
        Attribute::SRCLANG => [],
    ];
}
