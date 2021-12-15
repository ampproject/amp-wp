<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class StylesheetTooLong.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class StylesheetTooLong extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'STYLESHEET_TOO_LONG';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The author stylesheet specified in tag \'%1\' is too long - document contains %2 bytes whereas the limit is %3 bytes.',
        SpecRule::SPECIFICITY => 35,
    ];
}
