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
 * Tag class AmphtmlEngineScriptAmp4email.
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
 * @property-read array<array<array<string>>> $cdata
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class AmphtmlEngineScriptAmp4email extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amphtml engine script [AMP4EMAIL]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'amphtml engine script [AMP4EMAIL]',
        SpecRule::MANDATORY => true,
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::SRC => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'https://cdn.ampproject.org/v0.js',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
            AttributeList\AmphtmlEngineAttrs::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#required-markup',
        SpecRule::CDATA => [
            SpecRule::DISALLOWED_CDATA_REGEX => [
                [
                    SpecRule::REGEX => '.',
                    SpecRule::ERROR_MESSAGE => 'contents',
                ],
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4EMAIL,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'amphtml engine script',
    ];
}
