<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class AttrDisallowedByImpliedLayout.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class AttrDisallowedByImpliedLayout extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'ATTR_DISALLOWED_BY_IMPLIED_LAYOUT';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The attribute \'%1\' in tag \'%2\' is disallowed by implied layout \'%3\'.',
        SpecRule::SPECIFICITY => 51,
    ];
}
