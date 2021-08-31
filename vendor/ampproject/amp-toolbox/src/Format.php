<?php

namespace AmpProject;

/**
 * Interface with constants for the different AMP HTML formats.
 *
 * @package ampproject/amp-toolbox
 */
interface Format
{

    /**
     * AMP for websites format.
     *
     * @var string
     */
    const AMP = 'AMP';

    /**
     * AMP for ads format.
     *
     * @var string
     */
    const AMP4ADS = 'AMP4ADS';

    /**
     * AMP for email format.
     *
     * @var string
     */
    const AMP4EMAIL = 'AMP4EMAIL';
}
