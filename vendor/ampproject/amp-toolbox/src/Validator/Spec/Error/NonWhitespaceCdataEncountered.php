<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class NonWhitespaceCdataEncountered.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class NonWhitespaceCdataEncountered extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'NON_WHITESPACE_CDATA_ENCOUNTERED';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\' contains text, which is disallowed.',
        SpecRule::SPECIFICITY => 3,
    ];
}
