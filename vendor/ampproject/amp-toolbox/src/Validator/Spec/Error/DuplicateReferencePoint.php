<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class DuplicateReferencePoint.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DuplicateReferencePoint extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DUPLICATE_REFERENCE_POINT';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The reference point \'%1\' for \'%2\' must be unique but a duplicate was encountered.',
        SpecRule::SPECIFICITY => 82,
    ];
}
