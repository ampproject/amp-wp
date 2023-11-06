<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class MissingRequiredExtension.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MissingRequiredExtension extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MISSING_REQUIRED_EXTENSION';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\' requires including the \'%2\' extension JavaScript.',
        SpecRule::SPECIFICITY => 12,
    ];
}
