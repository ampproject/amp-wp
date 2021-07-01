<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\AttributeList;

use AmpProject\Attribute;
use AmpProject\Validator\Spec\AttributeList;
use AmpProject\Validator\Spec\Identifiable;

/**
 * Attribute list class SvgTransferFunctionAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read array $amplitude
 * @property-read array $exponent
 * @property-read array $intercept
 * @property-read array $offset
 * @property-read array $slope
 * @property-read array $table
 * @property-read array $tablevalues
 * @property-read array $type
 */
final class SvgTransferFunctionAttributes extends AttributeList implements Identifiable
{
    /**
     * ID of the attribute list.
     *
     * @var string
     */
    const ID = 'svg-transfer-function-attributes';

    /**
     * Array of attributes.
     *
     * @var array<array>
     */
    const ATTRIBUTES = [
        Attribute::AMPLITUDE => [],
        Attribute::EXPONENT => [],
        Attribute::INTERCEPT => [],
        Attribute::OFFSET => [],
        Attribute::SLOPE => [],
        Attribute::TABLE => [],
        Attribute::TABLEVALUES => [],
        Attribute::TYPE => [],
    ];
}
