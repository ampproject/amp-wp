<?php

namespace AmpProject\Html\Parser;

/**
 * An interface to the HtmlParser visitor that gets called while the HTML is being parsed.
 *
 * @package ampproject/amp-toolbox
 */
interface HtmlSaxHandler
{
    /**
     * Handler called when the parser found a new tag.
     *
     * @param ParsedTag $tag New tag that was found.
     * @return void
     */
    public function startTag(ParsedTag $tag);

    /**
     * Handler called when the parser found a closing tag.
     *
     * @param ParsedTag $tag Closing tag that was found.
     * @return void
     */
    public function endTag(ParsedTag $tag);

    /**
     * Handler called when PCDATA is found.
     *
     * @param string $text The PCDATA that was found.
     * @return void
     */
    public function pcdata($text);

    /**
     * Handler called when RCDATA is found.
     *
     * @param string $text The RCDATA that was found.
     * @return void
     */
    public function rcdata($text);

    /**
     * Handler called when CDATA is found.
     *
     * @param string $text The CDATA that was found.
     * @return void
     */
    public function cdata($text);

    /**
     * Handler called when the parser is starting to parse the document.
     *
     * @return void
     */
    public function startDoc();

    /**
     * Handler called when the parsing is done.
     *
     * @return void
     */
    public function endDoc();

    /**
     * Callback for informing that the parser is manufacturing a <body> tag not actually found on the page. This will be
     * followed by a startTag() with the actual body tag in question.
     *
     * @return void
     */
    public function markManufacturedBody();

    /**
     * HTML5 defines how parsers treat documents with multiple body tags: they merge the attributes from the later ones
     * into the first one. Therefore, just before the parser sends the endDoc event, it will also send this event which
     * will provide the attributes from the effective body tag to the client (the handler).
     *
     * @param array<ParsedAttribute> $attributes Array of parsed attributes.
     * @return void
     */
    public function effectiveBodyTag($attributes);
}
