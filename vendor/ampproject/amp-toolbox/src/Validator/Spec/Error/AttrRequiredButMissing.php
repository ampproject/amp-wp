<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class AttrRequiredButMissing.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class AttrRequiredButMissing extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'ATTR_REQUIRED_BUT_MISSING';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The attribute \'%1\' in tag \'%2\' is missing or incorrect, but required by attribute \'%3\'.',
        SpecRule::SPECIFICITY => 30,
    ];
}
