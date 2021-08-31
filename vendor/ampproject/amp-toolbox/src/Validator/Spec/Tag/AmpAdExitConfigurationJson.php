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
 * Tag class AmpAdExitConfigurationJson.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read string $mandatoryParent
 * @property-read array<array> $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $satisfies
 * @property-read array<string> $requiresExtension
 */
final class AmpAdExitConfigurationJson extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-ad-exit configuration JSON';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Element::SCRIPT,
        SpecRule::SPEC_NAME => 'amp-ad-exit configuration JSON',
        SpecRule::MANDATORY_PARENT => Extension::AD_EXIT,
        SpecRule::ATTRS => [
            Attribute::TYPE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE => [
                    'application/json',
                ],
                SpecRule::DISPATCH_KEY => 'NAME_VALUE_PARENT_DISPATCH',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\NonceAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-ad-exit/',
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
        ],
        SpecRule::SATISFIES => [
            'amp-ad-exit configuration JSON',
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::AD_EXIT,
        ],
    ];
}
