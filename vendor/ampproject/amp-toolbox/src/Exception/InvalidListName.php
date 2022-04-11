<?php

namespace AmpProject\Exception;

use OutOfRangeException;

/**
 * Exception thrown when an invalid list name is requested from the validator spec.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidListName extends OutOfRangeException implements AmpException
{
    /**
     * Instantiate an InvalidListName exception for a attribute that is not found within the attribute
     * list name index.
     *
     * @param string $attributeList Name of the attribute list that was requested.
     * @return self
     */
    public static function forAttributeList($attributeList)
    {
        $message = "Invalid attribute list '{$attributeList}' was requested from the validator spec.";

        return new self($message);
    }

    /**
     * Instantiate an InvalidListName exception for a declaration that is not found within the declaration
     * list name index.
     *
     * @param string $declarationList Name of the declaration list that was requested.
     * @return self
     */
    public static function forDeclarationList($declarationList)
    {
        $message = "Invalid declaration list '{$declarationList}' was requested from the validator spec.";

        return new self($message);
    }

    /**
     * Instantiate an InvalidListName exception for a descendant tag that is not found within the descendant tag
     * list name index.
     *
     * @param string $descendantTagList Name of the descendant tag list that was requested.
     * @return self
     */
    public static function forDescendantTagList($descendantTagList)
    {
        $message = "Invalid descendant tag list '{$descendantTagList}' was requested from the validator spec.";

        return new self($message);
    }
}
