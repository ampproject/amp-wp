<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class StyleAmpRuntimeTransformed.
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
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 * @property-read string $descriptiveName
 */
final class StyleAmpRuntimeTransformed extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'style[amp-runtime] (transformed)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::STYLE,
        SpecRule::SPEC_NAME => 'style[amp-runtime] (transformed)',
        SpecRule::MANDATORY => true,
        SpecRule::UNIQUE => true,
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::AMP_RUNTIME => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    '',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_PARENT_DISPATCH',
            ],
            Attribute::I_AMPHTML_VERSION => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => '^\d{15}|latest$',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#stylesheets',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::ENABLED_BY => [
            Attribute::TRANSFORMED,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'style[amp-runtime]',
    ];
}
