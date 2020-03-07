<?php

namespace AmpProject;

/**
 * Interface with constants for AMP extensions.
 *
 * @package ampproject/common
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
    const STORY               = 'amp-story';
    const VIDEO               = 'amp-video';
    const YOUTUBE             = 'amp-youtube';

    /**
     * Prefix of an AMP extension.
     *
     * @var string.
     */
    const PREFIX = 'amp-';
}
