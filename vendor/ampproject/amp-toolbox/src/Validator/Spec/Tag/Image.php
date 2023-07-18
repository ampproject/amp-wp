<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class Image.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class Image extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'IMAGE';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::IMAGE,
        SpecRule::ATTRS => [
            Attribute::EXTERNALRESOURCESREQUIRED => [],
            Attribute::HEIGHT => [],
            Attribute::PRESERVEASPECTRATIO => [],
            Attribute::TRANSFORM => [],
            Attribute::WIDTH => [],
            Attribute::X => [],
            Attribute::XLINK_ACTUATE => [],
            Attribute::XLINK_ARCROLE => [],
            Attribute::XLINK_HREF => [
                SpecRule::ALTERNATIVE_NAMES => [
                    Attribute::HREF,
                ],
                SpecRule::DISALLOWED_VALUE_REGEX => '(^|\s)data:image\/svg\+xml',
                SpecRule::VALUE_URL => [
                    SpecRule::PROTOCOL => [
                        Protocol::DATA,
                        Protocol::HTTP,
                        Protocol::HTTPS,
                    ],
                    SpecRule::ALLOW_EMPTY => false,
                ],
            ],
            Attribute::XLINK_ROLE => [],
            Attribute::XLINK_SHOW => [],
            Attribute::XLINK_TITLE => [],
            Attribute::XLINK_TYPE => [],
            Attribute::Y => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\SvgConditionalProcessingAttributes::ID,
            AttributeList\SvgCoreAttributes::ID,
            AttributeList\SvgPresentationAttributes::ID,
            AttributeList\SvgStyleAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#svg',
        SpecRule::MANDATORY_ANCESTOR => Element::SVG,
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
    ];
}
