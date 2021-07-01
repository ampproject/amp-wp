<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class InvalidAttrValue.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class InvalidAttrValue extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'INVALID_ATTR_VALUE';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The attribute \'%1\' in tag \'%2\' is set to the invalid value \'%3\'.',
        SpecRule::SPECIFICITY => 24,
    ];
}
