<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class IncorrectNumChildTags.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class IncorrectNumChildTags extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'INCORRECT_NUM_CHILD_TAGS';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'Tag \'%1\' must have %2 child tags - saw %3 child tags.',
        SpecRule::SPECIFICITY => 74,
    ];
}
