<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Protocol;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class VideoSource.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 */
final class VideoSource extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'video > source';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SOURCE,
        SpecRule::SPEC_NAME => 'video > source',
        SpecRule::MANDATORY_PARENT => Element::VIDEO,
        SpecRule::ATTRS => [
            Attribute::MEDIA => [],
            Attribute::SRC => [
                SpecRule::MANDATORY => true,
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_RELATIVE => true,
                ],
            ],
            Attribute::TYPE => [
                SpecRule::MANDATORY => true,
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-video/',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
    ];
}
