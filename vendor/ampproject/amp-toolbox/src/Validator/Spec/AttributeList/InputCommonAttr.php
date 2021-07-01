<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Attribute list class InputCommonAttr.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $accept
 * @property-read array $accesskey
 * @property-read array $autocomplete
 * @property-read array<array<string>> $autofocus
 * @property-read array $checked
 * @property-read array $disabled
 * @property-read array $height
 * @property-read array<array<string>> $inputmode
 * @property-read array<array<string>> $list
 * @property-read array<array<string>> $enterkeyhint
 * @property-read array $max
 * @property-read array $maxlength
 * @property-read array $min
 * @property-read array $minlength
 * @property-read array $multiple
 * @property-read array $pattern
 * @property-read array $placeholder
 * @property-read array $readonly
 * @property-read array $required
 * @property-read array<array<string>> $selectiondirection
 * @property-read array $size
 * @property-read array $spellcheck
 * @property-read array $step
 * @property-read array $tabindex
 * @property-read array $value
 * @property-read array $width
 * @property-read array<array<string>> $accept_binding
 * @property-read array<array<string>> $accesskey_binding
 * @property-read array $autocomplete_binding
 * @property-read array $checked_binding
 * @property-read array $disabled_binding
 * @property-read array $height_binding
 * @property-read array<array<string>> $inputmode_binding
 * @property-read array $max_binding
 * @property-read array $maxlength_binding
 * @property-read array $min_binding
 * @property-read array $minlength_binding
 * @property-read array $multiple_binding
 * @property-read array $pattern_binding
 * @property-read array $placeholder_binding
 * @property-read array $readonly_binding
 * @property-read array $required_binding
 * @property-read array<array<string>> $selectiondirection_binding
 * @property-read array $size_binding
 * @property-read array $spellcheck_binding
 * @property-read array $step_binding
 * @property-read array $value_binding
 * @property-read array $width_binding
 */
final class InputCommonAttr extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'input-common-attr';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::ACCEPT => [],
        Attribute::ACCESSKEY => [],
        Attribute::AUTOCOMPLETE => [],
        Attribute::AUTOFOCUS => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        Attribute::CHECKED => [],
        Attribute::DISABLED => [],
        Attribute::HEIGHT => [],
        Attribute::INPUTMODE => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        Attribute::LIST_ => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        Attribute::ENTERKEYHINT => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        Attribute::MAX => [],
        Attribute::MAXLENGTH => [],
        Attribute::MIN => [],
        Attribute::MINLENGTH => [],
        Attribute::MULTIPLE => [],
        Attribute::PATTERN => [],
        Attribute::PLACEHOLDER => [],
        Attribute::READONLY => [],
        Attribute::REQUIRED => [],
        Attribute::SELECTIONDIRECTION => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        Attribute::SIZE => [],
        Attribute::SPELLCHECK => [],
        Attribute::STEP => [],
        Attribute::TABINDEX => [],
        Attribute::VALUE => [],
        Attribute::WIDTH => [],
        '[ACCEPT]' => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        '[ACCESSKEY]' => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        '[AUTOCOMPLETE]' => [],
        '[CHECKED]' => [],
        '[DISABLED]' => [],
        '[HEIGHT]' => [],
        '[INPUTMODE]' => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        '[MAX]' => [],
        '[MAXLENGTH]' => [],
        '[MIN]' => [],
        '[MINLENGTH]' => [],
        '[MULTIPLE]' => [],
        '[PATTERN]' => [],
        '[PLACEHOLDER]' => [],
        '[READONLY]' => [],
        '[REQUIRED]' => [],
        '[SELECTIONDIRECTION]' => [
            SpecRule::DISABLED_BY => [
                Attribute::AMP4EMAIL,
            ],
        ],
        '[SIZE]' => [],
        '[SPELLCHECK]' => [],
        '[STEP]' => [],
        '[VALUE]' => [],
        '[WIDTH]' => [],
    ];
}
