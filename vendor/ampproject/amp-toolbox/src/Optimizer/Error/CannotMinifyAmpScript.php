<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;

/**
 * Optimizer error object when unable to minify an amp-script element.
 *
 * @package ampproject/amp-toolbox
 */
final class CannotMinifyAmpScript implements Error
{
    use ErrorProperties;

    /**
     * Instantiate a CannotMinifyAmpScript object with an error message.
     *
     * @param string $data     The script to be minified.
     * @param string $errorMsg The error message.
     * @return self
     */
    public static function withMessage($data, $errorMsg)
    {
        return new self(
            sprintf(
                "Could not minify inline amp-script.\n%s\n%s",
                $errorMsg,
                $data
            )
        );
    }
}
