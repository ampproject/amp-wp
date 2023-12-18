<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class CssExcessivelyNested.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class CssExcessivelyNested extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'CSS_EXCESSIVELY_NESTED';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'CSS excessively nested in tag \'%1\'.',
        SpecRule::SPECIFICITY => 125,
    ];
}
