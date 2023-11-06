<?php

namespace AmpProject\Optimizer;

/**
 * Error object to transport optimization errors.
 *
 * @package ampproject/amp-toolbox
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
