<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document;
use AmpProject\Dom\Document\AfterLoadFilter;
use DOMAttr;

/**
 * Normalizes HTML attributes to be HTML5 compatible.
 *
 * @package ampproject/amp-toolbox
 */
final class NormalizeHtmlAttributes implements AfterLoadFilter
{
    /**
     * Normalizes HTML attributes to be HTML5 compatible.
     *
     * Conditionally removes html[xmlns], and converts html[xml:lang] to html[lang].
     *
     * @param Document $document Document to be processed.
     */
    public function afterLoad(Document $document)
    {
        if (! $document->html->hasAttributes()) {
            return;
        }

        $xmlns = $document->html->attributes->getNamedItem('xmlns');
        if ($xmlns instanceof DOMAttr && 'http://www.w3.org/1999/xhtml' === $xmlns->nodeValue) {
            $document->html->removeAttributeNode($xmlns);
        }

        $xml_lang = $document->html->attributes->getNamedItem('xml:lang');
        if ($xml_lang instanceof DOMAttr) {
            $lang_node = $document->html->attributes->getNamedItem('lang');
            if ((! $lang_node || ! $lang_node->nodeValue) && $xml_lang->nodeValue) {
                // Move the html[xml:lang] value to html[lang].
                $document->html->setAttribute('lang', $xml_lang->nodeValue);
            }
            $document->html->removeAttributeNode($xml_lang);
        }
    }
}
