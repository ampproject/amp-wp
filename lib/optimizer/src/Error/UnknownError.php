<?php

namespace Amp\Optimizer\Error;

use Amp\Optimizer\Error;

final class UnknownError implements Error
{
    use ErrorProperties;

    /**
     * Code to use for the error.
     */
    const CODE = 'UNKNOWN_ERROR';

    /**
     * Instantiate a UnknownError object.
     *
     * @param string $message Message for the error.
     */
    public function __construct($message)
    {
        $this->code    = self::CODE;
        $this->message = $message;
    }
}
