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

    const CUSTOM_ELEMENT  = 'custom-element';
    const CUSTOM_TEMPLATE = 'custom-template';

    /**
     * List of dynamic components
     *
     * This list should be kept in sync with the list of dynamic components at:
     *
     * @see https://github.com/ampproject/amphtml/blob/master/spec/amp-cache-guidelines.md#guidelines-adding-a-new-cache-to-the-amp-ecosystem
     *
     * @var array[]
     */
    const DYNAMIC_COMPONENTS = [
        self::CUSTOM_ELEMENT  => [Extension::GEO],
        self::CUSTOM_TEMPLATE => [],
    ];

    /**
     * List of valid Amp formats.
     *
     * @var string[]
     */
    const FORMATS = ['AMP', 'AMP4EMAIL', 'AMP4ADS'];
}
