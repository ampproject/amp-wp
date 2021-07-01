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
 * Tag class MetaNameAmp3pIframeSrc.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 */
final class MetaNameAmp3pIframeSrc extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'meta name=amp-3p-iframe-src';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::META,
        SpecRule::SPEC_NAME => 'meta name=amp-3p-iframe-src',
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::CONTENT => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTPS,
                    ],
                ],
            ],
            Attribute::NAME => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
                SpecRule::VALUE_CASEI => [
                    'amp-3p-iframe-src',
                ],
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-ad/',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
