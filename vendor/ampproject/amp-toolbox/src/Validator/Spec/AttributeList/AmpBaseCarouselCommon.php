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
 * Attribute list class AmpBaseCarouselCommon.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $advanceCount
 * @property-read array<string> $autoAdvance
 * @property-read array<string> $autoAdvanceCount
 * @property-read array<string> $autoAdvanceInterval
 * @property-read array<string> $autoAdvanceLoops
 * @property-read array<string> $controls
 * @property-read array<string> $horizontal
 * @property-read array<string> $loop
 * @property-read array<string> $mixedLength
 * @property-read array<string> $orientation
 * @property-read array<string> $slide
 * @property-read array<string> $snap
 * @property-read array<string> $snapAlign
 * @property-read array<string> $snapBy
 * @property-read array<string> $visibleCount
 * @property-read array $advanceCount_binding
 * @property-read array $autoAdvance_binding
 * @property-read array $autoAdvanceCount_binding
 * @property-read array $autoAdvanceInterval_binding
 * @property-read array $autoAdvanceLoops_binding
 * @property-read array $horizontal_binding
 * @property-read array $loop_binding
 * @property-read array $mixedLength_binding
 * @property-read array $orientation_binding
 * @property-read array $slide_binding
 * @property-read array $snap_binding
 * @property-read array $snapAlign_binding
 * @property-read array $snapBy_binding
 * @property-read array $visibleCount_binding
 */
final class AmpBaseCarouselCommon extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-base-carousel-common';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ADVANCE_COUNT => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(-?\d+),\s*)*(-?\d+)',
        ],
        Attribute::AUTO_ADVANCE => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false)',
        ],
        Attribute::AUTO_ADVANCE_COUNT => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(-?\d+),\s*)*(-?\d+)',
        ],
        Attribute::AUTO_ADVANCE_INTERVAL => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+),\s*)*(\d+)',
        ],
        Attribute::AUTO_ADVANCE_LOOPS => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+),\s*)*(\d+)',
        ],
        Attribute::CONTROLS => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(always|auto|never),\s*)*(always|auto|never)',
        ],
        Attribute::HORIZONTAL => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false)',
        ],
        Attribute::LOOP => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false|^$)',
        ],
        Attribute::MIXED_LENGTH => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false)',
        ],
        Attribute::ORIENTATION => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(horizontal|vertical),\s*)*(horizontal|vertical)',
        ],
        Attribute::SLIDE => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+),\s*)*(\d+)',
        ],
        Attribute::SNAP => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false)',
        ],
        Attribute::SNAP_ALIGN => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(start|center),\s*)*(start|center)',
        ],
        Attribute::SNAP_BY => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+),\s*)*(\d+)',
        ],
        Attribute::VISIBLE_COUNT => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+(\.\d+)?),\s*)*(\d+(\.\d+)?)',
        ],
        '[ADVANCE_COUNT]' => [],
        '[AUTO_ADVANCE]' => [],
        '[AUTO_ADVANCE_COUNT]' => [],
        '[AUTO_ADVANCE_INTERVAL]' => [],
        '[AUTO_ADVANCE_LOOPS]' => [],
        '[HORIZONTAL]' => [],
        '[LOOP]' => [],
        '[MIXED_LENGTH]' => [],
        '[ORIENTATION]' => [],
        '[SLIDE]' => [],
        '[SNAP]' => [],
        '[SNAP_ALIGN]' => [],
        '[SNAP_BY]' => [],
        '[VISIBLE_COUNT]' => [],
    ];
}
