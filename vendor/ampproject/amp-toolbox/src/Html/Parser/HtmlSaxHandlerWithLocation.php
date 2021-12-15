<?php

namespace AmpProject\Html\Parser;

/**
 * An interface to the HtmlParser visitor that gets called while the HTML is being parsed.
 *
 * @package ampproject/amp-toolbox
 */
interface HtmlSaxHandlerWithLocation extends HtmlSaxHandler
{
    /**
     * Called prior to parsing a document, that is, before startTag().
     *
     * @param DocLocator $locator A locator instance which provides access to the line/column information while SAX
     *                            events are being received by the handler.
     * @return void
     */
    public function setDocLocator(DocLocator $locator);
}
