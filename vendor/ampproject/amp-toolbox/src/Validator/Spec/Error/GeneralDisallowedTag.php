<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class GeneralDisallowedTag.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class GeneralDisallowedTag extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'GENERAL_DISALLOWED_TAG';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\' is disallowed except in specific forms.',
        SpecRule::SPECIFICITY => 103,
    ];
}
