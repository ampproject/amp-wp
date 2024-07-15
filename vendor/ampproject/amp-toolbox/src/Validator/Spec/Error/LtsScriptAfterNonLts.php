<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class LtsScriptAfterNonLts.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class LtsScriptAfterNonLts extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'LTS_SCRIPT_AFTER_NON_LTS';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => '\'%1\' must use the non-LTS version to correspond with the first script in the page, which does not use LTS.',
        SpecRule::SPECIFICITY => 21,
    ];
}
