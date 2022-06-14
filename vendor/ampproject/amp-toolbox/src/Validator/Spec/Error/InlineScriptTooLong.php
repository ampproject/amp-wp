<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class InlineScriptTooLong.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class InlineScriptTooLong extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'INLINE_SCRIPT_TOO_LONG';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The inline script is %1 bytes, which exceeds the limit of %2 bytes.',
        SpecRule::SPECIFICITY => 38,
    ];
}
