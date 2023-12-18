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
 * Tag class HeroImage.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $disallowedAncestor
 * @property-read array<string> $htmlFormat
 * @property-read string $descriptiveName
 */
final class HeroImage extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'Hero Image';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::IMAGE,
        SpecRule::SPEC_NAME => 'Hero Image',
        SpecRule::ATTRS => [
            Attribute::DATA_HERO => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_DISPATCH',
            ],
            Attribute::DECODING => [
                SpecRule::VALUE_CASEI => [
                    'async',
                ],
            ],
            Attribute::SIZES => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ImgAttrs::ID,
            AttributeList\MandatorySrcOrSrcset::ID,
        ],
        SpecRule::DISALLOWED_ANCESTOR => [
            'AMP-IMG',
            'AMP-STORY',
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'img',
    ];
}
