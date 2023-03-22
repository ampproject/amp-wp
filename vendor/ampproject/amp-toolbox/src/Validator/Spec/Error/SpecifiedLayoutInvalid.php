<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class SpecifiedLayoutInvalid.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class SpecifiedLayoutInvalid extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'SPECIFIED_LAYOUT_INVALID';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The specified layout \'%1\' is not supported by tag \'%2\'.',
        SpecRule::SPECIFICITY => 50,
    ];
}
