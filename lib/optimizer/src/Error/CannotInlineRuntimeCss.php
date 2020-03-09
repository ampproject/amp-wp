<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;
use DOMElement;
use Exception;

final class CannotInlineRuntimeCss implements Error
{
    use ErrorProperties;

    const EXCEPTION_STRING                 = 'Cannot inline the amp-runtime CSS in %3$s into %2$s: %1$s.';
    const MISSING_AMP_RUNTIME_STYLE_STRING = 'Cannot inline the amp-runtime CSS in %s: the <style amp-runtime> element is missing.';

    /**
     * Instantiate a CannotInlineRuntimeCss object for an exception that was thrown.
     *
     * @param Exception  $exception       Exception that was thrown.
     * @param DOMElement $ampRuntimeStyle DOM element of the <style amp-runtime> tag that was targeted.
     * @param string     $version         Version string that was meant to be used.
     * @return self
     */
    public static function fromException(Exception $exception, DOMElement $ampRuntimeStyle, $version)
    {
        $version = empty($version) ? 'unspecified version' : "version {$version}";

        return new self(sprintf(self::EXCEPTION_STRING, $exception, new ElementDump($ampRuntimeStyle), $version));
    }

    /**
     * Instantiate a CannotInlineRuntimeCss object for a missing <style amp-runtime> element.
     *
     * @param string $version Version string that was meant to be used.
     * @return self
     */
    public static function fromMissingAmpRuntimeStyle($version)
    {
        $version = empty($version) ? 'unspecified version' : "version {$version}";

        return new self(sprintf(self::MISSING_AMP_RUNTIME_STYLE_STRING, $version));
    }
}
