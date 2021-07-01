<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Internal;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class ImgIAmphtmlIntrinsicSizer.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 */
final class ImgIAmphtmlIntrinsicSizer extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'IMG-I-AMPHTML-INTRINSIC-SIZER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::IMG,
        SpecRule::SPEC_NAME => 'IMG-I-AMPHTML-INTRINSIC-SIZER',
        SpecRule::MANDATORY_PARENT => Internal::SIZER_INTRINSIC,
        SpecRule::ATTRS => [
            Attribute::ALT => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::ARIA_HIDDEN => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'true',
                ],
            ],
            Attribute::CLASS_ => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'i-amphtml-intrinsic-sizer',
                ],
            ],
            Attribute::ROLE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'presentation',
                ],
            ],
            Attribute::SRC => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => 'data:image\/svg\+xml;charset=utf-8,\s*<svg height="\d+(\.\d+)?" width="\d+(\.\d+)?" xmlns="http:\/\/www\.w3\.org\/2000\/svg" version="1\.1"\/>|data:image\/svg\+xml;charset=utf-8,\s*<svg height=\'\d+(\.\d+)?\' width=\'\d+(\.\d+)?\' xmlns=\'http:\/\/www\.w3\.org\/2000\/svg\' version=\'1\.1\'\/>|data:image\/svg\+xml;base64,[a-zA-Z0-9+\/=]+',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::ENABLED_BY => [
            Attribute::TRANSFORMED,
        ],
    ];
}
