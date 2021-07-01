<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStateAmp4email.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<string> $disallowedAncestor
 * @property-read array<array<string>> $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpStateAmp4email extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'amp-state (AMP4EMAIL)';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::STATE,
        SpecRule::SPEC_NAME => 'amp-state (AMP4EMAIL)',
        SpecRule::ATTR_LISTS => [
            AttributeList\MandatoryIdAttr::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-bind/',
        SpecRule::DISALLOWED_ANCESTOR => [
            'AMP-LIST',
            'AMP-STATE',
            'TEMPLATE',
        ],
        SpecRule::CHILD_TAGS => [
            SpecRule::FIRST_CHILD_TAG_NAME_ONEOF => [
                'SCRIPT',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP4ADS,
            Format::AMP4EMAIL,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::BIND,
        ],
    ];
}
