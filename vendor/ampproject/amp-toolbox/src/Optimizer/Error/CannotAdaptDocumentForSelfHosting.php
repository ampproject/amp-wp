<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;
use Exception;

/**
 * Optimizer error object for when a document cannot be adapted for self-hosting the AMP runtime.
 *
 * @package ampproject/amp-toolbox
 */
final class CannotAdaptDocumentForSelfHosting implements Error
{
    use ErrorProperties;

    const FAILED_TO_ADAPT_WITH_EXCEPTION       = 'Cannot adapt document for a self-hosted runtime: ';
    const FAILED_TO_ADAPT_FOR_NON_ABSOLUTE_URL = 'Cannot add runtime host, ampUrlPrefix must be an absolute URL, got: ';

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

    /**
     * Instantiate a CannotAdaptDocumentForSelfHosting object for a non-absolute URL provided via ampUrlPrefix.
     *
     * @param string $url URL that was provided.
     * @return self
     */
    public static function forNonAbsoluteUrl($url)
    {
        return new self(self::FAILED_TO_ADAPT_FOR_NON_ABSOLUTE_URL . $url);
    }
}
