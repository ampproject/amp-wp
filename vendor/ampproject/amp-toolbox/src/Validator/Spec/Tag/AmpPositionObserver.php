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
 * Tag class AmpPositionObserver.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read array<array<string>> $ampLayout
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 */
final class AmpPositionObserver extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-POSITION-OBSERVER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::POSITION_OBSERVER,
        SpecRule::ATTRS => [
            Attribute::INTERSECTION_RATIOS => [
                SpecRule::VALUE_REGEX => '^([0]*?\.\d*$|1$|0$)|([0]*?\.\d*|1|0)\s{1}([0]*?\.\d*$|1$|0$)',
            ],
            Attribute::ONCE => [
                SpecRule::VALUE => [
                    '',
                ],
            ],
            Attribute::TARGET => [],
            Attribute::VIEWPORT_MARGINS => [
                SpecRule::VALUE_REGEX => '^(\d+$|\d+px$|\d+vh$)|((\d+|\d+px|\d+vh)\s{1}(\d+$|\d+px$|\d+vh$))',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::NODISPLAY,
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
            Format::AMP4ADS,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::POSITION_OBSERVER,
        ],
    ];
}
