<?php

namespace AmpProject;

/**
 * Interface with constants for the different request destinations that are supported.
 *
 * For the purposes of the AMP implementation, we are only interested in the
 * request destinations that are valid values for the 'as' attribute in preloads.
 *
 * Full list of request destinations:
 * @see https://fetch.spec.whatwg.org/#concept-request-destination
 *
 * @package ampproject/amp-toolbox
 */
interface RequestDestination
{

    /**
     * Audio file, as typically used in <audio>.
     *
     * @var string
     */
    const AUDIO = 'audio';

    /**
     * An HTML document intended to be embedded by a <frame> or <iframe>.
     *
     * @var string
     */
    const DOCUMENT = 'document';

    /**
     * A resource to be embedded inside an <embed> element.
     *
     * @var string
     */
    const EMBED = 'embed';

    /**
     * Resource to be accessed by a fetch or XHR request, such as an ArrayBuffer or JSON file.
     *
     * @var string
     */
    const FETCH = 'fetch';

    /**
     * Font file.
     *
     * @var string
     */
    const FONT = 'font';

    /**
     * Image file.
     *
     * @var string
     */
    const IMAGE = 'image';

    /**
     * A resource to be embedded inside an <object> element.
     *
     * @var string
     */
    const OBJECT = 'object';

    /**
     * JavaScript file.
     *
     * @var string
     */
    const SCRIPT = 'script';

    /**
     * CSS stylesheet.
     *
     * @var string
     */
    const STYLE = 'style';

    /**
     * WebVTT file.
     *
     * @var string
     */
    const TRACK = 'track';

    /**
     * A JavaScript web worker or shared worker.
     *
     * @var string
     */
    const WORKER = 'worker';

    /**
     * Video file, as typically used in <video>.
     *
     * @var string
     */
    const VIDEO = 'video';
}
