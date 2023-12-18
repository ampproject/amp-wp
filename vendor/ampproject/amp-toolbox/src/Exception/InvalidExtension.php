<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid extension is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidExtension extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidExtension exception for an extension that is not found within the extension spec index.
     *
     * @param string $extension Spec name that was requested.
     * @return self
     */
    public static function forExtension($extension)
    {
        $message = "Invalid extension '{$extension}' was requested from the validator spec.";

        return new self($message);
    }
}
