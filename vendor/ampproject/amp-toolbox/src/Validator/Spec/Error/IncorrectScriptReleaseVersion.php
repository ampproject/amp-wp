<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class IncorrectScriptReleaseVersion.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class IncorrectScriptReleaseVersion extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'INCORRECT_SCRIPT_RELEASE_VERSION';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The script version for \'%1\' is a %2 version which mismatches with the first script on the page using the %3 version.',
        SpecRule::SPECIFICITY => 20,
    ];
}
