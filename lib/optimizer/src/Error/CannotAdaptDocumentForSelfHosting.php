<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;
use Exception;

final class CannotAdaptDocumentForSelfHosting implements Error
{
    use ErrorProperties;

    const FAILED_TO_ADAPT_WITH_EXCEPTION  = 'Cannot adapt document for a self-hosted runtime: ';

    /**
     * Instantiate a CannotAdaptDocumentForSelfHosting object for an exception that blocked adapting the document.
     *
     * @param Exception $exception Exception that was caught and that blocked adapting the document.
     * @return self
     */
    public static function fromException(Exception $exception)
    {
        return new self(self::FAILED_TO_ADAPT_WITH_EXCEPTION . $exception->getMessage());
    }
}
