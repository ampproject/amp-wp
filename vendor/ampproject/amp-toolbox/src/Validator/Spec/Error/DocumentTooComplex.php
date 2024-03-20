<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class DocumentTooComplex.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DocumentTooComplex extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DOCUMENT_TOO_COMPLEX';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The document is too complex.',
        SpecRule::SPECIFICITY => 107,
    ];
}
