<?php

namespace AmpProject;

/**
 * Compatibility fix that can be registered.
 *
 * @package ampproject/amp-toolbox
 */
interface CompatibilityFix
{
    /**
     * Register the compatibility fix.
     *
     * @return void
     */
    public static function register();
}
