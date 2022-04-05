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
 * Tag class ImgUsingSrcset.
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
final class ImgUsingSrcset extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'Img using srcset';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::IMG,
        SpecRule::SPEC_NAME => 'Img using srcset',
        SpecRule::ATTRS => [
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
