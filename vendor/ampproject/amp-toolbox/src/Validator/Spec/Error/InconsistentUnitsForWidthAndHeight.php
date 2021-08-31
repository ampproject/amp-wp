<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class InconsistentUnitsForWidthAndHeight.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class InconsistentUnitsForWidthAndHeight extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'INCONSISTENT_UNITS_FOR_WIDTH_AND_HEIGHT';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'Inconsistent units for width and height in tag \'%1\' - width is specified in \'%2\' whereas height is specified in \'%3\'.',
        SpecRule::SPECIFICITY => 45,
    ];
}
