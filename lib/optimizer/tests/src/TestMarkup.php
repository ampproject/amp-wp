<?php

namespace AmpProject\Optimizer\Tests;

/**
 * Constants that provide the test markup to use.
 *
 * These are taken from the canonical AMP Package (Go) project, with some adaptations:
 * - all attribute values are enclosed in double quotes;
 * - DOCTYPE is uppercase.
 *
 * @see     https://github.com/ampproject/amppackager/blob/releases/transformer/internal/testing/testing.go
 *
 * @package ampproject/optimizer
 */
final class TestMarkup
{

    /**
     * Associative array of mapping data for stubbing remote requests.
     *
     * @var array
     */
    const STUBBED_REMOTE_REQUESTS = [
        'https://cdn.ampproject.org/rtv/metadata'               => '{"ampRuntimeVersion":"012345678900000","ampCssUrl":"https://cdn.ampproject.org/rtv/012345678900000/v0.css","canaryPercentage":"0.1","diversions":["023456789000000","034567890100000","045678901200000"]}',
        'https://cdn.ampproject.org/v0.css'                     => '/* v0.css */',
    ];

    // Doctype is the doctype expected for AMP documents.
    const DOCTYPE = '<!DOCTYPE html>';

    // LinkCanonical is a link to the canonical document.
    const LINK_CANONICAL = '<link href="self.html" rel="canonical">';

    // LinkFavicon is an example link tag.
    const LINK_FAVICON = '<link href="https://example.com/favicon.ico" rel="icon">';

    // LinkGoogleFont is a Google Font stylesheet.
    const LINK_GOOGLE_FONT = '<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">';

    // LinkGoogleFontPreconnect is a preconnect for Google Fonts.
    const LINK_GOOGLE_FONT_PRECONNECT = '<link crossorigin="" href="https://fonts.gstatic.com/" rel="dns-prefetch preconnect">';

    // LinkStylesheetGoogleFont is a link tag for a Google Font.
    const LINK_STYLESHEET_GOOGLE_FONT = '<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">';

    // MetaCharset is a required tag for an AMP document.
    const META_CHARSET = '<meta charset="utf-8">';

    // MetaViewport is a required tag for an AMP document.
    const META_VIEWPORT = '<meta content="width=device-width,minimum-scale=1,initial-scale=1" name="viewport">';

    // NoscriptAMPBoilerplate is the standard style for <noscript> tag.
    const NOSCRIPT_AMPBOILERPLATE = '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';

    // ScriptAMPAccess is the script for amp-access.
    const SCRIPT_AMPACCESS = '<script async custom-element="amp-access" src="https://cdn.ampproject.org/v0/amp-accesss-0.1.js"></script>';

    // ScriptAMPAd is the script for amp-ad.
    const SCRIPT_AMPAD = '<script async custom-element="amp-ad" src="https://cdn.ampproject.org/v0/amp-ad-0.1.js"></script>';

    // ScriptAMPAnalytics is the script for amp-analytics.
    const SCRIPT_AMPANALYTICS = '<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';

    // ScriptAMPAudio is the script for amp-audio.
    const SCRIPT_AMPAUDIO = '<script async custom-element="amp-audio" src="https://cdn.ampproject.org/v0/amp-audio-0.1.js"></script>';

    // ScriptAMPDynamicCSSClasses is the script for amp-dynamic-css-class.
    const SCRIPT_AMPDYNAMIC_CSSCLASSES = '<script async custom-element="amp-dynamic-css-classes" src="https://cdn.ampproject.org/v0/amp-dynamic-css-classes-0.1.js"></script>';

    // ScriptAMPExperiment is the script for amp-experiment.
    const SCRIPT_AMPEXPERIMENT = '<script async custom-element="amp-experiment" src="https://cdn.ampproject.org/v0/amp-experiment-0.1.js"></script>';

    // ScriptAMPForm is the script for amp-form.
    const SCRIPT_AMPFORM = '<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>';

    // ScriptAMPMraid is the script for amp-mraid.
    const SCRIPT_AMPMRAID = '<script async host-service="amp-mraid" src="https://cdn.ampproject.org/v0/amp-mraid-0.1.js"></script>';

    // ScriptAMPMustache is the script for amp-mustache.
    const SCRIPT_AMPMUSTACHE = '<script async custom-template="amp-mustache" src="https://cdn.ampproject.org/v0/amp-mustache-0.1.js"></script>';

    // ScriptAMPRuntime is the AMP script tag.
    const SCRIPT_AMPRUNTIME = '<script async src="https://cdn.ampproject.org/v0.js"></script>';

    // ScriptAMPViewerRuntime is the AMP viewer runtime script tag.
    const SCRIPT_AMPVIEWER_RUNTIME = '<script async src="https://cdn.ampproject.org/v0/amp-viewer-integration-0.1.js"></script>';

    // ScriptAMP4AdsRuntime is the AMP4Ads script tag.
    const SCRIPT_AMP_4_ADS_RUNTIME = '<script async src="https://cdn.ampproject.org/amp4ads-v0.js"></script>';

    // ScriptAMPStory is the script for amp-story.
    const SCRIPT_AMPSTORY = '<script async custom-element="amp-story" src="https://cdn.ampproject.org/v0/amp-story-0.1.js"></script>';

    // StyleAMP4AdsBoilerplate is the script for amp4ads boilerplate.
    const STYLE_AMP_4_ADS_BOILERPLATE = '<style amp4ads-boilerplate>body{visibility;hidden}</style>';

    // StyleAMP4EmailBoilerplate is the script for amp4email boilerplate.
    const STYLE_AMP_4_EMAIL_BOILERPLATE = '<style amp4email-boilerplate>body{visibility;hidden}</style>';

    // StyleAMPBoilerplate is the standard style.
    const STYLE_AMPBOILERPLATE = '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>';

    // StyleAMPCustom is a customized stylesheet for an AMP document.
    const STYLE_AMPCUSTOM = '<style amp-custom>#lemur { color: #adaaad }</style>';

    // StyleAMPRuntime is an injected tag from server-side rendering.
    const STYLE_AMPRUNTIME = '<style amp-runtime=""></style>';

    // Title is a title tag for an AMP document.
    const TITLE = '<title>Hello AMP</title>';
}
