<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class CssSyntaxDisallowedQualifiedRuleMustBeInsideKeyframe.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class CssSyntaxDisallowedQualifiedRuleMustBeInsideKeyframe extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'CSS_SYNTAX_DISALLOWED_QUALIFIED_RULE_MUST_BE_INSIDE_KEYFRAME';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'CSS syntax error in tag \'%1\' - qualified rule \'%2\' must be located inside of a keyframe.',
        SpecRule::SPECIFICITY => 114,
    ];
}
