<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class MandatoryLastChildTag.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MandatoryLastChildTag extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MANDATORY_LAST_CHILD_TAG';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'Tag \'%1\', if present, must be the last child of tag \'%2\'.',
        SpecRule::SPECIFICITY => 110,
    ];
}
