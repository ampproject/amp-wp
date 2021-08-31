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
 * Tag class AmpImageSlider.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read array $attrs
 * @property-read array<string> $attrLists
 * @property-read string $specUrl
 * @property-read array<array<string>> $ampLayout
 * @property-read array $childTags
 * @property-read array<string> $htmlFormat
 * @property-read array<string> $requiresExtension
 * @property-read array<string> $disabledBy
 */
final class AmpImageSlider extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-IMAGE-SLIDER';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => Extension::IMAGE_SLIDER,
        SpecRule::ATTRS => [
            Attribute::DISABLE_HINT_REAPPEAR => [],
            Attribute::INITIAL_SLIDER_POSITION => [
                SpecRule::VALUE_REGEX => '0(\.[0-9]+)?|1(\.0+)?',
            ],
            Attribute::STEP_SIZE => [
                SpecRule::VALUE_REGEX => '0(\.[0-9]+)?|1(\.0+)?',
            ],
        ],
        SpecRule::ATTR_LISTS => [
            AttributeList\ExtendedAmpGlobal::ID,
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-image-slider/',
        SpecRule::AMP_LAYOUT => [
            SpecRule::SUPPORTED_LAYOUTS => [
                Layout::FIXED,
                Layout::INTRINSIC,
                Layout::NODISPLAY,
                Layout::RESPONSIVE,
            ],
        ],
        SpecRule::CHILD_TAGS => [
            SpecRule::CHILD_TAG_NAME_ONEOF => [
                'AMP-IMG',
                'DIV',
            ],
            SpecRule::MANDATORY_MIN_NUM_CHILD_TAGS => 2,
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
        SpecRule::REQUIRES_EXTENSION => [
            Extension::IMAGE_SLIDER,
        ],
        SpecRule::DISABLED_BY => [
            Attribute::TRANSFORMED,
        ],
    ];
}
