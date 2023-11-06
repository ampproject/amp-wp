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
 * Attribute list class InteractiveOptionsResultsCategoryAttrs.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array<array<array<string>>> $option1ResultsCategory
 * @property-read array<array<array<string>>> $option2ResultsCategory
 * @property-read array<array<array<string>>> $option3ResultsCategory
 * @property-read array<array<array<string>>> $option4ResultsCategory
 */
final class InteractiveOptionsResultsCategoryAttrs extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'interactive-options-results-category-attrs';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::OPTION_1_RESULTS_CATEGORY => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_2_RESULTS_CATEGORY,
                ],
            ],
        ],
        Attribute::OPTION_2_RESULTS_CATEGORY => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_1_RESULTS_CATEGORY,
                ],
            ],
        ],
        Attribute::OPTION_3_RESULTS_CATEGORY => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_2_RESULTS_CATEGORY,
                    Attribute::OPTION_3_TEXT,
                ],
            ],
        ],
        Attribute::OPTION_4_RESULTS_CATEGORY => [
            SpecRule::TRIGGER => [
                SpecRule::ALSO_REQUIRES_ATTR => [
                    Attribute::OPTION_3_RESULTS_CATEGORY,
                    Attribute::OPTION_4_TEXT,
                ],
            ],
        ],
    ];
}
