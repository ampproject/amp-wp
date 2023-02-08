<?php

namespace AmpProject\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when a HTML contains invalid byte sequences.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidByteSequence extends InvalidArgumentException implements AmpException
{
    /**
     * Instantiate a InvalidByteSequence exception for a HTML with invalid byte sequences.
     *
     * @return self
     */
    public static function forHtml()
    {
        $message = 'Provided HTML contains invalid byte sequences. '
            . 'This is usually fixed by replacing string manipulation functions '
            . 'with their `mb_*` multibyte counterparts.';

        return new self($message);
    }
}
