<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Protocol;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class AmpDatePickerCommonAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<string>> $allowBlockedEndDate
 * @property-read array<array<string>> $allowBlockedRanges
 * @property-read array $blocked
 * @property-read array<string> $daySize
 * @property-read array<string> $firstDayOfWeek
 * @property-read array $format
 * @property-read array $highlighted
 * @property-read array $locale
 * @property-read array $max
 * @property-read array $min
 * @property-read array $monthFormat
 * @property-read array<string> $numberOfMonths
 * @property-read array<array<string>> $openAfterClear
 * @property-read array<array<string>> $openAfterSelect
 * @property-read array<array<string>> $hideKeyboardShortcutsPanel
 * @property-read array $src
 * @property-read array $weekDayFormat
 * @property-read array $src_binding
 * @property-read array $max_binding
 * @property-read array $min_binding
 */
final class AmpDatePickerCommonAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'amp-date-picker-common-attributes';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ALLOW_BLOCKED_END_DATE => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::ALLOW_BLOCKED_RANGES => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::BLOCKED => [],
        Attribute::DAY_SIZE => [
            SpecRule::VALUE_REGEX => '[0-9]+',
        ],
        Attribute::FIRST_DAY_OF_WEEK => [
            SpecRule::VALUE_REGEX => '[0-6]',
        ],
        Attribute::FORMAT => [],
        Attribute::HIGHLIGHTED => [],
        Attribute::LOCALE => [],
        Attribute::MAX => [],
        Attribute::MIN => [],
        Attribute::MONTH_FORMAT => [],
        Attribute::NUMBER_OF_MONTHS => [
            SpecRule::VALUE_REGEX => '[0-9]+',
        ],
        Attribute::OPEN_AFTER_CLEAR => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::OPEN_AFTER_SELECT => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::HIDE_KEYBOARD_SHORTCUTS_PANEL => [
            SpecRule::VALUE => [
                '',
            ],
        ],
        Attribute::SRC => [
            SpecRule::DISALLOWED_VALUE_REGEX => '__amp_source_origin',
            SpecRule::VALUE_URL => [
                SpecRule::PROTOCOL => [
                    Protocol::HTTPS,
                ],
                SpecRule::ALLOW_RELATIVE => false,
            ],
        ],
        Attribute::WEEK_DAY_FORMAT => [],
        '[SRC]' => [],
        '[MAX]' => [],
        '[MIN]' => [],
    ];
}
