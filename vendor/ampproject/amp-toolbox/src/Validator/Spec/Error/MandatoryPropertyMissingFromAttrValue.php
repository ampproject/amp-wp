<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class MandatoryPropertyMissingFromAttrValue.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class MandatoryPropertyMissingFromAttrValue extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'MANDATORY_PROPERTY_MISSING_FROM_ATTR_VALUE';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The property \'%1\' is missing from attribute \'%2\' in tag \'%3\'.',
        SpecRule::SPECIFICITY => 40,
    ];
}
