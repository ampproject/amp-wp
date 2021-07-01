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
 * Tag class InputMaskDateMmDdYyyy.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class InputMaskDateMmDdYyyy extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'input [mask=date-mm-dd-yyyy]';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::INPUT,
        SpecRule::SPEC_NAME => 'input [mask=date-mm-dd-yyyy]',
        SpecRule::ATTRS => [
            Attribute::MASK => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'date-mm-dd-yyyy',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\AmpInputmaskCommonAttr::ID,
            AttributeList\InputCommonAttr::ID,
            AttributeList\NameAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-inputmask/',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::INPUTMASK,
        ],
    ];
}
