<?php

namespace Amp\Optimizer;

/**
 * Error object to transport optimization errors.
 *
 * @package amp/optimizer
 */
interface Error
{

    /**
     * Get the code of the error.
     *
     * @return string Code of the error.
     */
    public function getCode();

    /**
     * Get the message of the error.
     *
     * @return string Message of the error.
     */
    public function getMessage();
}
