<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class MissingLayoutAttributes.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MissingLayoutAttributes extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MISSING_LAYOUT_ATTRIBUTES';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'Incomplete layout attributes specified for tag \'%1\'. For example, provide attributes \'width\' and \'height\'.',
        SpecRule::SPECIFICITY => 48,
    ];
}
