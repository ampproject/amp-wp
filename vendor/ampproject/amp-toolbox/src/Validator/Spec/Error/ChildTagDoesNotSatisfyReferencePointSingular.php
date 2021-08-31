<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class ChildTagDoesNotSatisfyReferencePointSingular.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class ChildTagDoesNotSatisfyReferencePointSingular extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'CHILD_TAG_DOES_NOT_SATISFY_REFERENCE_POINT_SINGULAR';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\', a child tag of \'%2\', does not satisfy the reference point \'%3\'.',
        SpecRule::SPECIFICITY => 82,
    ];
}
