<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class NoscriptStyleAmpBoilerplate.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read bool $mandatory
 * @property-read bool $unique
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array $cdata
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $disabledBy
 * @property-read string $descriptiveName
 */
final class NoscriptStyleAmpBoilerplate extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'noscript > style[amp-boilerplate]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::STYLE,
        SpecRule::SPEC_NAME => 'noscript > style[amp-boilerplate]',
        SpecRule::MANDATORY => true,
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::NOSCRIPT,
        SpecRule::ATTRS => [
            Attribute::AMP_BOILERPLATE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_PARENT_DISPATCH',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amp-boilerplate/?format=websites',
        SpecRule::CDATA => [
            SpecRule::CDATA_REGEX => '\s*body\s*{\s*-webkit-animation:\s*none;\s*-moz-animation:\s*none;\s*-ms-animation:\s*none;\s*animation:\s*none;?\s*}\s*',
            SpecRule::DOC_CSS_BYTES => false,
        ],
        SpecRule::MANDATORY_ANCESTOR => Element::HEAD,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DISABLED_BY => [
            Attribute::TRANSFORMED,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'noscript > style[amp-boilerplate]',
    ];
}
