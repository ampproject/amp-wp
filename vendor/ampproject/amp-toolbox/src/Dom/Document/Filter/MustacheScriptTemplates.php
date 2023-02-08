<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document;
use AmpProject\Dom\Document\AfterLoadFilter;
use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Dom\Document\BeforeSaveFilter;
use AmpProject\Html\Tag;

/**
 * Filter to handle the script[template="amp-mustache"].
 *
 * @package ampproject/amp-toolbox
 */
final class MustacheScriptTemplates implements BeforeLoadFilter, AfterLoadFilter, BeforeSaveFilter, AfterSaveFilter
{
    /**
     * Xpath query to fetch the elements containing Mustache templates (both <template type=amp-mustache> and
     * <script type=text/plain template=amp-mustache>).
     *
     * @var string
     */
    const XPATH_MUSTACHE_TEMPLATE_ELEMENTS_QUERY = './/self::template[ @type = "amp-mustache" ]'
                                                   . '|//self::script[ @type = "text/plain" '
                                                   . 'and @template = "amp-mustache" ]';
    /**
     * Xpath query to fetch the attributes that are being URL-encoded by saveHTML().
     *
     * @var string
     */
    const XPATH_URL_ENCODED_ATTRIBUTES_QUERY = './/*/@src|.//*/@href|.//*/@action';

    /**
     * Store whether mustache template tags were replaced and need to be restored.
     *
     * @see replaceMustacheTemplateTokens()
     *
     * @var bool
     */
    private $mustacheTagsReplaced = false;

    /**
     * Secures instances of script[template="amp-mustache"] by renaming element to tmp-script, as a workaround to a
     * libxml parsing issue.
     *
     * This script can have closing tags of its children table and td stripped.
     * So this changes its name from script to tmp-script to avoid this.
     *
     * @link https://github.com/ampproject/amp-wp/issues/4254
     *
     * @param string $html To replace the tag name that contains the mustache templates.
     * @return string The HTML, with the tag name of the mustache templates replaced.
     */
    public function beforeLoad($html)
    {
        $result = preg_replace(
            '#<script(\s[^>]*?template=(["\']?)amp-mustache\2[^>]*)>(.*?)</script\s*?>#is',
            '<tmp-script$1>$3</tmp-script>',
            $html
        );

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Restores the tag names of script[template="amp-mustache"] elements that were replaced earlier.
     *
     * @param Document $document Document to be processed.
     */
    public function afterLoad(Document $document)
    {
        $tmp_script_elements = iterator_to_array($document->getElementsByTagName('tmp-script'));
        foreach ($tmp_script_elements as $tmp_script_element) {
            $script = $document->createElement(Tag::SCRIPT);
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
     * @param Document $document Document to be processed.
     */
    public function beforeSave(Document $document)
    {
        $templates = $document->xpath->query(self::XPATH_MUSTACHE_TEMPLATE_ELEMENTS_QUERY, $document->body);
        if (0 === $templates->length) {
            return;
        }

        $mustacheTagPlaceholders = $this->getMustacheTagPlaceholders();

        foreach ($templates as $template) {
            foreach ($document->xpath->query(self::XPATH_URL_ENCODED_ATTRIBUTES_QUERY, $template) as $attribute) {
                $value = preg_replace_callback(
                    $this->getMustacheTagPattern(),
                    static function ($matches) use ($mustacheTagPlaceholders) {
                        return $mustacheTagPlaceholders[trim($matches[0])];
                    },
                    $attribute->nodeValue,
                    -1,
                    $count
                );

                if ($count) {
                    // Note we cannot do `$attribute->nodeValue = $value` because the PHP DOM will try to parse any
                    // entities. In the case of a URL value like '/foo/?bar=1&baz=2' the result is a warning for an
                    // unterminated entity reference "baz". When the attribute value is updated via setAttribute() this
                    // same problem does not occur, so that is why the following is used.
                    $attribute->parentNode->setAttribute($attribute->nodeName, $value);

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
     */
    public function afterSave($html)
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
     */
    private function getMustacheTagPlaceholders()
    {
        static $placeholders = null;

        if (null === $placeholders) {
            $placeholders = [];

            // Note: The order of these tokens is important, as it determines the order of the replacements.
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
                $placeholders[$token] = '_amp_mustache_' . md5(uniqid($token));
            }
        }

        return $placeholders;
    }

    /**
     * Get a regular expression that matches all amp-mustache tags while consuming whitespace.
     *
     * Removing whitespace is needed to avoid DOMDocument turning whitespace into entities, like %20 for spaces.
     *
     * @return string Regex pattern to match amp-mustache tags with whitespace.
     */
    private function getMustacheTagPattern()
    {
        static $tagPattern = null;

        if (null === $tagPattern) {
            $delimiter  = ':';
            $tags       = [];

            foreach (array_keys($this->getMustacheTagPlaceholders()) as $token) {
                if ('{' === $token[0]) {
                    $tags[] = preg_quote($token, $delimiter) . '\s*';
                } else {
                    $tags[] = '\s*' . preg_quote($token, $delimiter);
                }
            }

            $tagPattern = $delimiter . implode('|', $tags) . $delimiter;
        }

        return $tagPattern;
    }
}
