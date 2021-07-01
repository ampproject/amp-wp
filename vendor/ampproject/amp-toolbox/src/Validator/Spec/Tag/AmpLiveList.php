<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Extension;
use AmpProject\Format;
use AmpProject\Layout;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpLiveList.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<array> $referencePoints
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpLiveList extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-LIVE-LIST';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::LIVE_LIST,
        SpecRule::ATTRS => [
            Attribute::DATA_MAX_ITEMS_PER_PAGE => [
                SpecRule::MANDATORY => true,
                SpecRule::VALUE_REGEX => '\d+',
            ],
            Attribute::DATA_POLL_INTERVAL => [
                SpecRule::VALUE_REGEX => '\d{5,}',
            ],
            Attribute::DISABLED => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::SORT => [
                SpecRule::VALUE => [
                    'ascending',
                ],
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\MandatoryIdAttr::ID,
        ],
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::CONTAINER,
                Layout::FIXED_HEIGHT,
            ],
        ],
        SpecRule::REFERENCE_POINTS => [
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-LIVE-LIST [update]',
                SpecRule::MANDATORY => true,
                SpecRule::UNIQUE => true,
            ],
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-LIVE-LIST [items]',
                SpecRule::MANDATORY => true,
                SpecRule::UNIQUE => true,
            ],
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-LIVE-LIST [pagination]',
                SpecRule::UNIQUE => true,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::LIVE_LIST,
        ],
    ];
}
