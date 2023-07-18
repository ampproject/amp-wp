<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class FormMethodPostAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class FormMethodPostAmp4email extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'FORM [method=POST] (AMP4EMAIL)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::FORM,
        SpecRule::SPEC_NAME => 'FORM [method=POST] (AMP4EMAIL)',
        SpecRule::ATTRS => [
            Attribute::ACCEPT => [],
            Attribute::ACCEPT_CHARSET => [],
            Attribute::ACTION_XHR => [
                SpecRule::MANDATORY => true,
                SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin|{{|}}',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_RELATIVE => false,
                ],
            ],
            Attribute::AUTOCOMPLETE => [],
            Attribute::CUSTOM_VALIDATION_REPORTING => [
                SpecRule::VALUE => [
                    'as-you-go',
                    'interact-and-submit',
                    'show-all-on-submit',
                    'show-first-on-submit',
                ],
            ],
            Attribute::ENCTYPE => [],
            Attribute::METHOD => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
                SpecRule::VALUE_CASEI => [
                    'post',
                ],
            ],
            Attribute::NOVALIDATE => [],
            Attribute::XSSI_PREFIX => [],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::FORM,
        ],
    ];
}
