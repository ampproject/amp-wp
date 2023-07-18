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
 * Tag class LinkRelStylesheetForAmpStory10Css.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $enabledBy
 * @property-read string $descriptiveName
 */
final class LinkRelStylesheetForAmpStory10Css extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'link rel=stylesheet for amp-story-1.0 css';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::LINK,
        SpecRule::SPEC_NAME => 'link rel=stylesheet for amp-story-1.0 css',
        SpecRule::MANDATORY_PARENT => Element::HEAD,
        SpecRule::ATTRS => [
            Attribute::CROSSORIGIN => [],
            Attribute::HREF => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'https://cdn.ampproject.org/v0/amp-story-1.0.css',
                    'https://cdn.ampproject.org/lts/v0/amp-story-1.0.css',
                ],
            ],
            Attribute::INTEGRITY => [],
            Attribute::MEDIA => [],
            Attribute::REL => [
                SpecRule::MANDATORY => true,
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_DISPATCH',
                SpecRule::VALUE_CASEI => [
                    'stylesheet',
                ],
            ],
            Attribute::TYPE => [
                SpecRule::VALUE_CASEI => [
                    'text/css',
                ],
            ],
            Attribute::AMP_EXTENSION => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_CASEI => [
                    'amp-story',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::ENABLED_BY => [
            Attribute::TRANSFORMED,
        ],
        SpecRule::DESCRIPTIVE_NAME => 'link rel=stylesheet for amp-story-1.0 css',
    ];
}
