<?php

namespace Amp;

final class Amp
{

    /**
     * List of Amp attribute tags that can be appended to the <html> element.
     *
     * @var string[]
     */
    const TAGS = ['amp', '⚡', '⚡4ads', 'amp4ads', '⚡4email', 'amp4email'];

    /**
     * URL of the Amp cache host CDN.
     *
     * @var string
     */
    const CACHE_HOST = 'https://cdn.ampproject.org';

    /**
     * List of valid Amp formats.
     *
     * @var string[]
     */
    const FORMATS = ['AMP', 'AMP4EMAIL', 'AMP4ADS'];
}
