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
 * Tag class AmpNextPageWithSrcAttribute.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $unique
 * @property-read array<array> $attrs
 * @property-read string $specUrl
 * @property-read array<array> $referencePoints
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpNextPageWithSrcAttribute extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-next-page with src attribute';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::NEXT_PAGE,
        SpecRule::SPEC_NAME => 'amp-next-page with src attribute',
        SpecRule::UNIQUE => true,
        SpecRule::ATTRS => [
            Attribute::DEEP_PARSING => [],
            Attribute::MAX_PAGES => [],
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
            Attribute::XSSI_PREFIX => [],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-next-page/',
        SpecRule::REFERENCE_POINTS => [
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-NEXT-PAGE > [separator]',
                SpecRule::UNIQUE => true,
            ],
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-NEXT-PAGE > [recommendation-box]',
                SpecRule::UNIQUE => true,
            ],
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-NEXT-PAGE > [footer]',
                SpecRule::UNIQUE => true,
            ],
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-NEXT-PAGE > SCRIPT[type=application/json]',
                SpecRule::UNIQUE => true,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::NEXT_PAGE,
        ],
    ];
}
