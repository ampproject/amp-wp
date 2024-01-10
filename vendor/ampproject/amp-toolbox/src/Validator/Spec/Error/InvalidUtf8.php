<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class InvalidUtf8.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class InvalidUtf8 extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'INVALID_UTF8';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The document contains invalid UTF8.',
        SpecRule::SPECIFICITY => 124,
    ];
}
