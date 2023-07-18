<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class MandatoryTagAncestorWithHint.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MandatoryTagAncestorWithHint extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MANDATORY_TAG_ANCESTOR_WITH_HINT';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\' may only appear as a descendant of tag \'%2\'. Did you mean \'%3\'?',
        SpecRule::SPECIFICITY => 7,
    ];
}
