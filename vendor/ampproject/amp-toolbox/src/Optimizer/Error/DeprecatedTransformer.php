<?php

namespace AmpProject\Optimizer\Error;

use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\Transformer\PreloadHeroImage;

/**
 * Optimizer error object for when a deprecated transformer is being used.
 *
 * @package ampproject/amp-toolbox
 */
final class DeprecatedTransformer implements Error
{
    use ErrorProperties;

    const WITHOUT_REPLACEMENT = 'Use of a deprecated transformer %s.';

    const WITH_REPLACEMENT = 'Use of a deprecated transformer %s, use %s instead.';

    /**
     * Instantiate a DeprecatedTransformer object without a suggested replacement.
     *
     * @param string $deprecated Class of the deprecated transformer.
     * @return self
     */
    public static function withoutReplacement($deprecated)
    {
        return new self(sprintf(self::WITHOUT_REPLACEMENT, $deprecated));
    }

    /**
     * Instantiate a DeprecatedTransformer object with a suggested replacement.
     *
     * @param string $deprecated  Class of the deprecated transformer.
     * @param string $replacement Class of the suggested replacement transformer.
     * @return self
     */
    public static function withReplacement($deprecated, $replacement)
    {
        return new self(sprintf(self::WITH_REPLACEMENT, $deprecated, $replacement));
    }
}
