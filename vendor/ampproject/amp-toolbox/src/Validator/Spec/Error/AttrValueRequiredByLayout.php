<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class AttrValueRequiredByLayout.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class AttrValueRequiredByLayout extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'ATTR_VALUE_REQUIRED_BY_LAYOUT';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'Invalid value \'%1\' for attribute \'%2\' in tag \'%3\' - for layout \'%4\', set the attribute \'%2\' to value \'%5\'.',
        SpecRule::SPECIFICITY => 28,
    ];
}
