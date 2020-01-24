<?php

namespace Amp;

/**
 * Interface with constants for Amp extensions.
 *
 * @package amp/common
 */
interface Extension
{

    const BIND                = 'amp-bind';
    const DYNAMIC_CSS_CLASSES = 'amp-dynamic-css-classes';
    const EXPERIMENT          = 'amp-experiment';
    const GEO                 = 'amp-geo';
    /**
     * Prefix of an Amp extension.
     *
     * @var string.
     */
    const PREFIX = 'amp-';
    const STORY  = 'amp-story';
}
