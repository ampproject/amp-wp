<?php

namespace Amp;

/**
 * Interface with constants for Amp extensions.
 *
 * @package amp/common
 */
interface Extension
{

    const ANALYTICS           = 'amp-analytics';
    const AUDIO               = 'amp-audio';
    const BIND                = 'amp-bind';
    const DYNAMIC_CSS_CLASSES = 'amp-dynamic-css-classes';
    const EXPERIMENT          = 'amp-experiment';
    const GEO                 = 'amp-geo';
    const MUSTACHE            = 'amp-mustache';
    const PIXEL               = 'amp-pixel';
    const SOCIAL_SHARE        = 'amp-social-share';

    /**
     * Prefix of an Amp extension.
     *
     * @var string.
     */
    const PREFIX = 'amp-';
    const STORY  = 'amp-story';
}
