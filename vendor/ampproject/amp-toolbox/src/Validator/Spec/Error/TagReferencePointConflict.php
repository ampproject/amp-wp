<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class TagReferencePointConflict.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class TagReferencePointConflict extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'TAG_REFERENCE_POINT_CONFLICT';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\' conflicts with reference point \'%2\' because both define reference points.',
        SpecRule::SPECIFICITY => 81,
    ];
}
