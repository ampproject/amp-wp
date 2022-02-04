<?php

namespace AmpProject\Dom;

use AmpProject\DevMode;
use AmpProject\Dom\Document\AfterLoadFilter;
use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Dom\Document\BeforeSaveFilter;
use AmpProject\Dom\Document\Filter;
use AmpProject\Dom\Document\Option;
use AmpProject\Encoding;
use AmpProject\Exception\FailedToRetrieveRequiredDomElement;
use AmpProject\Exception\InvalidDocumentFilter;
use AmpProject\Exception\MaxCssByteCountExceeded;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\Optimizer\CssRule;
use AmpProject\Validator\Spec\CssRuleset\AmpNoTransformed;
use AmpProject\Validator\Spec\SpecRule;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use DOMXPath;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

/**
 * Abstract away some of the difficulties of working with PHP's DOMDocument.
 *
 * @property DOMXPath     $xpath                   XPath query object for this document.
 * @property Element      $html                    The document's <html> element.
 * @property Element      $head                    The document's <head> element.
 * @property Element      $body                    The document's <body> element.
 * @property Element|null $charset                 The document's charset meta element.
 * @property Element|null $viewport                The document's viewport meta element.
 * @property DOMNodeList  $ampElements             The document's <amp-*> elements.
 * @property Element      $ampCustomStyle          The document's <style amp-custom> element.
 * @property int          $ampCustomStyleByteCount Count of bytes of CSS in the <style amp-custom> tag.
 * @property int          $inlineStyleByteCount    Count of bytes of CSS in all of the inline style attributes.
 * @property LinkManager  $links                   Link manager to manage <link> tags in the <head>.
 *
 * @package ampproject/amp-toolbox
 */
final class Document extends DOMDocument
{
    /**
     * Default document type to use.
     *
     * @var string
     */
    const DEFAULT_DOCTYPE = '<!DOCTYPE html>';

    /**
     * Regular expression to match the HTML doctype.
     *
     * @var string
     */
    const HTML_DOCTYPE_REGEX_PATTERN = '#<!doctype\s+html[^>]+?>#si';

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

    // Regex pattern used for removing Internet Explorer conditional comments.
    const HTML_IE_CONDITIONAL_COMMENTS_PATTERN = '/<!--(?>\[if\s|<!\[endif)(?>[^>]+(?<!--)>)*(?>[^>]+(?<=--)>)/i';

    /**
     * Error message to use when the __get() is triggered for an unknown property.
     *
     * @var string
     */
    const PROPERTY_GETTER_ERROR_MESSAGE = 'Undefined property: AmpProject\\Dom\\Document::';

    // Attribute to use as a placeholder to move the emoji AMP symbol (⚡) over to DOM.
    const EMOJI_AMP_ATTRIBUTE_PLACEHOLDER = 'emoji-amp';

    /**
     * XPath query to retrieve all <amp-*> tags, relative to the <body> node.
     *
     * @var string
     */
    const XPATH_AMP_ELEMENTS_QUERY = ".//*[starts-with(name(), 'amp-')]";

    /**
     * XPath query to retrieve the <style amp-custom> tag, relative to the <head> node.
     *
     * @var string
     */
    const XPATH_AMP_CUSTOM_STYLE_QUERY = './/style[@amp-custom]';

    /**
     * XPath query to fetch the inline style attributes, relative to the <body> node.
     *
     * @var string
     */
    const XPATH_INLINE_STYLE_ATTRIBUTES_QUERY = './/@style';

    /**
     * Associative array of options to configure the behavior of the DOM document abstraction.
     *
     * @see Option::DEFAULTS For a list of available options.
     *
     * @var Options
     */
    private $options;

    /**
     * Whether `data-ampdevmode` was initially set on the the document element.
     *
     * @var bool
     */
    private $hasInitialAmpDevMode = false;

    /**
     * The original encoding of how the Dom\Document was created.
     *
     * This is stored to do an automatic conversion to UTF-8, which is a requirement for AMP.
     *
     * @var Encoding
     */
    private $originalEncoding;

    /**
     * The maximum number of bytes of CSS that is enforced.
     *
     * A negative number will disable the byte count limit.
     *
     * @var int
     */
    private $cssMaxByteCountEnforced = -1;

    /**
     * Resource hint manager to manage resource hint <link> tags in the <head>.
     *
     * @var LinkManager|null
     */
    private $links;

    /**
     * List of document filter class names.
     *
     * @var string[]
     */
    private $filterClasses = [];

    /**
     * List of document filter class instances.
     *
     * @var Filter[]
     */
    private $filters = [];

    /**
     * Unique ID manager for the Document instance.
     *
     * @var UniqueIdManager
     */
    private $uniqueIdManager;

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
        $this->originalEncoding = new Encoding($encoding);
        parent::__construct($version ?: '1.0', Encoding::AMP);
        $this->registerNodeClass(DOMElement::class, Element::class);
        $this->options         = new Options(Option::DEFAULTS);
        $this->uniqueIdManager = new UniqueIdManager();

        $this->registerFilters(
            [
                Filter\DetectInvalidByteSequence::class,
                Filter\SvgSourceAttributeEncoding::class,
                Filter\AmpEmojiAttribute::class,
                Filter\AmpBindAttributes::class,
                Filter\SelfClosingTags::class,
                Filter\SelfClosingSVGElements::class,
                Filter\NoscriptElements::class,
                Filter\DeduplicateTag::class,
                Filter\ConvertHeadProfileToLink::class,
                Filter\MustacheScriptTemplates::class,
                Filter\DoctypeNode::class,
                Filter\NormalizeHtmlAttributes::class,
                Filter\DocumentEncoding::class,
                Filter\HttpEquivCharset::class,
                Filter\LibxmlCompatibility::class,
                Filter\ProtectEsiTags::class,
                Filter\NormalizeHtmlEntities::class,
            ]
        );
    }

    /**
     * Named constructor to provide convenient way of transforming HTML into DOM.
     *
     * Due to slow automatic encoding detection, it is recommended to provide an explicit
     * charset either via a <meta charset> tag or via $options.
     *
     * @param string       $html    HTML to turn into a DOM.
     * @param array|string $options Optional. Array of options to configure the document. Used as encoding if a string
     *                              is passed. Defaults to an empty array.
     * @return Document|false DOM generated from provided HTML, or false if the transformation failed.
     */
    public static function fromHtml($html, $options = [])
    {
        // Assume options are the encoding if a string is passed, for BC reasons.
        if (is_string($options)) {
            $options = [Option::ENCODING => $options];
        }

        $encoding = isset($options[Option::ENCODING]) ? $options[Option::ENCODING] : null;

        $dom = new self('', $encoding);

        if (! $dom->loadHTML($html, $options)) {
            return false;
        }

        return $dom;
    }

    /**
     * Named constructor to provide convenient way of transforming a HTML fragment into DOM.
     *
     * The difference to Document::fromHtml() is that fragments are not normalized as to their structure.
     *
     * Due to slow automatic encoding detection, it is recommended to pass in an explicit
     * charset via $options.
     *
     * @param string       $html    HTML to turn into a DOM.
     * @param array|string $options Optional. Array of options to configure the document. Used as encoding if a string
     *                              is passed. Defaults to an empty array.
     * @return Document|false DOM generated from provided HTML, or false if the transformation failed.
     */
    public static function fromHtmlFragment($html, $options = [])
    {
        // Assume options are the encoding if a string is passed, for BC reasons.
        if (is_string($options)) {
            $options = [Option::ENCODING => $options];
        }

        $encoding = isset($options[Option::ENCODING]) ? $options[Option::ENCODING] : null;

        $dom = new self('', $encoding);

        if (! $dom->loadHTMLFragment($html, $options)) {
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
        $node = $dom->importNode($node, true);
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
     * @param string           $source  The HTML string.
     * @param array|int|string $options Optional. Array of options to configure the document. Used as additional Libxml
     *                                  parameters if an int or string is passed. Defaults to an empty array.
     * @return bool true on success or false on failure.
     */
    public function loadHTML($source, $options = [])
    {
        $source  = $this->normalizeDocumentStructure($source);
        $success = $this->loadHTMLFragment($source, $options);

        if ($success) {
            $this->insertMissingCharset();

            // Do some further clean-up.
            $this->moveInvalidHeadNodesToBody();
            $this->movePostBodyNodesToBody();
        }

        return $success;
    }

    /**
     * Load a HTML fragment from a string.
     *
     * @param string           $source  The HTML fragment string.
     * @param array|int|string $options Optional. Array of options to configure the document. Used as additional Libxml
     *                                  parameters if an int or string is passed. Defaults to an empty array.
     * @return bool true on success or false on failure.
     */
    public function loadHTMLFragment($source, $options = [])
    {
        // Assume options are the additional libxml flags if a string or int is passed, for BC reasons.
        if (is_string($options)) {
            $options = (int) $options;
        }
        if (is_int($options)) {
            $options = [Option::LIBXML_FLAGS => $options];
        }

        $this->options = $this->options->merge($options);

        $this->reset();

        foreach ($this->filterClasses as $filterClass) {
            $filter = null;

            try {
                $filter = $this->instantiateFilter($filterClass);
                $this->filters[] = $filter;
            } catch (ReflectionException $exception) {
                // A filter cannot properly be instantiated. Let's just skip loading it for now.
                continue;
            }

            if (! $filter instanceof Filter) {
                throw InvalidDocumentFilter::forFilter($filter);
            }

            if ($filter instanceof BeforeLoadFilter) {
                $source = $filter->beforeLoad($source);
            }
        }

        $success = parent::loadHTML($source, $this->options[Option::LIBXML_FLAGS]);

        if ($success) {
            foreach ($this->filters as $filter) {
                if ($filter instanceof AfterLoadFilter) {
                    $filter->afterLoad($this);
                }
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
     * @param DOMNode|null $node Optional. Parameter to output a subset of the document.
     * @return string The HTML, or false if an error occurred.
     */
    #[\ReturnTypeWillChange]
    public function saveHTML(DOMNode $node = null)
    {
        return $this->saveHTMLFragment($node);
    }

    /**
     * Dumps the internal document fragment into a string using HTML formatting.
     *
     * @param DOMNode|null $node Optional. Parameter to output a subset of the document.
     * @return string The HTML fragment, or false if an error occurred.
     */
    public function saveHTMLFragment(DOMNode $node = null)
    {
        $filtersInReverse = array_reverse($this->filters);

        foreach ($filtersInReverse as $filter) {
            if ($filter instanceof BeforeSaveFilter) {
                $filter->beforeSave($this);
            }
        }

        if (null === $node || PHP_VERSION_ID >= 70300) {
            $html = parent::saveHTML($node);
        } else {
            $html = $this->extractNodeViaFragmentBoundaries($node);
        }

        foreach ($filtersInReverse as $filter) {
            if ($filter instanceof AfterSaveFilter) {
                $html = $filter->afterSave($html);
            }
        }

        return $html;
    }

    /**
     * Get the current options of the Document instance.
     *
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
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
        $charset->setAttribute(Attribute::CHARSET, Encoding::AMP);
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
        $boundary      = $this->uniqueIdManager->getUniqueId('fragment_boundary');
        $startBoundary = $boundary . ':start';
        $endBoundary   = $boundary . ':end';
        $commentStart  = $this->createComment($startBoundary);
        $commentEnd    = $this->createComment($endBoundary);

        $node->parentNode->insertBefore($commentStart, $node);
        $node->parentNode->insertBefore($commentEnd, $node->nextSibling);

        $pattern = '/^.*?'
                   . preg_quote("<!--{$startBoundary}-->", '/')
                   . '(.*)'
                   . preg_quote("<!--{$endBoundary}-->", '/')
                   . '.*?\s*$/s';

        $html = preg_replace($pattern, '$1', parent::saveHTML());

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
        $doctype = preg_replace(self::HTML_DOCTYPE_REGEX_PATTERN, self::DEFAULT_DOCTYPE, $doctype);
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
        }

        if (Tag::HTML !== $this->documentElement->nodeName) {
            $nextSibling = $this->documentElement->nextSibling;
            /**
             * The old document element that we need to remove and replace as we cannot just move it around.
             *
             * @var Element
             */
            $oldDocumentElement = $this->removeChild($this->documentElement);
            $html = $this->createElement(Tag::HTML);
            $this->insertBefore($html, $nextSibling);

            if ($oldDocumentElement->nodeName === Tag::HEAD) {
                $head = $oldDocumentElement;
            } else {
                $head = $this->getElementsByTagName(Tag::HEAD)->item(0);
                if (!$head) {
                    $head = $this->createElement(Tag::HEAD);
                }
            }

            if (!$head instanceof Element) {
                throw FailedToRetrieveRequiredDomElement::forHeadElement($head);
            }

            $this->head = $head;
            $html->appendChild($this->head);

            if ($oldDocumentElement->nodeName === Tag::BODY) {
                $body = $oldDocumentElement;
            } else {
                $body = $this->getElementsByTagName(Tag::BODY)->item(0);
                if (!$body) {
                    $body = $this->createElement(Tag::BODY);
                }
            }

            if (!$body instanceof Element) {
                throw FailedToRetrieveRequiredDomElement::forBodyElement($body);
            }

            $this->body = $body;
            $html->appendChild($this->body);

            if ($oldDocumentElement !== $this->body && $oldDocumentElement !== $this->head) {
                $this->body->appendChild($oldDocumentElement);
            }
        } else {
            $head = $this->getElementsByTagName(Tag::HEAD)->item(0);
            if (!$head) {
                $this->head = $this->createElement(Tag::HEAD);
                $this->documentElement->insertBefore($this->head, $this->documentElement->firstChild);
            }

            $body = $this->getElementsByTagName(Tag::BODY)->item(0);
            if (!$body) {
                $this->body = $this->createElement(Tag::BODY);
                $this->documentElement->appendChild($this->body);
            }
        }

        $this->moveInvalidHeadNodesToBody();
        $this->movePostBodyNodesToBody();
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
            if ($nextSibling instanceof Element && Tag::HTML === $nextSibling->nodeName) {
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
            ($node instanceof Element && in_array($node->nodeName, Tag::ELEMENTS_ALLOWED_IN_HEAD, true))
            ||
            ($node instanceof DOMText && preg_match('/^\s*$/', $node->nodeValue)) // Whitespace text nodes are OK.
            ||
            $node instanceof DOMComment
        );
    }

    /**
     * Get the ID for an element.
     *
     * If the element does not have an ID, create one first.
     *
     * @param Element $element Element to get the ID for.
     * @param string  $prefix  Optional. The prefix to use (should not have a trailing dash). Defaults to 'i-amp-id'.
     * @return string ID to use.
     */
    public function getElementId(Element $element, $prefix = 'i-amp')
    {
        if ($element->hasAttribute(Attribute::ID)) {
            return $element->getAttribute(Attribute::ID);
        }

        $id = $this->uniqueIdManager->getUniqueId($prefix);
        while ($this->getElementById($id) instanceof Element) {
            $id = $this->uniqueIdManager->getUniqueId($prefix);
        }

        $element->setAttribute(Attribute::ID, $id);

        return $id;
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
     * Add style(s) to the <style amp-custom> tag.
     *
     * @param string $style Style to add.
     * @throws MaxCssByteCountExceeded If the allowed max byte count is exceeded.
     */
    public function addAmpCustomStyle($style)
    {
        $style         = trim($style, CssRule::CSS_TRIM_CHARACTERS);
        $existingStyle = (string)$this->ampCustomStyle->textContent;

        // Inject new styles before any potential source map annotation comment like: /*# sourceURL=amp-custom.css */.
        // If not present, then just put it at the end of the stylesheet. This isn't strictly required, but putting the
        // source map comments at the end is the convention.
        $newStyle = preg_replace(
            ':(?=\s+/\*#[^*]+?\*/\s*$|$):s',
            $style,
            $existingStyle,
            1
        );

        $newByteCount = strlen($newStyle);

        if ($this->getRemainingCustomCssSpace() < ($newByteCount - $this->ampCustomStyleByteCount)) {
            throw MaxCssByteCountExceeded::forAmpCustom($newStyle);
        }

        $this->ampCustomStyle->textContent = $newStyle;
        $this->ampCustomStyleByteCount     = $newByteCount;
    }

    /**
     * Add the given number of bytes ot the total inline style byte count.
     *
     * @param int $byteCount Bytes to add.
     */
    public function addInlineStyleByteCount($byteCount)
    {
        $this->inlineStyleByteCount += $byteCount;
    }

    /**
     * Get the remaining number bytes allowed for custom CSS.
     *
     * @return int
     */
    public function getRemainingCustomCssSpace()
    {
        if ($this->cssMaxByteCountEnforced < 0) {
            // No CSS byte count limit is being enforced, so return the next best thing to +∞.
            return PHP_INT_MAX;
        }

        return max(
            0,
            $this->cssMaxByteCountEnforced - (int)$this->ampCustomStyleByteCount - (int)$this->inlineStyleByteCount
        );
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
                $html = $this->getElementsByTagName(Tag::HTML)->item(0);

                if ($html === null) {
                    // Document was assembled manually and bypassed normalisation.
                    $this->normalizeDomStructure();
                    $html = $this->getElementsByTagName(Tag::HTML)->item(0);
                }

                if (!$html instanceof Element) {
                    throw FailedToRetrieveRequiredDomElement::forHtmlElement($html);
                }

                $this->html = $html;
                return $this->html;
            case Tag::HEAD:
                $head = $this->getElementsByTagName(Tag::HEAD)->item(0);

                if ($head === null) {
                    // Document was assembled manually and bypassed normalisation.
                    $this->normalizeDomStructure();
                    $head = $this->getElementsByTagName(Tag::HEAD)->item(0);
                }

                if (!$head instanceof Element) {
                    throw FailedToRetrieveRequiredDomElement::forHeadElement($head);
                }

                $this->head = $head;
                return $this->head;
            case Tag::BODY:
                $body = $this->getElementsByTagName(Tag::BODY)->item(0);

                if ($body === null) {
                    // Document was assembled manually and bypassed normalisation.
                    $this->normalizeDomStructure();
                    $body = $this->getElementsByTagName(Tag::BODY)->item(0);
                }

                if (!$body instanceof Element) {
                    throw FailedToRetrieveRequiredDomElement::forBodyElement($body);
                }

                $this->body = $body;
                return $this->body;
            case Attribute::CHARSET:
                // This is not cached as it could potentially be requested too early, before the viewport was added, and
                // the cache would then store null without rechecking later on after the viewport has been added.
                for ($node = $this->head->firstChild; $node !== null; $node = $node->nextSibling) {
                    if (
                        $node instanceof Element
                        && $node->tagName === Tag::META
                        && $node->getAttribute(Attribute::NAME) === Attribute::CHARSET
                    ) {
                        return $node;
                    }
                }
                return null;
            case Attribute::VIEWPORT:
                // This is not cached as it could potentially be requested too early, before the viewport was added, and
                // the cache would then store null without rechecking later on after the viewport has been added.
                for ($node = $this->head->firstChild; $node !== null; $node = $node->nextSibling) {
                    if (
                        $node instanceof Element
                        && $node->tagName === Tag::META
                        && $node->getAttribute(Attribute::NAME) === Attribute::VIEWPORT
                    ) {
                        return $node;
                    }
                }
                return null;
            case 'ampElements':
                // This is not cached as we clone some elements during SSR transformations to avoid ending up with
                // partially transformed, broken elements.
                return $this->xpath->query(self::XPATH_AMP_ELEMENTS_QUERY, $this->body)
                    ?: new DOMNodeList();

            case 'ampCustomStyle':
                $ampCustomStyle = $this->xpath->query(self::XPATH_AMP_CUSTOM_STYLE_QUERY, $this->head)->item(0);
                if (!$ampCustomStyle instanceof Element) {
                    $ampCustomStyle = $this->createElement(Tag::STYLE);
                    $ampCustomStyle->appendChild($this->createAttribute(Attribute::AMP_CUSTOM));
                    $this->head->appendChild($ampCustomStyle);
                }

                $this->ampCustomStyle = $ampCustomStyle;

                return $this->ampCustomStyle;

            case 'ampCustomStyleByteCount':
                if (!isset($this->ampCustomStyle)) {
                    $ampCustomStyle = $this->xpath->query(self::XPATH_AMP_CUSTOM_STYLE_QUERY, $this->head)->item(0);
                    if (!$ampCustomStyle instanceof Element) {
                        return 0;
                    } else {
                        $this->ampCustomStyle = $ampCustomStyle;
                    }
                }

                if (!isset($this->ampCustomStyleByteCount)) {
                    $this->ampCustomStyleByteCount = strlen($this->ampCustomStyle->textContent);
                }

                return $this->ampCustomStyleByteCount;

            case 'inlineStyleByteCount':
                if (!isset($this->inlineStyleByteCount)) {
                    $this->inlineStyleByteCount = 0;
                    $attributes = $this->xpath->query(
                        self::XPATH_INLINE_STYLE_ATTRIBUTES_QUERY,
                        $this->documentElement
                    );
                    foreach ($attributes as $attribute) {
                        $this->inlineStyleByteCount += strlen($attribute->textContent);
                    }
                }

                return $this->inlineStyleByteCount;

            case 'links':
                if (! isset($this->links)) {
                    $this->links = new LinkManager($this);
                }

                return $this->links;
        }

        // Mimic regular PHP behavior for missing notices.
        trigger_error(self::PROPERTY_GETTER_ERROR_MESSAGE . $name, E_USER_NOTICE);
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

    /**
     * Create new element node.
     *
     * @link https://php.net/manual/domdocument.createelement.php
     *
     * This override only serves to provide the correct object type-hint for our extended Dom/Element class.
     *
     * @param string $name  The tag name of the element.
     * @param string $value Optional. The value of the element. By default, an empty element will be created.
     *                      You can also set the value later with Element->nodeValue.
     * @return Element|false A new instance of class Element or false if an error occurred.
     */
    public function createElement($name, $value = '')
    {
        $element = parent::createElement($name, $value);

        if (!$element instanceof Element) {
            return false;
        }

        return $element;
    }

    /**
     * Create new element node.
     *
     * @link https://php.net/manual/domdocument.createelement.php
     *
     * This override only serves to provide the correct object type-hint for our extended Dom/Element class.
     *
     * @param string $name       The tag name of the element.
     * @param array  $attributes Attributes to add to the newly created element.
     * @param string $value      Optional. The value of the element. By default, an empty element will be created.
     *                           You can also set the value later with Element->nodeValue.
     * @return Element|false A new instance of class Element or false if an error occurred.
     */
    public function createElementWithAttributes($name, $attributes, $value = '')
    {
        $element = parent::createElement($name, $value);

        if (!$element instanceof Element) {
            return false;
        }

        $element->setAttributes($attributes);

        return $element;
    }

    /**
     * Check whether the CSS maximum byte count is enforced.
     *
     * @return bool Whether the CSS maximum byte count is enforced.
     */
    public function isCssMaxByteCountEnforced()
    {
        return $this->cssMaxByteCountEnforced >= 0;
    }

    /**
     * Enforce a maximum number of bytes for the CSS.
     *
     * @param int|null $maxByteCount Maximum number of bytes to limit the CSS to. A negative number disables the limit.
     *                               If null then the max bytes from AmpNoTransformed is used.
     */
    public function enforceCssMaxByteCount($maxByteCount = null)
    {
        if ($maxByteCount === null) {
            // No need to instantiate the spec here, we can just directly reference the needed constant.
            $maxByteCount = AmpNoTransformed::SPEC[SpecRule::MAX_BYTES];
        }

        $this->cssMaxByteCountEnforced = $maxByteCount;
    }

    /**
     * Register filters to pre- or post-process the document content.
     *
     * @param string[] $filterClasses Array of FQCNs of document filter classes.
     */
    public function registerFilters($filterClasses)
    {
        foreach ($filterClasses as $filterClass) {
            $this->filterClasses[] = $filterClass;
        }
    }

    /**
     * Instantiate a filter from its class while providing the needed dependencies.
     *
     * @param string $filterClass Class of the filter to instantiate.
     * @return Filter Filter object instance.
     * @throws ReflectionException If the constructor could not be reflected upon.
     */
    private function instantiateFilter($filterClass)
    {
        $constructor  = (new ReflectionClass($filterClass))->getConstructor();
        $parameters   = $constructor === null ? [] : $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencyType = null;

            // The use of `ReflectionParameter::getClass()` is deprecated in PHP 8, and is superseded
            // by `ReflectionParameter::getType()`. See https://github.com/php/php-src/pull/5209.
            if (PHP_VERSION_ID >= 70100) {
                if ($parameter->getType()) {
                    /** @var ReflectionNamedType $returnType */
                    $returnType = $parameter->getType();
                    $dependencyType = new ReflectionClass($returnType->getName());
                }
            } else {
                $dependencyType = $parameter->getClass();
            }

            if ($dependencyType === null) {
                // No type provided, so we pass `null` in the hopes that the argument is optional.
                $dependencies[] = null;
                continue;
            }

            if (is_a($dependencyType->name, Encoding::class, true)) {
                $dependencies[] = $this->originalEncoding;
                continue;
            }

            if (is_a($dependencyType->name, Options::class, true)) {
                $dependencies[] = $this->options;
                continue;
            }

            if (is_a($dependencyType->name, UniqueIdManager::class, true)) {
                $dependencies[] = $this->uniqueIdManager;
                continue;
            }

            // Unknown dependency type, so we pass `null` in the hopes that the argument is optional.
            $dependencies[] = null;
        }

        return new $filterClass(...$dependencies);
    }
}
