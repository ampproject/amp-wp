<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;
use DOMElement;

final class CannotPreloadImage implements Error
{

    use ErrorProperties;

    const SRCSET_STRING = 'Not preloading the hero image because of the presence of a "srcset" attribute, which can currently only be preloaded by Chromium-based browsers (see https://web.dev/preload-responsive-images/): ';

    /**
     * Instantiate a CannotPreloadImage object for an image with a srcset attribute.
     *
     * @param DOMElement $element Image element that has the srcset attribute.
     * @return self
     */
    public static function fromImageWithSrcsetAttribute(DOMElement $element)
    {
        return new self(self::SRCSET_STRING . new ElementDump($element));
    }
}
