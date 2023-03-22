<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class NonLtsScriptAfterLts.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class NonLtsScriptAfterLts extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'NON_LTS_SCRIPT_AFTER_LTS';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => '\'%1\' must use the LTS version to correspond with the first script in the page, which uses LTS.',
        SpecRule::SPECIFICITY => 20,
    ];
}
