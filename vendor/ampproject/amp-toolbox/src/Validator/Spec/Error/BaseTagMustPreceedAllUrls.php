<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class BaseTagMustPreceedAllUrls.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class BaseTagMustPreceedAllUrls extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'BASE_TAG_MUST_PRECEED_ALL_URLS';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The tag \'%1\', which contains URLs, was found earlier in the document than the BASE element.',
        SpecRule::SPECIFICITY => 90,
    ];
}
