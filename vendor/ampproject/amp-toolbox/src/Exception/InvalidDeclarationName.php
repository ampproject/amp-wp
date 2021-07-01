<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid declaration name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidDeclarationName extends OutOfRangeException implements AmpException
{

    /**
     * Instantiate an InvalidDeclarationName exception for an declaration that is not found within name index.
     *
     * @param string $declaration Name of the declaration that was requested.
     * @return self
     */
    public static function forDeclaration($declaration)
    {
        $message = "Invalid declaration '{$declaration}' was requested from the validator spec.";

        return new self($message);
    }
}
