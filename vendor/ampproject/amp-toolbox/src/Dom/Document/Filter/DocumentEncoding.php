<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Html\Attribute;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Encoding;
use AmpProject\Html\Tag;

/**
 * Filter for document encoding.
 *
 * @package ampproject/amp-toolbox
 */
final class DocumentEncoding implements BeforeLoadFilter
{
    /**
     * Regex pattern to find a tag without an attribute.
     *
     * @var string
     */
    const HTML_FIND_TAG_WITHOUT_ATTRIBUTE_PATTERN = '/<%1$s[^>]*?>[^<]*(?><\/%1$s>)?/i';

    /**
     * Regex pattern to find a tag with an attribute.
     *
     * @var string
     */
    const HTML_FIND_TAG_WITH_ATTRIBUTE_PATTERN = '/<%1$s [^>]*?\s*%2$s\s*=[^>]*?>[^<]*(?><\/%1$s>)?/i';

    /**
     * Regex pattern to extract an attribute value out of an attribute string.
     *
     * @var string
     */
    const HTML_EXTRACT_ATTRIBUTE_VALUE_PATTERN = '/%s=(?>([\'"])(?<full>.*)?\1|(?<partial>[^ \'";]+))/';

    /**
     * Delimiter used in regular expressions.
     *
     * @var string
     */
    const HTML_FIND_TAG_DELIMITER = '/';

    /**
     * Original encoding that was used for the document.
     *
     * @var Encoding
     */
    private $originalEncoding;

    /**
     * DocumentEncoding constructor.
     *
     * @param Encoding $originalEncoding Original encoding that was used for the document.
     */
    public function __construct(Encoding $originalEncoding)
    {
        $this->originalEncoding = $originalEncoding;
    }

    /**
     * Detect the encoding of the document.
     *
     * @param string $html Content of which to detect the encoding.
     * @return string Preprocessed string of HTML markup.
     */
    public function beforeLoad($html)
    {
        $encoding = (string) $this->originalEncoding;

        // Check for HTML 4 http-equiv meta tags.
        foreach ($this->findTags($html, Tag::META, Attribute::HTTP_EQUIV) as $potentialHttpEquivTag) {
            $encoding = $this->extractValue($potentialHttpEquivTag, Attribute::CHARSET);
            if (false !== $encoding) {
                $httpEquivTag = $potentialHttpEquivTag;
            }
        }

        // Strip all charset tags.
        if (isset($httpEquivTag)) {
            $html = str_replace($httpEquivTag, '', $html);
        }

        // Check for HTML 5 charset meta tag. This overrides the HTML 4 charset.
        $charsetTag = $this->findTag($html, Tag::META, Attribute::CHARSET);
        if ($charsetTag) {
            $encoding = $this->extractValue($charsetTag, Attribute::CHARSET);

            // Strip the encoding if it is not the required one.
            if (strtolower($encoding) !== Encoding::AMP) {
                $html = str_replace($charsetTag, '', $html);
            }
        }

        $this->originalEncoding = new Encoding($encoding);

        if (! $this->originalEncoding->equals(Encoding::AMP)) {
            $html = $this->adaptEncoding($html);
        }

        return $html;
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
        if (function_exists('mb_detect_encoding') && $this->originalEncoding->equals(Encoding::UNKNOWN)) {
            $this->originalEncoding = new Encoding($this->detectEncoding($source));
        }

        // Guessing the encoding seems to have failed, so we assume UTF-8 instead.
        // In my testing, this was not possible as long as one ISO-8859-x is in the detection order.
        if ($this->originalEncoding === null) {
            $this->originalEncoding = new Encoding(Encoding::AMP); // @codeCoverageIgnore
        }

        $this->originalEncoding->sanitize();

        // Sanitization failed, so we do a last effort to auto-detect.
        if (function_exists('mb_detect_encoding') && $this->originalEncoding->equals(Encoding::UNKNOWN)) {
            $detectedEncoding = $this->detectEncoding($source);
            if ($detectedEncoding !== false) {
                $this->originalEncoding = new Encoding($detectedEncoding);
            }
        }

        $target = false;
        if (! $this->originalEncoding->equals(Encoding::AMP)) {
            $target = function_exists('mb_convert_encoding')
                ? mb_convert_encoding($source, Encoding::AMP, (string) $this->originalEncoding)
                : false;
        }

        return false !== $target ? $target : $source;
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
     * Detect character encoding.
     *
     * @param string $source Source content to detect the encoding of.
     * @return string The character encoding of the source.
     */
    private function detectEncoding($source)
    {
        $detectionOrder = PHP_VERSION_ID >= 80100 ? Encoding::DETECTION_ORDER_PHP81 : Encoding::DETECTION_ORDER;
        return mb_detect_encoding($source, $detectionOrder, true);
    }
}
