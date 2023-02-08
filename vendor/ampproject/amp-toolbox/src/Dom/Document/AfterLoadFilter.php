<?php

namespace AmpProject\Dom\Document;

use AmpProject\Dom\Document;

/**
 * Filter the Dom\Document after it was loaded.
 *
 * @package ampproject/amp-toolbox
 */
interface AfterLoadFilter extends Filter
{
    /**
     * Process the Document after the html loaded into the Dom\Document.
     *
     * @param Document $document Document to be processed.
     */
    public function afterLoad(Document $document);
}
