<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpAutocomplete.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpAutocomplete extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-autocomplete';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::AUTOCOMPLETE,
        SpecRule::SPEC_NAME => 'amp-autocomplete',
        SpecRule::ATTRS => [
            Attribute::FILTER => [
                SpecRule::MANDATORY => true,
                SpecRule::TRIGGER => [
                    SpecRule::IF_VALUE_REGEX => 'custom',
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::FILTER_EXPR,
                    ],
                ],
                SpecRule::VALUE_CASEI => [
                    'custom',
                    'fuzzy',
                    'none',
                    'prefix',
                    'substring',
                    'token-prefix',
                ],
            ],
            Attribute::FILTER_EXPR => [
                SpecRule::REQUIRES_EXTENSION => [
                    Extension::BIND,
                ],
            ],
            Attribute::FILTER_VALUE => [],
            Attribute::HIGHLIGHT_USER_ENTRY => [],
            Attribute::INLINE => [],
            Attribute::ITEMS => [],
            Attribute::MAX_ENTRIES => [],
            Attribute::MAX_ITEMS => [],
            Attribute::MIN_CHARACTERS => [],
            Attribute::PREFETCH => [],
            Attribute::QUERY => [
                SpecRule::TRIGGER => [
                    SpecRule::ALSO_REQUIRES_ATTR => [
                        Attribute::SRC,
                    ],
                ],
            ],
            Attribute::SRC => [
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_RELATIVE => true,
                ],
            ],
            Attribute::SUBMIT_ON_ENTER => [],
            Attribute::SUGGEST_FIRST => [],
            Attribute::TEMPLATE => [],
            '[src]' => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-autocomplete/',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::CONTAINER,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::AUTOCOMPLETE,
        ],
    ];
}
