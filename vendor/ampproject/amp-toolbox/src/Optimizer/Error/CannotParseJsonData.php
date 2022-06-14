<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Dom\Element;
use AmpProject\Dom\ElementDump;
use AmpProject\Optimizer\Error;
use Exception;

/**
 * Optimizer error object for invalid JSON data.
 *
 * @package ampproject/amp-toolbox
 */
final class CannotParseJsonData implements Error
{
    use ErrorProperties;

    const SCRIPT_EXCEPTION_MESSAGE = 'Cannot parse JSON data for script element %2$s: %1$s.';

    /**
     * Instantiate a CannotParseJsonData object for an exception that was thrown.
     *
     * @param Exception $exception Exception that was thrown.
     * @param Element   $script    DOM element of the <style amp-runtime> tag that was targeted.
     * @return self
     */
    public static function fromExceptionForScriptElement(Exception $exception, Element $script)
    {
        return new self(sprintf(self::SCRIPT_EXCEPTION_MESSAGE, $exception, new ElementDump($script)));
    }
}
