<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class MandatoryTagAncestor.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MandatoryTagAncestor extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MANDATORY_TAG_ANCESTOR';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\' may only appear as a descendant of tag \'%2\'.',
        SpecRule::SPECIFICITY => 6,
    ];
}
