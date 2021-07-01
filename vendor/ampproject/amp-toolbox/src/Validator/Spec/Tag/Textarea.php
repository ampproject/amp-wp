<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class Textarea.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 */
final class Textarea extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'TEXTAREA';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::TEXTAREA,
        SpecRule::ATTRS => [
            Attribute::AUTOCOMPLETE => [],
            Attribute::AUTOEXPAND => [
                SpecRule::REQUIRES_EXTENSION => [
                    Extension::FORM,
                ],
            ],
            Attribute::AUTOFOCUS => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            Attribute::COLS => [],
            Attribute::DISABLED => [],
            Attribute::MAXLENGTH => [],
            Attribute::MINLENGTH => [],
            Attribute::NO_VERIFY => [
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            Attribute::PATTERN => [],
            Attribute::PLACEHOLDER => [],
            Attribute::READONLY => [],
            Attribute::REQUIRED => [],
            Attribute::ROWS => [],
            Attribute::SELECTIONDIRECTION => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            Attribute::SELECTIONEND => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            Attribute::SELECTIONSTART => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            Attribute::SPELLCHECK => [],
            Attribute::WRAP => [],
            '[autocomplete]' => [],
            '[autofocus]' => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            '[cols]' => [],
            '[defaulttext]' => [],
            '[disabled]' => [],
            '[maxlength]' => [],
            '[minlength]' => [],
            '[pattern]' => [],
            '[placeholder]' => [],
            '[readonly]' => [],
            '[required]' => [],
            '[rows]' => [],
            '[selectiondirection]' => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            '[selectionend]' => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            '[selectionstart]' => [
                SpecRule::DISABLED_BY => [
                    Attribute::AMP4EMAIL,
                ],
            ],
            '[spellcheck]' => [],
            '[wrap]' => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NameAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-form/',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
            Format::AMP4EMAIL,
        ],
    ];
}
