<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class DisallowedTagAncestor.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DisallowedTagAncestor extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DISALLOWED_TAG_ANCESTOR';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\' may not appear as a descendant of tag \'%2\'.',
        SpecRule::SPECIFICITY => 5,
    ];
}
