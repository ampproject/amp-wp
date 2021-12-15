<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class UnescapedTemplateInAttrValue.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class UnescapedTemplateInAttrValue extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'UNESCAPED_TEMPLATE_IN_ATTR_VALUE';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The attribute \'%1\' in tag \'%2\' is set to \'%3\', which contains unescaped Mustache template syntax.',
        SpecRule::SPECIFICITY => 44,
    ];
}
