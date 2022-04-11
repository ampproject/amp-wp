<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class MissingUrl.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MissingUrl extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MISSING_URL';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'Missing URL for attribute \'%1\' in tag \'%2\'.',
        SpecRule::SPECIFICITY => 55,
    ];
}
