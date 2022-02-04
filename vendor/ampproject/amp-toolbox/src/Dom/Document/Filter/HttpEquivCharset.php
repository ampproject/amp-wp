<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Html\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Document\AfterLoadFilter;
use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Dom\Document\BeforeSaveFilter;
use AmpProject\Dom\Element;
use AmpProject\Html\Tag;
use DOMComment;

/**
 * Filter for http-equiv charset.
 *
 * @package ampproject/amp-toolbox
 */
final class HttpEquivCharset implements BeforeLoadFilter, AfterLoadFilter, BeforeSaveFilter, AfterSaveFilter
{
    /**
     * Value of the content field of the meta tag for the http-equiv compatibility charset.
     *
     * @var string
     */
    const HTML_HTTP_EQUIV_CONTENT_VALUE = 'text/html; charset=utf-8';

    /**
     * Type of the meta tag for the http-equiv compatibility charset.
     *
     * @var string
     */
    const HTML_HTTP_EQUIV_VALUE = 'content-type';

    /**
     * Charset compatibility tag for making DOMDocument behave.
     *
     * See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
     *
     * @var string
     */
    const HTTP_EQUIV_META_TAG = '<meta http-equiv="content-type" content="text/html; charset=utf-8">';

    /**
     * Regex pattern for adding an http-equiv charset for compatibility by anchoring it to the <head> tag.
     *
     * The opening tag pattern contains a comment to make sure we don't match a <head> tag within a comment.
     *
     * @var string
     */
    const HTML_GET_HEAD_OPENING_TAG_PATTERN     = '/(?><!--.*?-->\s*)*<head(?>\s+[^>]*)?>/is';

    /**
     * Regex replacement template for adding an http-equiv charset for compatibility by anchoring it to the <head> tag.
     *
     * @var string
     */
    const HTML_GET_HEAD_OPENING_TAG_REPLACEMENT = '$0' . self::HTTP_EQUIV_META_TAG;

    /**
     * Regex pattern for adding an http-equiv charset for compatibility by anchoring it to the <html> tag.
     *
     * The opening tag pattern contains a comment to make sure we don't match a <html> tag within a comment.
     *
     * @var string
     */
    const HTML_GET_HTML_OPENING_TAG_PATTERN     = '/(?><!--.*?-->\s*)*<html(?>\s+[^>]*)?>/is';

    /**
     * Regex replacement template for adding an http-equiv charset for compatibility by anchoring it to the <html> tag.
     *
     * @var string
     */
    const HTML_GET_HTML_OPENING_TAG_REPLACEMENT = '$0<head>' . self::HTTP_EQUIV_META_TAG . '</head>';

    /**
     * Regex pattern for matching an existing or added http-equiv charset.
     */
    const HTML_GET_HTTP_EQUIV_TAG_PATTERN       = '#<meta http-equiv=([\'"])content-type\1 '
                                                  . 'content=([\'"])text/html; '
                                                  . 'charset=utf-8\2>#i';

    /**
     * Temporary http-equiv charset node added to a document before saving.
     *
     * @var Element|null
     */
    private $temporaryCharset;

    /**
     * Add a http-equiv charset meta tag to the document's <head> node.
     *
     * This is needed to make the DOMDocument behave as it should in terms of encoding.
     * See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
     *
     * @param string $html HTML string to add the http-equiv charset to.
     * @return string Adapted string of HTML.
     */
    public function beforeLoad($html)
    {
        $count = 0;

        // We try first to detect an existing <head> node.
        $result = preg_replace(
            self::HTML_GET_HEAD_OPENING_TAG_PATTERN,
            self::HTML_GET_HEAD_OPENING_TAG_REPLACEMENT,
            $html,
            1,
            $count
        );

        if (is_string($result)) {
            $html = $result;
        }

        // If no <head> was found, we look for the <html> tag instead.
        if ($count < 1) {
            $result = preg_replace(
                self::HTML_GET_HTML_OPENING_TAG_PATTERN,
                self::HTML_GET_HTML_OPENING_TAG_REPLACEMENT,
                $html,
                1,
                $count
            );

            if (is_string($result)) {
                $html = $result;
            }
        }

        // Finally, we just prepend the head with the required http-equiv charset.
        if ($count < 1) {
            $html = '<head>' . self::HTTP_EQUIV_META_TAG . '</head>' . $html;
        }

        return $html;
    }

    /**
     * Remove http-equiv charset again.
     *
     * @param Document $document Document to be processed.
     */
    public function afterLoad(Document $document)
    {
        $meta = $document->head->firstChild;

        // We might have leading comments we need to preserve here.
        while ($meta instanceof DOMComment) {
            $meta = $meta->nextSibling;
        }

        if (
            $meta instanceof Element
            && Tag::META === $meta->tagName
            && self::HTML_HTTP_EQUIV_VALUE === $meta->getAttribute(Attribute::HTTP_EQUIV)
            && self::HTML_HTTP_EQUIV_CONTENT_VALUE === $meta->getAttribute(Attribute::CONTENT)
        ) {
            $document->head->removeChild($meta);
        }
    }

    /**
     * Add a temporary http-equiv charset to the document before saving.
     *
     * @param Document $document Document to be preprocessed before saving it into HTML.
     */
    public function beforeSave(Document $document)
    {
        // Force-add http-equiv charset to make DOMDocument behave as it should.
        // See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
        $this->temporaryCharset = $document->createElement(Tag::META);
        $this->temporaryCharset->setAttribute(Attribute::HTTP_EQUIV, self::HTML_HTTP_EQUIV_VALUE);
        $this->temporaryCharset->setAttribute(Attribute::CONTENT, self::HTML_HTTP_EQUIV_CONTENT_VALUE);
        $document->head->insertBefore($this->temporaryCharset, $document->head->firstChild);
    }

    /**
     * Remove the temporary http-equiv charset again.
     *
     * It is also removed from the DOM again in case saveHTML() is used multiple times.
     *
     * @param string $html String of HTML markup to be preprocessed.
     * @return string Preprocessed string of HTML markup.
     */
    public function afterSave($html)
    {
        if ($this->temporaryCharset instanceof Element) {
            $this->temporaryCharset->parentNode->removeChild($this->temporaryCharset);
        }

        $result = preg_replace(self::HTML_GET_HTTP_EQUIV_TAG_PATTERN, '', $html, 1);

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }
}
