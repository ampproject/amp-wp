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
 * Tag class BlockquoteWithTiktok.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $mandatoryAncestor
 * @property-read array<string> $htmlFormat
 */
final class BlockquoteWithTiktok extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'BLOCKQUOTE with TikTok';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::BLOCKQUOTE,
        SpecRule::SPEC_NAME => 'BLOCKQUOTE with TikTok',
        SpecRule::ATTRS => [
            Attribute::ALIGN => [],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\CiteAttr::ID,
        ],
        SpecRule::MANDATORY_ANCESTOR => 'AMP-TIKTOK blockquote',
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
