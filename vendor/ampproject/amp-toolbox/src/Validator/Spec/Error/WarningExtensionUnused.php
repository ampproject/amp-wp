<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class WarningExtensionUnused.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class WarningExtensionUnused extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'WARNING_EXTENSION_UNUSED';

    /**
     * Array of spec data.
     *
     * @var array<array>
     */
    const SPEC = [
        SpecRule::FORMAT => 'The extension \'%1\' was found on this page, but is unused (no \'%2\' tag seen). This may become an error in the future.',
        SpecRule::SPECIFICITY => 16,
    ];
}
