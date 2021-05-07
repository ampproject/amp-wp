<?php

namespace AmpProject;

/**
 * Interface with constants for AMP extensions.
 *
 * @package ampproject/amp-toolbox
 */
interface Extension
{

    const AD                    = 'amp-ad';
    const ANALYTICS             = 'amp-analytics';
    const ANIM                  = 'amp-anim';
    const AUDIO                 = 'amp-audio';
    const BIND                  = 'amp-bind';
    const BRIGHTCOVE            = 'amp-brightcove';
    const DAILYMOTION           = 'amp-dailymotion';
    const DELIGHT_PLAYER        = 'amp-delight-player';
    const DYNAMIC_CSS_CLASSES   = 'amp-dynamic-css-classes';
    const EXPERIMENT            = 'amp-experiment';
    const FACEBOOK              = 'amp-facebook';
    const GEO                   = 'amp-geo';
    const GFYCAT                = 'amp-gfycat';
    const GOOGLE_DOCUMENT_EMBED = 'amp-google-document-embed';
    const IFRAME                = 'amp-iframe';
    const IMG                   = 'amp-img';
    const IMGUR                 = 'amp-imgur';
    const INSTAGRAM             = 'amp-instagram';
    const MUSTACHE              = 'amp-mustache';
    const PINTEREST             = 'amp-pinterest';
    const PIXEL                 = 'amp-pixel';
    const REDDIT                = 'amp-reddit';
    const SOCIAL_SHARE          = 'amp-social-share';
    const STORY                 = 'amp-story';
    const TWITTER               = 'amp-twitter';
    const VIDEO                 = 'amp-video';
    const VIDEO_IFRAME          = 'amp-video-iframe';
    const VIMEO                 = 'amp-vimeo';
    const YOUTUBE               = 'amp-youtube';
    const WISTIA_PLAYER         = 'amp-wistia-player';

    /**
     * Prefix of an AMP extension.
     *
     * @var string.
     */
    const PREFIX = 'amp-';
}
