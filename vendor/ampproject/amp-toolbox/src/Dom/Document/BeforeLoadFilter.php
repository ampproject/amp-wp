<?php

namespace AmpProject\Dom\Document;

/**
 * Filter the HTML before it is loaded into the Dom\Document.
 *
 * @package ampproject/amp-toolbox
 */
interface BeforeLoadFilter extends Filter
{
    /**
     * Preprocess the HTML to be loaded into the Dom\Document.
     *
     * @param string $html String of HTML markup to be preprocessed.
     * @return string Preprocessed string of HTML markup.
     */
    public function beforeLoad($html);
}
