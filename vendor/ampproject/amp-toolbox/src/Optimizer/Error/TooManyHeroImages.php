<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\Transformer\PreloadHeroImage;

/**
 * Optimizer error object for when too many images are marked for being optimized as hero images.
 *
 * @package ampproject/amp-toolbox
 */
final class TooManyHeroImages implements Error
{
    use ErrorProperties;

    const PAST_MAX_STRING = 'Too many images with the "data-hero" attribute were detected, the maximum allowed is %d.';

    /**
     * Instantiate a TooManyHeroImages object for when a hero image was detected past the maximum allowed.
     *
     * @return self
     */
    public static function whenPastMaximum()
    {
        return new self(sprintf(self::PAST_MAX_STRING, PreloadHeroImage::DATA_HERO_MAX));
    }
}
