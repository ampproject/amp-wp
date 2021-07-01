<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpAnalytics.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array> $attrs
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpAnalytics extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-ANALYTICS';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::ANALYTICS,
        SpecRule::ATTRS => [
            Attribute::CONFIG => [
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_RELATIVE => true,
                    SpecRule::ALLOW_EMPTY => true,
                ],
            ],
            Attribute::TYPE => [],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-analytics/',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::ANALYTICS,
        ],
    ];
}
