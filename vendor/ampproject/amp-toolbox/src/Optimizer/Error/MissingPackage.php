<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;

/**
 * Optimizer error object for missing optional PHP package.
 *
 * @package ampproject/amp-toolbox
 */
final class MissingPackage implements Error
{
    use ErrorProperties;

    /**
     * Instantiate a MissingPackage object for a missing PHP package.
     *
     * @param string $errorMsg The error message.
     * @return self
     */
    public static function withMessage($errorMsg)
    {
        return new self($errorMsg);
    }
}
