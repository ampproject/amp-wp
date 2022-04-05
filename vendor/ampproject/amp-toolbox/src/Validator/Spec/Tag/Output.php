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
 * Tag class Output.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 */
final class Output extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'OUTPUT';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::OUTPUT,
        SpecRule::ATTRS => [
            Attribute::FOR_ => [],
            Attribute::FORM => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NameAttr::ID,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
            Format::AMP4EMAIL,
        ],
    ];
}
