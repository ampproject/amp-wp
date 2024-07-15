<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class MetaNameViewport.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $mandatory
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class MetaNameViewport extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'meta name=viewport';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::META,
        SpecRule::SPEC_NAME => 'meta name=viewport',
        SpecRule::MANDATORY => true,
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::CONTENT => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_PROPERTIES => [
                    SpecRule::PROPERTIES => [
                        [
                            SpecRule::NAME => 'width',
                            SpecRule::MANDATORY => true,
                            SpecRule::VALUE => 'device-width',
                        ],
                        [
                            SpecRule::NAME => 'height',
                        ],
                        [
                            SpecRule::NAME => 'initial-scale',
                        ],
                        [
                            SpecRule::NAME => 'minimum-scale',
                        ],
                        [
                            SpecRule::NAME => 'maximum-scale',
                        ],
                        [
                            SpecRule::NAME => 'shrink-to-fit',
                        ],
                        [
                            SpecRule::NAME => 'user-scalable',
                        ],
                        [
                            SpecRule::NAME => 'viewport-fit',
                        ],
                    ],
                ],
            ],
            Attribute::NAME => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'viewport',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#required-markup',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'meta name=viewport',
    ];
}
