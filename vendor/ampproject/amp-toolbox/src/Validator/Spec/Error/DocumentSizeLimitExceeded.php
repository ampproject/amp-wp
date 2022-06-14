<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class DocumentSizeLimitExceeded.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class DocumentSizeLimitExceeded extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'DOCUMENT_SIZE_LIMIT_EXCEEDED';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'Document exceeded %1 bytes limit. Actual size %2 bytes.',
        SpecRule::SPECIFICITY => 126,
    ];
}
