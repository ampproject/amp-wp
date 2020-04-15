<?php

namespace AmpProject\Dom;

use AmpProject\Attribute;
use AmpProject\DevMode;
use AmpProject\Tag;
use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;

/**
 * Class AmpProject\Dom\Document.
 *
 * Abstract away some of the difficulties of working with PHP's DOMDocument.
 *
 * @property DOMXPath    $xpath       XPath query object for this document.
 * @property DOMElement  $html        The document's <html> element.
 * @property DOMElement  $head        The document's <head> element.
 * @property DOMElement  $body        The document's <body> element.
 * @property DOMNodeList $ampElements The document's <amp-*> elements.
 *
 * @package ampproject/common
 */
final class Document extends DOMDocument
{

    /**
     * AMP requires the HTML markup to be encoded in UTF-8.
     *
     * @var string
     */
    const AMP_ENCODING = 'utf-8';

    /**
     * Encoding identifier to use for an unknown encoding.
     *
     * "auto" is recognized by mb_convert_encoding() as a special value.
     *
     * @var string
     */
    const UNKNOWN_ENCODING = 'auto';

    /**
     * Default document type to use.
     *
     * @var string
     */
    const DEFAULT_DOCTYPE = '<!DOCTYPE html>';

    /**
     * Encoding detection order in case we have to guess.
     *
     * This list of encoding detection order is just a wild guess and might need fine-tuning over time.
     * If the charset was not provided explicitly, we can really only guess, as the detection can
     * never be 100% accurate and reliable.
     *
     * @var string
     */
    const ENCODING_DETECTION_ORDER = 'UTF-8, EUC-JP, eucJP-win, JIS, ISO-2022-JP, ISO-8859-15, ISO-8859-1, ASCII';

    /**
     * Attribute prefix for AMP-bind data attributes.
     *
     * @var string
     */
    const AMP_BIND_DATA_ATTR_PREFIX = 'data-amp-bind-';

    /**
     * Pattern for HTML attribute accounting for binding attr name, boolean attribute, single/double-quoted attribute
     * value, and unquoted attribute values.
     *
     * @var string
     */
    const AMP_BIND_ATTR_PATTERN = '#^\s+(?P<name>\[?[a-zA-Z0-9_\-]+\]?)(?P<value>=(?>"[^"]*+"|\'[^\']*+\'|[^\'"\s]+))?#';

    /**
     * Match all start tags that contain a binding attribute.
     *
     * @var string
     */
    const AMP_BIND_START_TAGS_PATTERN = '#<'
                                        . '(?P<name>[a-zA-Z0-9_\-]+)'               // Tag name.
                                        . '(?P<attrs>\s'                            // Attributes.
                                        . '(?>[^>"\'\[\]]+|"[^"]*+"|\'[^\']*+\')*+' // Non-binding attributes tokens.
                                        . '\[[a-zA-Z0-9_\-]+\]'                     // One binding attribute key.
                                        . '(?>[^>"\']+|"[^"]*+"|\'[^\']*+\')*+'     // Any attribute tokens, including binding ones.
                                        . ')>#s';

    /*
     * Regular expressions to fetch the individual structural tags.
     * These patterns were optimized to avoid extreme backtracking on large documents.
     */
    const HTML_STRUCTURE_DOCTYPE_PATTERN = '/^(?<doctype>[^<]*(?>\s*<!--.*?-->\s*)*<!doctype(?>\s+[^>]+)?>)/is';
    const HTML_STRUCTURE_HTML_START_TAG  = '/^(?<html_start>[^<]*(?>\s*<!--.*?-->\s*)*<html(?>\s+[^>]*)?>)/is';
    const HTML_STRUCTURE_HTML_END_TAG    = '/(?<html_end><\/html(?>\s+[^>]*)?>.*)$/is';
    const HTML_STRUCTURE_HEAD_START_TAG  = '/^[^<]*(?><!--.*?-->\s*)*(?><head(?>\s+[^>]*)?>)/is';
    const HTML_STRUCTURE_BODY_START_TAG  = '/^[^<]*(?><!--.*-->\s*)*(?><body(?>\s+[^>]*)?>)/is';
    const HTML_STRUCTURE_BODY_END_TAG    = '/(?><\/body(?>\s+[^>]*)?>.*)$/is';
    const HTML_STRUCTURE_HEAD_TAG        = '/^(?>[^<]*(?><head(?>\s+[^>]*)?>).*?<\/head(?>\s+[^>]*)?>)/is';
    const HTML_DOCTYPE_HTML_4_SUFFIX     = ' PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"';

    // Regex patterns used for securing and restoring the doctype node.
    const HTML_SECURE_DOCTYPE_IF_NOT_FIRST_PATTERN = '/(^[^<]*(?>\s*<!--[^>]*>\s*)+<)(!)(doctype)(\s+[^>]+?)(>)/i';
    const HTML_RESTORE_DOCTYPE_PATTERN             = '/(^[^<]*(?>\s*<!--[^>]*>\s*)+<)(!--amp-)(doctype)(\s+[^>]+?)(-->)/i';

    // Regex pattern used for removing Internet Explorer conditional comments.
    const HTML_IE_CONDITIONAL_COMMENTS_PATTERN = '/<!--(?>\[if\s|<!\[endif)(?>[^>]+(?<!--)>)*(?>[^>]+(?<=--)>)/i';

    /**
     * Xpath query to fetch the attributes that are being URL-encoded by saveHTML().
     *
     * @var string
     */
    const XPATH_URL_ENCODED_ATTRIBUTES_QUERY = './/*/@src|.//*/@href|.//*/@action';

    /**
     * Xpath query to fetch the elements containing Mustache templates (both <template type=amp-mustache> and <script type=text/plain template=amp-mustache>).
     *
     * @var string
     */
    const XPATH_MUSTACHE_TEMPLATE_ELEMENTS_QUERY = './/self::template[ @type = "amp-mustache" ]|//self::script[ @type = "text/plain" and @template = "amp-mustache" ]';

    /**
     * Error message to use when the __get() is triggered for an unknown property.
     *
     * @var string
     */
    const PROPERTY_GETTER_ERROR_MESSAGE = 'Undefined property: AmpProject\\Dom\\Document::';

    // Regex patterns and values used for adding and removing http-equiv charsets for compatibility.
    const HTML_GET_HEAD_OPENING_TAG_PATTERN     = '/(?><!--.*?-->\s*)*<head(?>\s+[^>]*)?>/is'; // This pattern contains a comment to make sure we don't match a <head> tag within a comment.    const HTML_GET_HEAD_OPENING_TAG_REPLACEMENT = '$0<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    const HTML_GET_HEAD_OPENING_TAG_REPLACEMENT = '$0<meta http-equiv="content-type" content="text/html; charset=utf-8">';
    const HTML_GET_HTTP_EQUIV_TAG_PATTERN       = '#<meta http-equiv=([\'"])content-type\1 content=([\'"])text/html; charset=utf-8\2>#i';
    const HTML_HTTP_EQUIV_VALUE                 = 'content-type';
    const HTML_HTTP_EQUIV_CONTENT_VALUE         = 'text/html; charset=utf-8';

    // Regex patterns used for finding tags or extracting attribute values in an HTML string.
    const HTML_FIND_TAG_WITHOUT_ATTRIBUTE_PATTERN = '/<%1$s[^>]*?>[^<]*(?><\/%1$s>)?/i';
    const HTML_FIND_TAG_WITH_ATTRIBUTE_PATTERN    = '/<%1$s [^>]*?\s*%2$s\s*=[^>]*?>[^<]*(?><\/%1$s>)?/i';
    const HTML_EXTRACT_ATTRIBUTE_VALUE_PATTERN    = '/%s=(?>([\'"])(?<full>.*)?\1|(?<partial>[^ \'";]+))/';
    const HTML_FIND_TAG_DELIMITER                 = '/';

    /**
     * Pattern to match an AMP emoji together with its variant (amp4ads, amp4email, ...).
     *
     * @var string
     */
    const AMP_EMOJI_ATTRIBUTE_PATTERN = '/(<html [^>]*?)(' . Attribute::AMP_EMOJI_ALT . '|' . Attribute::AMP_EMOJI . ')([^\s^>]*)/iu';

    // Attribute to use as a placeholder to move the emoji AMP symbol (⚡) over to DOM.
    const EMOJI_AMP_ATTRIBUTE_PLACEHOLDER = 'emoji-amp';

    // Patterns used for fixing the mangled encoding of src attributes with SVG data.
    const I_AMPHTML_SIZER_REGEX_PATTERN = '/(?<before_src><i-amphtml-sizer\s+[^>]*>\s*<img\s+[^>]*?\s+src=([\'"]))(?<src>.*?)(?<after_src>\2><\/i-amphtml-sizer>)/i';
    const SRC_SVG_REGEX_PATTERN         = '/^\s*(?<type>[^<]+)(?<value><svg[^>]+>)\s*$/i';

    /**
     * Associative array of encoding mappings.
     *
     * Translates HTML charsets into encodings PHP can understand.
     *
     * @var string[]
     */
    const ENCODING_MAP = [
        // Assume ISO-8859-1 for some charsets.
        'latin-1' => 'ISO-8859-1',
    ];

    /**
     * Whether `data-ampdevmode` was initially set on the the document element.
     *
     * @var bool
     */
    private $hasInitialAmpDevMode = false;

    /**
     * The original encoding of how the AmpProject\Dom\Document was created.
     *
     * This is stored to do an automatic conversion to UTF-8, which is
     * a requirement for AMP.
     *
     * @var string
     */
    private $originalEncoding;

    /**
     * Store the placeholder comments that were generated to replace <noscript> elements.
     *
     * @see maybeReplaceNoscriptElements()
     *
     * @var string[]
     */
    private $noscriptPlaceholderComments = [];

    /**
     * Store whether mustache template tags were replaced and need to be restored.
     *
     * @see replaceMustacheTemplateTokens()
     *
     * @var bool
     */
    private $mustacheTagsReplaced = false;

    /**
     * Whether we had secured a doctype that needs restoring or not.
     *
     * This is an int as it receives the $count from the preg_replace().
     *
     * @var int
     */
    private $securedDoctype = 0;

    /**
     * Whether the self-closing tags were transformed and need to be restored.
     *
     * This avoids duplicating this effort (maybe corrupting the DOM) on multiple calls to saveHTML().
     *
     * @var bool
     */
    private $selfClosingTagsTransformed = false;

    /**
     * Store the emoji that was used to represent the AMP attribute.
     *
     * There are a few variations, so we want to keep track of this.
     *
     * @see https://github.com/ampproject/amphtml/issues/25990
     *
     * @var string
     */
    private $usedAmpEmoji;

    /**
     * Creates a new AmpProject\Dom\Document object
     *
     * @link  https://php.net/manual/domdocument.construct.php
     *
     * @param string $version  Optional. The version number of the document as part of the XML declaration.
     * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
     */
    public function __construct($version = '', $encoding = null)
    {
        $this->originalEncoding = (string)$encoding ?: self::UNKNOWN_ENCODING;
        parent::__construct($version ?: '1.0', self::AMP_ENCODING);
    }

    /**
     * Named constructor to provide convenient way of transforming HTML into DOM.
     *
     * @param string $html     HTML to turn into a DOM.
     * @param string $encoding Optional. Encoding of the provided HTML string.
     * @return Document|false DOM generated from provided HTML, or false if the transformation failed.
     */
    public static function fromHtml($html, $encoding = null)
    {
        $dom = new self('', $encoding);

        if (! $dom->loadHTML($html)) {
            return false;
        }

        return $dom;
    }

    /**
     * Named constructor to provide convenient way of transforming a HTML fragment into DOM.
     *
     * The difference to Document::fromHtml() is that fragments are not normalized as to their structure.
     *
     * @param string $html     HTML to turn into a DOM.
     * @param string $encoding Optional. Encoding of the provided HTML string.
     * @return Document|false DOM generated from provided HTML, or false if the transformation failed.
     */
    public static function fromHtmlFragment($html, $encoding = null)
    {
        $dom = new self('', $encoding);

        if (! $dom->loadHTMLFragment($html)) {
            return false;
        }

        return $dom;
    }

    /**
     * Named constructor to provide convenient way of retrieving the DOM from a node.
     *
     * @param DOMNode $node Node to retrieve the DOM from. This is being modified by reference (!).
     * @return Document DOM generated from provided HTML, or false if the transformation failed.
     */
    public static function fromNode(DOMNode &$node)
    {
        /**
         * Document of the node.
         *
         * If the node->ownerDocument returns null, the node is the document.
         *
         * @var DOMDocument
         */
        $root = $node->ownerDocument === null ? $node : $node->ownerDocument;

        if ($root instanceof self) {
            return $root;
        }

        $dom = new self();

        // We replace the $node by reference, to make sure the next lines of code will
        // work as expected with the new document.
        // Otherwise $dom and $node would refer to two different DOMDocuments.
        $node = $dom->importNode($root->documentElement ?: $root, true);
        $dom->appendChild($node);

        $dom->hasInitialAmpDevMode = $dom->documentElement->hasAttribute(DevMode::DEV_MODE_ATTRIBUTE);

        return $dom;
    }

    /**
     * Reset the internal optimizations of the Document object.
     *
     * This might be needed if you are doing an operation that causes the cached
     * nodes and XPath objects to point to the wrong document.
     *
     * @return self Reset version of the Document object.
     */
    private function reset()
    {
        // Drop references to old DOM document.
        unset($this->xpath, $this->head, $this->body);

        // Reference of the document itself doesn't change here, but might need to change in the future.
        return $this;
    }

    /**
     * Load HTML from a string.
     *
     * @link  https://php.net/manual/domdocument.loadhtml.php
     *
     * @param string     $source  The HTML string.
     * @param int|string $options Optional. Specify additional Libxml parameters.
     * @return bool true on success or false on failure.
     */
    public function loadHTML($source, $options = 0)
    {
        $source  = $this->normalizeDocumentStructure($source);
        $success = $this->loadHTMLFragment($source, $options);

        if ($success) {
            $this->insertMissingCharset();

            // Do some further clean-up.
            $this->deduplicateTag(Tag::HEAD);
            $this->deduplicateTag(Tag::BODY);
            $this->moveInvalidHeadNodesToBody();
            $this->movePostBodyNodesToBody();
            $this->convertHeadProfileToLink();
        }

        return $success;
    }

    /**
     * Load a HTML fragment from a string.
     *
     * @param string     $source  The HTML fragment string.
     * @param int|string $options Optional. Specify additional Libxml parameters.
     * @return bool true on success or false on failure.
     */
    public function loadHTMLFragment($source, $options = 0)
    {
        $this->reset();

        $source = $this->convertAmpBindAttributes($source);
        $source = $this->replaceSelfClosingTags($source);
        $source = $this->maybeReplaceNoscriptElements($source);
        $source = $this->secureMustacheScriptTemplates($source);
        $source = $this->secureDoctypeNode($source);
        $source = $this->convertAmpEmojiAttribute($source);

        list($source, $this->originalEncoding) = $this->detectAndStripEncoding($source);

        if (self::AMP_ENCODING !== strtolower($this->originalEncoding)) {
            $source = $this->adaptEncoding($source);
        }

        // Force-add http-equiv charset to make DOMDocument behave as it should.
        // See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
        $source = preg_replace(self::HTML_GET_HEAD_OPENING_TAG_PATTERN, self::HTML_GET_HEAD_OPENING_TAG_REPLACEMENT, $source, 1);

        $libxml_previous_state = libxml_use_internal_errors(true);

        $options |= LIBXML_COMPACT;

        /*
         * LIBXML_HTML_NODEFDTD is only available for libxml 2.7.8+.
         * This should be the case for PHP 5.4+, but some systems seem to compile against a custom libxml version that
         * is lower than expected.
         */
        if (defined('LIBXML_HTML_NODEFDTD')) {
            $options |= constant('LIBXML_HTML_NODEFDTD');
        }

        $success = parent::loadHTML($source, $options);

        libxml_clear_errors();
        libxml_use_internal_errors($libxml_previous_state);

        if ($success) {
            $this->normalizeHtmlAttributes();
            $this->restoreMustacheScriptTemplates();

            // Remove http-equiv charset again.
            $meta = $this->head->firstChild;

            // We might have leading comments we need to preserve here.
            while ($meta instanceof DOMComment) {
                $meta = $meta->nextSibling;
            }

            if (
                $meta instanceof DOMElement
                && Tag::META === $meta->tagName
                && self::HTML_HTTP_EQUIV_VALUE === $meta->getAttribute(Attribute::HTTP_EQUIV)
                && (self::HTML_HTTP_EQUIV_CONTENT_VALUE) === $meta->getAttribute(Attribute::CONTENT)
            ) {
                $this->head->removeChild($meta);
            }

            $this->hasInitialAmpDevMode = $this->documentElement->hasAttribute(DevMode::DEV_MODE_ATTRIBUTE);
        }

        return $success;
    }

    /**
     * Dumps the internal document into a string using HTML formatting.
     *
     * @link  https://php.net/manual/domdocument.savehtml.php
     *
     * @param DOMNode $node Optional. Parameter to output a subset of the document.
     * @return string The HTML, or false if an error occurred.
     */
    public function saveHTML(DOMNode $node = null)
    {
        return $this->saveHTMLFragment($node);
    }

    /**
     * Dumps the internal document fragment into a string using HTML formatting.
     *
     * @param DOMNode $node Optional. Parameter to output a subset of the document.
     * @return string The HTML fragment, or false if an error occurred.
     */
    public function saveHTMLFragment(DOMNode $node = null)
    {
        $this->replaceMustacheTemplateTokens();

        // Force-add http-equiv charset to make DOMDocument behave as it should.
        // See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
        $charset = $this->createElement(Tag::META);
        $charset->setAttribute(Attribute::HTTP_EQUIV, self::HTML_HTTP_EQUIV_VALUE);
        $charset->setAttribute(Attribute::CONTENT, self::HTML_HTTP_EQUIV_CONTENT_VALUE);
        $this->head->insertBefore($charset, $this->head->firstChild);

        if (null === $node || PHP_VERSION_ID >= 70300) {
            $html = parent::saveHTML($node);
        } else {
            $html = $this->extractNodeViaFragmentBoundaries($node);
        }

        // Remove http-equiv charset again.
        // It is also removed from the DOM again in case saveHTML() is used multiple times.
        $this->head->removeChild($charset);
        $html = preg_replace(self::HTML_GET_HTTP_EQUIV_TAG_PATTERN, '', $html, 1);

        $html = $this->restoreDoctypeNode($html);
        $html = $this->restoreMustacheTemplateTokens($html);
        $html = $this->maybeRestoreNoscriptElements($html);
        $html = $this->restoreSelfClosingTags($html);
        $html = $this->restoreAmpEmojiAttribute($html);
        $html = $this->fixSvgSourceAttributeEncoding($html);

        // Whitespace just causes unit tests to fail... so whitespace begone.
        if ('' === trim($html)) {
            return '';
        }

        return $html;
    }

    /**
     * Add the required utf-8 meta charset tag if it is still missing.
     */
    private function insertMissingCharset()
    {
        // Bail if a charset tag is already present.
        if ($this->xpath->query('.//meta[ @charset ]')->item(0)) {
            return;
        }

        $charset = $this->createElement(Tag::META);
        $charset->setAttribute(Attribute::CHARSET, self::AMP_ENCODING);
        $this->head->insertBefore($charset, $this->head->firstChild);
    }

    /**
     * Extract a node's HTML via fragment boundaries.
     *
     * Temporarily adds fragment boundary comments in order to locate the desired node to extract from
     * the given HTML document. This is required because libxml seems to only preserve whitespace when
     * serializing when calling DOMDocument::saveHTML() on the entire document. If you pass the element
     * to DOMDocument::saveHTML() then formatting whitespace gets added unexpectedly. This is seen to
     * be fixed in PHP 7.3, but for older versions of PHP the following workaround is needed.
     *
     * @param DOMNode $node Node to extract the HTML for.
     * @return string Extracted HTML string.
     */
    private function extractNodeViaFragmentBoundaries(DOMNode $node)
    {
        $boundary      = 'fragment_boundary:' . $this->rand();
        $startBoundary = $boundary . ':start';
        $endBoundary   = $boundary . ':end';
        $commentStart  = $this->createComment($startBoundary);
        $commentEnd    = $this->createComment($endBoundary);

        $node->parentNode->insertBefore($commentStart, $node);
        $node->parentNode->insertBefore($commentEnd, $node->nextSibling);

        $html = preg_replace(
            '/^.*?' . preg_quote("<!--{$startBoundary}-->", '/') . '(.*)' . preg_quote("<!--{$endBoundary}-->", '/') . '.*?\s*$/s',
            '$1',
            parent::saveHTML()
        );

        $node->parentNode->removeChild($commentStart);
        $node->parentNode->removeChild($commentEnd);

        return $html;
    }

    /**
     * Normalize the document structure.
     *
     * This makes sure the document adheres to the general structure that AMP requires:
     *   ```
     *   <!DOCTYPE html>
     *   <html>
     *     <head>
     *       <meta charset="utf-8">
     *     </head>
     *     <body>
     *     </body>
     *   </html>
     *   ```
     *
     * @param string $content Content to normalize the structure of.
     * @return string Normalized content.
     */
    private function normalizeDocumentStructure($content)
    {
        $matches   = [];
        $doctype   = self::DEFAULT_DOCTYPE;
        $htmlStart = '<html>';
        $htmlEnd   = '</html>';

        // Strip IE conditional comments, which are supported by IE 5-9 only (which AMP doesn't support).
        $content = preg_replace(self::HTML_IE_CONDITIONAL_COMMENTS_PATTERN, '', $content);

        // Detect and strip <!doctype> tags.
        if (preg_match(self::HTML_STRUCTURE_DOCTYPE_PATTERN, $content, $matches)) {
            $doctype = $matches['doctype'];
            $content = preg_replace(self::HTML_STRUCTURE_DOCTYPE_PATTERN, '', $content, 1);
        }

        // Detect and strip <html> tags.
        if (preg_match(self::HTML_STRUCTURE_HTML_START_TAG, $content, $matches)) {
            $htmlStart = $matches['html_start'];
            $content   = preg_replace(self::HTML_STRUCTURE_HTML_START_TAG, '', $content, 1);

            preg_match(self::HTML_STRUCTURE_HTML_END_TAG, $content, $matches);
            $htmlEnd = isset($matches['html_end']) ? $matches['html_end'] : $htmlEnd;
            $content = preg_replace(self::HTML_STRUCTURE_HTML_END_TAG, '', $content, 1);
        }

        // Detect <head> and <body> tags and add as needed.
        if (! preg_match(self::HTML_STRUCTURE_HEAD_START_TAG, $content, $matches)) {
            if (! preg_match(self::HTML_STRUCTURE_BODY_START_TAG, $content, $matches)) {
                // Both <head> and <body> missing.
                $content = "<head></head><body>{$content}</body>";
            } else {
                // Only <head> missing.
                $content = "<head></head>{$content}";
            }
        } elseif (! preg_match(self::HTML_STRUCTURE_BODY_END_TAG, $content, $matches)) {
            // Only <body> missing.
            // @todo This is an expensive regex operation, look into further optimization.
            $content = preg_replace(self::HTML_STRUCTURE_HEAD_TAG, '$0<body>', $content, 1);
            $content .= '</body>';
        }

        $content = "{$htmlStart}{$content}{$htmlEnd}";

        // Reinsert a standard doctype (while preserving any potentially leading comments).
        $doctype = str_ireplace(self::HTML_DOCTYPE_HTML_4_SUFFIX, '', $doctype);
        $content = "{$doctype}{$content}";

        return $content;
    }

    /**
     * Normalize the structure of the document if it was already provided as a DOM.
     */
    public function normalizeDomStructure()
    {

        if (! $this->documentElement) {
            $this->appendChild($this->createElement(Tag::HTML));
        } elseif (Tag::HTML !== $this->documentElement->nodeName) {
            $nextSibling = $this->documentElement->nextSibling;
            /**
             * The old document element that we need to remove and replace as we cannot just move it around.
             *
             * @var DOMElement
             */
            $oldDocumentElement = $this->removeChild($this->documentElement);
            $html = $this->createElement(Tag::HTML);
            $this->insertBefore($html, $nextSibling);

            if ($oldDocumentElement->nodeName === Tag::HEAD) {
                $this->head = $oldDocumentElement;
            } else {
                $this->head = $this->getElementsByTagName(Tag::HEAD)->item(0);
                if (! $this->head) {
                    $this->head = $this->createElement(Tag::HEAD);
                }
            }
            $html->appendChild($this->head);

            if ($oldDocumentElement->nodeName === Tag::BODY) {
                $this->body = $oldDocumentElement;
            } else {
                $this->body = $this->getElementsByTagName(Tag::BODY)->item(0);
                if (! $this->body) {
                    $this->body = $this->createElement(Tag::BODY);
                }
            }
            $html->appendChild($this->body);

            if ($oldDocumentElement !== $this->body && $oldDocumentElement !== $this->head) {
                $this->body->appendChild($oldDocumentElement);
            }
        } else {
            $head = $this->getElementsByTagName(Tag::HEAD)->item(0);
            if (! $head) {
                $this->head = $this->createElement(Tag::HEAD);
                $this->documentElement->insertBefore($this->head, $this->documentElement->firstChild);
            }

            $body = $this->getElementsByTagName(Tag::BODY)->item(0);
            if (! $body) {
                $this->body = $this->createElement(Tag::BODY);
                $this->documentElement->appendChild($this->body);
            }
        }

        $this->moveInvalidHeadNodesToBody();
        $this->movePostBodyNodesToBody();
    }

    /**
     * Normalizes HTML attributes to be HTML5 compatible.
     *
     * Conditionally removes html[xmlns], and converts html[xml:lang] to html[lang].
     */
    private function normalizeHtmlAttributes()
    {
        $html = $this->documentElement;
        if (! $html->hasAttributes()) {
            return;
        }

        $xmlns = $html->attributes->getNamedItem('xmlns');
        if ($xmlns instanceof DOMAttr && 'http://www.w3.org/1999/xhtml' === $xmlns->nodeValue) {
            $html->removeAttributeNode($xmlns);
        }

        $xml_lang = $html->attributes->getNamedItem('xml:lang');
        if ($xml_lang instanceof DOMAttr) {
            $lang_node = $html->attributes->getNamedItem('lang');
            if ((! $lang_node || ! $lang_node->nodeValue) && $xml_lang->nodeValue) {
                // Move the html[xml:lang] value to html[lang].
                $html->setAttribute('lang', $xml_lang->nodeValue);
            }
            $html->removeAttributeNode($xml_lang);
        }
    }

    /**
     * Move invalid head nodes back to the body.
     */
    private function moveInvalidHeadNodesToBody()
    {
        // Walking backwards makes it easier to move elements in the expected order.
        $node = $this->head->lastChild;
        while ($node) {
            $nextSibling = $node->previousSibling;
            if (! $this->isValidHeadNode($node)) {
                $this->body->insertBefore($this->head->removeChild($node), $this->body->firstChild);
            }
            $node = $nextSibling;
        }
    }

    /**
     * Converts a possible head[profile] attribute to link[rel=profile].
     *
     * The head[profile] attribute is only valid in HTML4, not HTML5.
     * So if it exists and isn't empty, add it to the <head> as a link[rel=profile] and strip the attribute.
     */
    private function convertHeadProfileToLink()
    {
        if (! $this->head->hasAttribute(Attribute::PROFILE)) {
            return;
        }

        $profile = $this->head->getAttribute(Attribute::PROFILE);
        if ($profile) {
            $link = $this->createElement(Tag::LINK);
            $link->setAttribute(Attribute::REL, Attribute::PROFILE);
            $link->setAttribute(Attribute::HREF, $profile);
            $this->head->appendChild($link);
        }

        $this->head->removeAttribute(Attribute::PROFILE);
    }

    /**
     * Move any nodes appearing after </body> or </html> to be appended to the <body>.
     *
     * This accounts for markup that is output at shutdown, such markup from Query Monitor. Not only is elements after
     * the </body> not valid in AMP, but trailing elements after </html> will get wrapped in additional <html> elements.
     * While comment nodes would be allowed in AMP, everything is moved regardless so that source stack comments will
     * retain their relative position with the element nodes they annotate.
     */
    private function movePostBodyNodesToBody()
    {
        // Move nodes (likely comments) from after the </body>.
        while ($this->body->nextSibling) {
            $this->body->appendChild($this->body->nextSibling);
        }

        // Move nodes from after the </html>.
        while ($this->documentElement->nextSibling) {
            $nextSibling = $this->documentElement->nextSibling;
            if ($nextSibling instanceof DOMElement && Tag::HTML === $nextSibling->nodeName) {
                // Handle trailing elements getting wrapped in implicit duplicate <html>.
                while ($nextSibling->firstChild) {
                    $this->body->appendChild($nextSibling->firstChild);
                }
                $nextSibling->parentNode->removeChild($nextSibling); // Discard now-empty implicit <html>.
            } else {
                $this->body->appendChild($this->documentElement->nextSibling);
            }
        }
    }

    /**
     * Force all self-closing tags to have closing tags.
     *
     * This is needed because DOMDocument isn't fully aware of these.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     * @see restoreSelfClosingTags() Reciprocal function.
     *
     */
    private function replaceSelfClosingTags($html)
    {
        static $regexPattern = null;

        if (null === $regexPattern) {
            $regexPattern = '#<(' . implode('|', Tag::SELF_CLOSING_TAGS) . ')([^>]*?)(?>\s*\/)?>(?!</\1>)#';
        }

        $this->selfClosingTagsTransformed = true;

        return preg_replace($regexPattern, '<$1$2></$1>', $html);
    }

    /**
     * Restore all self-closing tags again.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     * @see replaceSelfClosingTags Reciprocal function.
     *
     */
    private function restoreSelfClosingTags($html)
    {
        static $regexPattern = null;

        if (! $this->selfClosingTagsTransformed) {
            return $html;
        }

        if (null === $regexPattern) {
            $regexPattern = '#</(' . implode('|', Tag::SELF_CLOSING_TAGS) . ')>#i';
        }

        $this->selfClosingTagsTransformed = false;

        return preg_replace($regexPattern, '', $html);
    }

    /**
     * Maybe replace noscript elements with placeholders.
     *
     * This is done because libxml<2.8 might parse them incorrectly.
     * When appearing in the head element, a noscript can cause the head to close prematurely
     * and the noscript gets moved to the body and anything after it which was in the head.
     * See <https://stackoverflow.com/questions/39013102/why-does-noscript-move-into-body-tag-instead-of-head-tag>.
     * This is limited to only running in the head element because this is where the problem lies,
     * and it is important for the AMP_Script_Sanitizer to be able to access the noscript elements
     * in the body otherwise.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     * @see maybeRestoreNoscriptElements() Reciprocal function.
     *
     */
    private function maybeReplaceNoscriptElements($html)
    {
        if (! version_compare(LIBXML_DOTTED_VERSION, '2.8', '<')) {
            return $html;
        }

        return preg_replace_callback(
            '#^.+?(?=<body)#is',
            function ($headMatches) {
                return preg_replace_callback(
                    '#<noscript[^>]*>.*?</noscript>#si',
                    function ($noscriptMatches) {
                        $placeholder = sprintf('<!--noscript:%s-->', (string)$this->rand());

                        $this->noscriptPlaceholderComments[$placeholder] = $noscriptMatches[0];
                        return $placeholder;
                    },
                    $headMatches[0]
                );
            },
            $html
        );
    }

    /**
     * Maybe replace noscript elements with placeholders.
     *
     * This is done because libxml<2.8 might parse them incorrectly.
     * When appearing in the head element, a noscript can cause the head to close prematurely
     * and the noscript gets moved to the body and anything after it which was in the head.
     * See <https://stackoverflow.com/questions/39013102/why-does-noscript-move-into-body-tag-instead-of-head-tag>.
     * This is limited to only running in the head element because this is where the problem lies,
     * and it is important for the AMP_Script_Sanitizer to be able to access the noscript elements
     * in the body otherwise.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     * @see maybeReplaceNoscriptElements() Reciprocal function.
     *
     */
    private function maybeRestoreNoscriptElements($html)
    {
        if (empty($this->noscriptPlaceholderComments)) {
            return $html;
        }

        return str_replace(
            array_keys($this->noscriptPlaceholderComments),
            $this->noscriptPlaceholderComments,
            $html
        );
    }

    /**
     * Secures instances of script[template="amp-mustache"] by renaming element to tmp-script, as a workaround to a libxml parsing issue.
     *
     * This script can have closing tags of its children table and td stripped.
     * So this changes its name from script to tmp-script to avoid this.
     *
     * @link https://github.com/ampproject/amp-wp/issues/4254
     * @see restoreMustacheScriptTemplates() Reciprocal function.
     *
     * @param string $html To replace the tag name that contains the mustache templates.
     * @return string The HTML, with the tag name of the mustache templates replaced.
     */
    private function secureMustacheScriptTemplates($html)
    {
        return preg_replace(
            '#<script(\s[^>]*?template=(["\']?)amp-mustache\2[^>]*)>(.*?)</script\s*?>#is',
            '<tmp-script$1>$3</tmp-script>',
            $html
        );
    }

    /**
     * Restores the tag names of script[template="amp-mustache"] elements that were replaced earlier.
     *
     * @see secureMustacheScriptTemplates() Reciprocal function.
     */
    private function restoreMustacheScriptTemplates()
    {
        $tmp_script_elements = iterator_to_array($this->getElementsByTagName('tmp-script'));
        foreach ($tmp_script_elements as $tmp_script_element) {
            $script = $this->createElement(Tag::SCRIPT);
            foreach ($tmp_script_element->attributes as $attr) {
                $script->setAttribute($attr->nodeName, $attr->nodeValue);
            }
            while ($tmp_script_element->firstChild) {
                $script->appendChild($tmp_script_element->firstChild);
            }
            $tmp_script_element->parentNode->replaceChild($script, $tmp_script_element);
        }
    }

    /**
     * Replace AMP binding attributes with something that libxml can parse (as HTML5 data-* attributes).
     *
     * This is necessary because attributes in square brackets are not understood in PHP and
     * get dropped with an error raised:
     * > Warning: DOMDocument::loadHTML(): error parsing attribute name
     *
     * @link https://www.ampproject.org/docs/reference/components/amp-bind
     *
     * @param string $html HTML containing amp-bind attributes.
     * @return string HTML with AMP binding attributes replaced with HTML5 data-* attributes.
     */
    private function convertAmpBindAttributes($html)
    {
        /**
         * Replace callback.
         *
         * @param array $tagMatches Tag matches.
         * @return string Replacement.
         */
        $replaceCallback = static function ($tagMatches) {

            // Strip the self-closing slash as long as it is not an attribute value, like for the href attribute (<a href=/>).
            $oldAttrs = rtrim(preg_replace('#(?<!=)/$#', '', $tagMatches['attrs']));

            $newAttrs = '';
            $offset   = 0;
            while (preg_match(self::AMP_BIND_ATTR_PATTERN, substr($oldAttrs, $offset), $attrMatches)) {
                $offset += strlen($attrMatches[0]);

                if ('[' === $attrMatches['name'][0]) {
                    $newAttrs .= ' ' . self::AMP_BIND_DATA_ATTR_PREFIX . trim($attrMatches['name'], '[]');
                    if (isset($attrMatches['value'])) {
                        $newAttrs .= $attrMatches['value'];
                    }
                } else {
                    $newAttrs .= $attrMatches[0];
                }
            }

            // Bail on parse error which occurs when the regex isn't able to consume the entire $newAttrs string.
            if (strlen($oldAttrs) !== $offset) {
                return $tagMatches[0];
            }

            return '<' . $tagMatches['name'] . $newAttrs . '>';
        };

        $converted = preg_replace_callback(
            self::AMP_BIND_START_TAGS_PATTERN,
            $replaceCallback,
            $html
        );

        /*
         * If the regex engine incurred an error during processing, for example exceeding the backtrack
         * limit, $converted will be null. In this case we return the originally passed document to allow
         * DOMDocument to attempt to load it.  If the AMP HTML doesn't make use of amp-bind or similar
         * attributes, then everything should still work.
         *
         * See https://github.com/ampproject/amp-wp/issues/993 for additional context on this issue.
         * See http://php.net/manual/en/pcre.constants.php for additional info on PCRE errors.
         */
        return (null !== $converted) ? $converted : $html;
    }

    /**
     * Adapt the encoding of the content.
     *
     * @param string $source Source content to adapt the encoding of.
     * @return string Adapted content.
     */
    private function adaptEncoding($source)
    {
        // No encoding was provided, so we need to guess.
        if (self::UNKNOWN_ENCODING === $this->originalEncoding && function_exists('mb_detect_encoding')) {
            $this->originalEncoding = mb_detect_encoding($source, self::ENCODING_DETECTION_ORDER, true);
        }

        // Guessing the encoding seems to have failed, so we assume UTF-8 instead.
        if (empty($this->originalEncoding)) {
            $this->originalEncoding = self::AMP_ENCODING;
        }

        $this->originalEncoding = $this->sanitizeEncoding($this->originalEncoding);

        $target = false;
        if (self::AMP_ENCODING !== strtolower($this->originalEncoding)) {
            $target = function_exists('mb_convert_encoding')
                ? mb_convert_encoding($source, self::AMP_ENCODING, $this->originalEncoding)
                : false;
        }

        return false !== $target ? $target : $source;
    }

    /**
     * Detect the encoding of the document.
     *
     * @param string      $content  Content of which to detect the encoding.
     * @return array {
     *                              Detected encoding of the document, or false if none.
     *
     * @type string       $content  Potentially modified content.
     * @type string|false $encoding Encoding of the content, or false if not detected.
     * }
     */
    private function detectAndStripEncoding($content)
    {
        $encoding = self::UNKNOWN_ENCODING;

        // Check for HTML 4 http-equiv meta tags.
        foreach ($this->findTags($content, Tag::META, Attribute::HTTP_EQUIV) as $potentialHttpEquivTag) {
            $encoding = $this->extractValue($potentialHttpEquivTag, Attribute::CHARSET);
            if (false !== $encoding) {
                $httpEquivTag = $potentialHttpEquivTag;
            }
        }

        // Strip all charset tags.
        if (isset($httpEquivTag)) {
            $content = str_replace($httpEquivTag, '', $content);
        }

        // Check for HTML 5 charset meta tag. This overrides the HTML 4 charset.
        $charsetTag = $this->findTag($content, Tag::META, Attribute::CHARSET);
        if ($charsetTag) {
            $encoding = $this->extractValue($charsetTag, Attribute::CHARSET);

            // Strip the encoding if it is not the required one.
            if (strtolower($encoding) !== self::AMP_ENCODING) {
                $content = str_replace($charsetTag, '', $content);
            }
        }

        return [$content, $encoding];
    }

    /**
     * Find a given tag with a given attribute.
     *
     * If multiple tags match, this method will only return the first one.
     *
     * @param string $content   Content in which to find the tag.
     * @param string $element   Element of the tag.
     * @param string $attribute Attribute that the tag contains.
     * @return string[] The requested tags. Returns an empty array if none found.
     */
    private function findTags($content, $element, $attribute = null)
    {
        $matches = [];
        $pattern = empty($attribute)
            ? sprintf(
                self::HTML_FIND_TAG_WITHOUT_ATTRIBUTE_PATTERN,
                preg_quote($element, self::HTML_FIND_TAG_DELIMITER)
            )
            : sprintf(
                self::HTML_FIND_TAG_WITH_ATTRIBUTE_PATTERN,
                preg_quote($element, self::HTML_FIND_TAG_DELIMITER),
                preg_quote($attribute, self::HTML_FIND_TAG_DELIMITER)
            );

        if (preg_match($pattern, $content, $matches)) {
            return $matches;
        }

        return [];
    }

    /**
     * Find a given tag with a given attribute.
     *
     * If multiple tags match, this method will only return the first one.
     *
     * @param string $content   Content in which to find the tag.
     * @param string $element   Element of the tag.
     * @param string $attribute Attribute that the tag contains.
     * @return string|false The requested tag, or false if not found.
     */
    private function findTag($content, $element, $attribute = null)
    {
        $matches = $this->findTags($content, $element, $attribute);

        if (empty($matches)) {
            return false;
        }

        return $matches[0];
    }

    /**
     * Extract an attribute value from an HTML tag.
     *
     * @param string $tag       Tag from which to extract the attribute.
     * @param string $attribute Attribute of which to extract the value.
     * @return string|false Extracted attribute value, false if not found.
     */
    private function extractValue($tag, $attribute)
    {
        $matches = [];
        $pattern = sprintf(
            self::HTML_EXTRACT_ATTRIBUTE_VALUE_PATTERN,
            preg_quote($attribute, self::HTML_FIND_TAG_DELIMITER)
        );

        if (preg_match($pattern, $tag, $matches)) {
            return empty($matches['full']) ? $matches['partial'] : $matches['full'];
        }

        return false;
    }

    /**
     * Sanitize the encoding that was detected.
     *
     * If sanitization fails, it will return 'auto', letting the conversion
     * logic try to figure it out itself.
     *
     * @param string $encoding Encoding to sanitize.
     * @return string Sanitized encoding. Falls back to 'auto' on failure.
     */
    private function sanitizeEncoding($encoding)
    {
        if (! function_exists('mb_list_encodings')) {
            return $encoding;
        }

        static $knownEncodings = null;

        if (null === $knownEncodings) {
            $knownEncodings = array_map('strtolower', mb_list_encodings());
        }

        $lcEncoding = strtolower($encoding);
        $encodings  = self::ENCODING_MAP;

        if (isset($encodings[$lcEncoding])) {
            $encoding = $encodings[$lcEncoding];
        }

        if (! in_array($lcEncoding, $knownEncodings, true)) {
            return self::UNKNOWN_ENCODING;
        }

        return $encoding;
    }

    /**
     * Replace Mustache template tokens to safeguard them from turning into HTML entities.
     *
     * Prevents amp-mustache syntax from getting URL-encoded in attributes when saveHTML is done.
     * While this is applying to the entire document, it only really matters inside of <template>
     * elements, since URL-encoding of curly braces in href attributes would not normally matter.
     * But when this is done inside of a <template> then it breaks Mustache. Since Mustache
     * is logic-less and curly braces are not unsafe for HTML, we can do a global replacement.
     * The replacement is done on the entire HTML document instead of just inside of the <template>
     * elements since it is faster and wouldn't change the outcome.
     *
     * @see restoreMustacheTemplateTokens() Reciprocal function.
     */
    private function replaceMustacheTemplateTokens()
    {
        $templates = $this->xpath->query(self::XPATH_MUSTACHE_TEMPLATE_ELEMENTS_QUERY, $this->body);
        if (0 === $templates->length) {
            return;
        }

        $mustacheTagPlaceholders = $this->getMustacheTagPlaceholders();

        foreach ($templates as $template) {
            foreach ($this->xpath->query(self::XPATH_URL_ENCODED_ATTRIBUTES_QUERY, $template) as $attribute) {
                $attribute->nodeValue = str_replace(
                    array_keys($mustacheTagPlaceholders),
                    $mustacheTagPlaceholders,
                    $attribute->nodeValue,
                    $count
                );
                if ($count) {
                    $this->mustacheTagsReplaced = true;
                }
            }
        }
    }

    /**
     * Restore Mustache template tokens that were previously replaced.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     * @see replaceMustacheTemplateTokens() Reciprocal function.
     *
     */
    private function restoreMustacheTemplateTokens($html)
    {
        if (! $this->mustacheTagsReplaced) {
            return $html;
        }

        $mustacheTagPlaceholders = $this->getMustacheTagPlaceholders();

        return str_replace(
            $mustacheTagPlaceholders,
            array_keys($mustacheTagPlaceholders),
            $html
        );
    }

    /**
     * Get amp-mustache tag/placeholder mappings.
     *
     * @return string[] Mapping of mustache tag token to its placeholder.
     * @see \wpdb::placeholder_escape()
     *
     */
    private function getMustacheTagPlaceholders()
    {
        static $placeholders = null;

        if (null === $placeholders) {
            $placeholders = [];
            $salt         = $this->rand();

            // Note: The order of these tokens is important, as it determines the order of the order of the replacements.
            $tokens = [
                '{{{',
                '}}}',
                '{{#',
                '{{^',
                '{{/',
                '{{',
                '}}',
            ];

            foreach ($tokens as $token) {
                $placeholders[$token] = '_amp_mustache_' . md5($salt . $token);
            }
        }

        return $placeholders;
    }

    /**
     * Covert the emoji AMP symbol (⚡) into pure text.
     *
     * The emoji symbol gets stripped by DOMDocument::loadHTML().
     *
     * @param string $source Source HTML string to convert the emoji AMP symbol in.
     * @return string Adapted source HTML string.
     */
    private function convertAmpEmojiAttribute($source)
    {
        $matches            = [];
        $this->usedAmpEmoji = '';

        if (! preg_match(self::AMP_EMOJI_ATTRIBUTE_PATTERN, $source, $matches)) {
            return $source;
        }

        $this->usedAmpEmoji = $matches[2];

        return preg_replace(self::AMP_EMOJI_ATTRIBUTE_PATTERN, '\1' . self::EMOJI_AMP_ATTRIBUTE_PLACEHOLDER . '="\3"', $source, 1);
    }

    /**
     * Restore the emoji AMP symbol (⚡) from its pure text placeholder.
     *
     * @param string $html HTML string to restore the AMP emoji symbol in.
     * @return string Adapted HTML string.
     */
    private function restoreAmpEmojiAttribute($html)
    {
        if (empty($this->usedAmpEmoji)) {
            return $html;
        }

        return preg_replace('/(<html [^>]*?)' . preg_quote(self::EMOJI_AMP_ATTRIBUTE_PLACEHOLDER, '/') . '="([^"]*)"/i', '\1' . $this->usedAmpEmoji . '\2', $html, 1);
    }

    /**
     * Produce a random number to use in hashes.
     *
     * ⚠️ This is not cryptographically secure!
     *
     * @param int $min Lower limit for the generated number
     * @param int $max Upper limit for the generated number
     * @return int A random number between min and max
     */
    private function rand($min = 0, $max = 0)
    {
        if (function_exists('mt_rand')) {
            return mt_rand($min, $max);
        }

        return rand($min, $max);
    }

    /**
     * Secure the original doctype node.
     *
     * We need to keep elements around that were prepended to the doctype, like comment node used for source-tracking.
     * As DOM_Document prepends a new doctype node and removes the old one if the first element is not the doctype, we
     * need to ensure the original one is not stripped (by changing its node type) and restore it later on.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     * @see restoreDoctypeNode() Reciprocal function.
     *
     */
    private function secureDoctypeNode($html)
    {
        return preg_replace(self::HTML_SECURE_DOCTYPE_IF_NOT_FIRST_PATTERN, '\1!--amp-\3\4-->', $html, 1, $this->securedDoctype);
    }

    /**
     * Restore the original doctype node.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     * @see secureDoctypeNode() Reciprocal function.
     *
     */
    private function restoreDoctypeNode($html)
    {
        if (! $this->securedDoctype) {
            return $html;
        }

        return preg_replace(self::HTML_RESTORE_DOCTYPE_PATTERN, '\1!\3\4>', $html, 1);
    }

    /**
     * Process the HTML output string and tweak it as needed.
     *
     * @param string $html HTML output string to tweak.
     * @return string Tweaked HTML output string.
     */
    public function fixSvgSourceAttributeEncoding($html)
    {
        return preg_replace_callback(self::I_AMPHTML_SIZER_REGEX_PATTERN, [$this, 'adaptSizer'], $html);
    }

    /**
     * Adapt the sizer element so that it validates against the AMP spec.
     *
     * @param array $matches Matches that the regular expression collected.
     * @return string Adapted string to use as replacement.
     */
    private function adaptSizer($matches)
    {
        $src = $matches['src'];
        $src = htmlspecialchars_decode($src, ENT_NOQUOTES);
        $src = preg_replace_callback(self::SRC_SVG_REGEX_PATTERN, [$this, 'adaptSvg'], $src);
        return $matches['before_src'] . $src . $matches['after_src'];
    }

    /**
     * Adapt the SVG syntax within the sizer element so that it validates against the AMP spec.
     *
     * @param array $matches Matches that the regular expression collected.
     * @return string Adapted string to use as replacement.
     */
    private function adaptSvg($matches)
    {
        return $matches['type'] . urldecode($matches['value']);
    }

    /**
     * Deduplicate a given tag.
     *
     * This keeps the first tag as the main tag and moves over all child nodes and attribute nodes from any subsequent
     * same tags over to remove them.
     *
     * @param string $tagName Name of the tag to deduplicate.
     */
    public function deduplicateTag($tagName)
    {
        $tags = $this->getElementsByTagName($tagName);

        /**
         * Main tag to keep.
         *
         * @var DOMElement $mainTag
         */
        $mainTag = $tags->item(0);

        if (null === $mainTag) {
            return;
        }

        while ($tags->length > 1) {
            /**
             * Tag to remove.
             *
             * @var DOMElement $tagToRemove
             */
            $tagToRemove = $tags->item(1);

            foreach ($tagToRemove->childNodes as $childNode) {
                $mainTag->appendChild($childNode->parentNode->removeChild($childNode));
            }

            while ($tagToRemove->hasAttributes()) {
                /**
                 * Attribute node to move over to the main tag.
                 *
                 * @var DOMAttr $attribute
                 */
                $attribute = $tagToRemove->attributes->item(0);
                $tagToRemove->removeAttributeNode($attribute);

                // @TODO This doesn't deal properly with attributes present on both tags. Maybe overkill to add?
                // We could move over the copy_attributes from AMP_DOM_Utils to do this.
                $mainTag->setAttributeNode($attribute);
            }

            $tagToRemove->parentNode->removeChild($tagToRemove);
        }

        // Avoid doing the above query again if possible.
        if (in_array($tagName, [Tag::HEAD, Tag::BODY], true)) {
            $this->$tagName = $mainTag;
        }
    }

    /**
     * Determine whether a node can be in the head.
     *
     * @link https://github.com/ampproject/amphtml/blob/445d6e3be8a5063e2738c6f90fdcd57f2b6208be/validator/engine/htmlparser.js#L83-L100
     * @link https://www.w3.org/TR/html5/document-metadata.html
     *
     * @param DOMNode $node Node.
     * @return bool Whether valid head node.
     */
    public function isValidHeadNode(DOMNode $node)
    {
        return (
            ($node instanceof DOMElement && in_array($node->nodeName, Tag::ELEMENTS_ALLOWED_IN_HEAD, true))
            ||
            ($node instanceof DOMText && preg_match('/^\s*$/', $node->nodeValue)) // Whitespace text nodes are OK.
            ||
            $node instanceof DOMComment
        );
    }

    /**
     * Determine whether `data-ampdevmode` was initially set on the document element.
     *
     * @return bool
     */
    public function hasInitialAmpDevMode()
    {
        return $this->hasInitialAmpDevMode;
    }

    /**
     * Magic getter to implement lazily-created, cached properties for the document.
     *
     * @param string $name Name of the property to get.
     * @return mixed Value of the property, or null if unknown property was requested.
     */
    public function __get($name)
    {
        switch ($name) {
            case 'xpath':
                $this->xpath = new DOMXPath($this);
                return $this->xpath;
            case Tag::HTML:
                $this->html = $this->getElementsByTagName(Tag::HTML)->item(0);
                if (null === $this->html) {
                    // Document was assembled manually and bypassed normalisation.
                    $this->normalizeDomStructure();
                    $this->html = $this->getElementsByTagName(Tag::HTML)->item(0);
                }
                return $this->html;
            case Tag::HEAD:
                $this->head = $this->getElementsByTagName(Tag::HEAD)->item(0);
                if (null === $this->head) {
                    // Document was assembled manually and bypassed normalisation.
                    $this->normalizeDomStructure();
                    $this->head = $this->getElementsByTagName(Tag::HEAD)->item(0);
                }
                return $this->head;
            case Tag::BODY:
                $this->body = $this->getElementsByTagName(Tag::BODY)->item(0);
                if (null === $this->body) {
                    // Document was assembled manually and bypassed normalisation.
                    $this->normalizeDomStructure();
                    $this->body = $this->getElementsByTagName(Tag::BODY)->item(0);
                }
                return $this->body;
            case 'ampElements':
                $this->ampElements = $this->xpath->query(".//*[ starts-with( name(), 'amp-' ) ]", $this->body) ?: new DOMNodeList();

                return $this->ampElements;
        }

        // Mimic regular PHP behavior for missing notices.
        trigger_error(self::PROPERTY_GETTER_ERROR_MESSAGE . $name, E_USER_NOTICE); // phpcs:ignore WordPress.PHP.DevelopmentFunctions,WordPress.Security.EscapeOutput
        return null;
    }

    /**
     * Make sure we properly reinitialize on clone.
     *
     * @return void
     */
    public function __clone()
    {
        $this->reset();
    }
}
