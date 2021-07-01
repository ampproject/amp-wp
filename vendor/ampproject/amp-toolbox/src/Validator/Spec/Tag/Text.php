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
 * Tag class Text.
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
final class Text extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'TEXT';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::TEXT,
        SpecRule::ATTRS => [
            Attribute::DX => [],
            Attribute::DY => [],
            Attribute::EXTERNALRESOURCESREQUIRED => [],
            Attribute::LENGTHADJUST => [],
            Attribute::ROTATE => [],
            Attribute::TEXT_ANCHOR => [],
            Attribute::TEXTLENGTH => [],
            Attribute::TRANSFORM => [],
            Attribute::X => [],
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
