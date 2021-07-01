<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Tag;

use AmpProject\Attribute;
use AmpProject\Format;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;
use AmpProject\Validator\Spec\Tag;

/**
 * Tag class AmpStoryCtaLayerAnimateIn.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $tagName
 * @property-read string $specName
 * @property-read array $attrs
 * @property-read string $specUrl
 * @property-read array<array<string>> $referencePoints
 * @property-read array<string> $htmlFormat
 */
final class AmpStoryCtaLayerAnimateIn extends Tag implements Identifiable
{
    /**
     * ID of the tag.
     *
     * @var string
     */
    const ID = 'AMP-STORY-CTA-LAYER animate-in';

    /**
     * Array of spec rules.
     *
     * @var array
     */
    const SPEC = [
        SpecRule::TAG_NAME => '$REFERENCE_POINT',
        SpecRule::SPEC_NAME => 'AMP-STORY-CTA-LAYER animate-in',
        SpecRule::ATTRS => [
            Attribute::ANIMATE_IN => [
                SpecRule::VALUE => [
                    'drop',
                    'fade-in',
                    'fly-in-bottom',
                    'fly-in-left',
                    'fly-in-right',
                    'fly-in-top',
                    'pan-down',
                    'pan-left',
                    'pan-right',
                    'pan-up',
                    'pulse',
                    'rotate-in-left',
                    'rotate-in-right',
                    'scale-fade-down',
                    'scale-fade-up',
                    'twirl-in',
                    'whoosh-in-left',
                    'whoosh-in-right',
                    'zoom-in',
                    'zoom-out',
                ],
            ],
            Attribute::ANIMATE_IN_AFTER => [],
            Attribute::ANIMATE_IN_DELAY => [],
            Attribute::ANIMATE_IN_DURATION => [],
            Attribute::ANIMATE_IN_TIMING_FUNCTION => [],
            Attribute::SCALE_END => [
                SpecRule::VALUE_REGEX => '[0-9]+([.][0-9]+)?',
            ],
            Attribute::SCALE_START => [
                SpecRule::VALUE_REGEX => '[0-9]+([.][0-9]+)?',
            ],
            Attribute::TRANSLATE_X => [
                SpecRule::VALUE_REGEX_CASEI => '[0-9]+px',
            ],
            Attribute::TRANSLATE_Y => [
                SpecRule::VALUE_REGEX_CASEI => '[0-9]+px',
            ],
        ],
        SpecRule::SPEC_URL => 'https://amp.dev/documentation/components/amp-story/',
        SpecRule::REFERENCE_POINTS => [
            [
                SpecRule::TAG_SPEC_NAME => 'AMP-STORY-CTA-LAYER animate-in',
            ],
        ],
        SpecRule::HTML_FORMAT => [
            Format::AMP,
        ],
    ];
}
