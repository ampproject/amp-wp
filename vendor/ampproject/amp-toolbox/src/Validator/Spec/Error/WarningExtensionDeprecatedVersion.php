<?php

/**
 * DO NOT EDIT!
 * This file was automatically generated via bin/generate-validator-spec.php.
 */

namespace AmpProject\Validator\Spec\Error;

use AmpProject\Validator\Spec\Error;
use AmpProject\Validator\Spec\SpecRule;

/**
 * Error class WarningExtensionDeprecatedVersion.
 *
 * @package ampproject/amp-toolbox.
 *
 * @property-read string $format
 * @property-read int $specificity
 */
final class WarningExtensionDeprecatedVersion extends Error
{
    /**
     * Code of the error.
     *
     * @var string
     */
    const CODE = 'WARNING_EXTENSION_DEPRECATED_VERSION';

    /**
     * Array of spec data.
     *
     * @var array{format: string, specificity?: int}
     */
    const SPEC = [
        SpecRule::FORMAT => 'The extension \'%1\' is referenced at version \'%2\' which is a deprecated version. Please use a more recent version of this extension. This may become an error in the future.',
        SpecRule::SPECIFICITY => 17,
    ];
}
