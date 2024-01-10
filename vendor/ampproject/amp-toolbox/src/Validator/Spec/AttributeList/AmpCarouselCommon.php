<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Html\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class AmpCarouselCommon.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $arrows
 * @property-read array<string> $autoplay
 * @property-read array $controls
 * @property-read array<string> $delay
 * @property-read array<array<string>> $dots
 * @property-read array<array<string>> $loop
 * @property-read array<string> $slide
 * @property-read array<array<string>> $type
 * @property-read array $slide_binding
 */
final class AmpCarouselCommon extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-carousel-common';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ARROWS => [
            SpecRule::VALUE => [
                '',
            ],
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        Attribute::AUTOPLAY => [
            SpecRule::VALUE_REGEX => '(|[0-9]+)',
        ],
        Attribute::CONTROLS => [],
        Attribute::DELAY => [
            SpecRule::VALUE_REGEX => '[0-9]+',
        ],
        Attribute::DOTS => [
            SpecRule::VALUE => [
                '',
            ],
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        Attribute::LOOP => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::SLIDE => [
            SpecRule::VALUE_REGEX => '[0-9]+',
        ],
        Attribute::TYPE => [
            SpecRule::VALUE => [
                'carousel',
                'slides',
            ],
        ],
        '[SLIDE]' => [],
    ];
}
