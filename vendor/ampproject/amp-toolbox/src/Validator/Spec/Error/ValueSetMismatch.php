<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class ValueSetMismatch.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class ValueSetMismatch extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'VALUE_SET_MISMATCH';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'Attribute \'%1\' in tag \'%2\' contains a value that does not match any other tags on the page.',
        SpecRule::SPECIFICITY => 127,
    ];
}
