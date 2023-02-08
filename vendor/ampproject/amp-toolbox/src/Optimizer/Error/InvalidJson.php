<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;

/**
 * Optimizer error object for invalid JSON data.
 *
 * @package ampproject/amp-toolbox
 */
final class InvalidJson implements Error
{
    use ErrorProperties;

    /**
     * Instantiate an InvalidJson object after decoding JSON data.
     *
     * @return self
     */
    public static function fromLastErrorMsgAfterDecoding()
    {
        $errorMsg = 'Error decoding JSON: ' . json_last_error_msg();
        return new self($errorMsg);
    }

    /**
     * Instantiate an InvalidJson object after encoding JSON data.
     *
     * @return self
     */
    public static function fromLastErrorMsgAfterEncoding()
    {
        $errorMsg = 'Error encoding JSON: ' . json_last_error_msg();
        return new self($errorMsg);
    }
}
