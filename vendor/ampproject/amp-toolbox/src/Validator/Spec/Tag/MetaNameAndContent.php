<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Format;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag as Element;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class MetaNameAndContent.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read array<string> $htmlFormat
 */
final class MetaNameAndContent extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'meta name= and content=';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::META,
        SpecRule::SPEC_NAME => 'meta name= and content=',
        SpecRule::ATTRS => [
            Attribute::CONTENT => [],
            Attribute::ITEMPROP => [],
            Attribute::NAME => [
                SpecRule::DISALLOWED_VALUE_REGEX => '(^|\s)(amp-.*|amp4ads-.*|apple-itunes-app|content-disposition|revisit-after|viewport)(\s|$)',
            ],
            Attribute::MEDIA => [],
            Attribute::PROPERTY => [],
            Attribute::SCHEME => [],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
            Format::AMP4EMAIL,
        ],
    ];
}
