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
 * Attribute list class AmpStreamGalleryCommon.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<string> $controls
 * @property-read array<array<string>> $extraSpace
 * @property-read array<string> $loop
 * @property-read array<string> $minVisibleCount
 * @property-read array<string> $maxVisibleCount
 * @property-read array<string> $minItemWidth
 * @property-read array<string> $maxItemWidth
 * @property-read array<string> $outsetArrows
 * @property-read array<string> $peek
 * @property-read array<string> $slideAlign
 * @property-read array<string> $snap
 * @property-read array $controls_binding
 * @property-read array $extraSpace_binding
 * @property-read array $loop_binding
 * @property-read array $minVisibleCount_binding
 * @property-read array $maxVisibleCount_binding
 * @property-read array $minItemWidth_binding
 * @property-read array $maxItemWidth_binding
 * @property-read array $outsetArrows_binding
 * @property-read array $peek_binding
 * @property-read array $slideAlign_binding
 * @property-read array $snap_binding
 */
final class AmpStreamGalleryCommon extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-stream-gallery-common';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::CONTROLS => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(always|auto|never),\s*)*(always|auto|never)',
        ],
        Attribute::EXTRA_SPACE => [
            SpecRule::VALUE => [
                'between',
            ],
        ],
        Attribute::LOOP => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false|^$)',
        ],
        Attribute::MIN_VISIBLE_COUNT => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+(\.\d+)?),\s*)*(\d+(\.\d+)?)',
        ],
        Attribute::MAX_VISIBLE_COUNT => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+(\.\d+)?),\s*)*(\d+(\.\d+)?)',
        ],
        Attribute::MIN_ITEM_WIDTH => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+),\s*)*(\d+)',
        ],
        Attribute::MAX_ITEM_WIDTH => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+),\s*)*(\d+)',
        ],
        Attribute::OUTSET_ARROWS => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false)',
        ],
        Attribute::PEEK => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(\d+(\.\d+)?),\s*)*(\d+(\.\d+)?)',
        ],
        Attribute::SLIDE_ALIGN => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(start|center),\s*)*(start|center)',
        ],
        Attribute::SNAP => [
            SpecRule::VALUE_REGEX => '([^,]+\s+(true|false),\s*)*(true|false)',
        ],
        '[CONTROLS]' => [],
        '[EXTRA_SPACE]' => [],
        '[LOOP]' => [],
        '[MIN_VISIBLE_COUNT]' => [],
        '[MAX_VISIBLE_COUNT]' => [],
        '[MIN_ITEM_WIDTH]' => [],
        '[MAX_ITEM_WIDTH]' => [],
        '[OUTSET_ARROWS]' => [],
        '[PEEK]' => [],
        '[SLIDE_ALIGN]' => [],
        '[SNAP]' => [],
    ];
}
