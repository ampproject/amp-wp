<?php

namespace AmpProject\Dom;

use DOMAttr;

/**
 * Dump an element with its attributes.
 *
 * This is meant for quick identification of an element and does not dump the child element or other inner content
 * from that element.
 *
 * @package ampproject/amp-toolbox
 */
final class ElementDump
{

    /**
     * Element to dump.
     *
     * @var Element
     */
    private $element;

    /**
     * Maximum length to truncate attributes and textContent to.
     *
     * Defaults to 120.
     *
     * @var int
     */
    private $truncate;

    /**
     * Instantiate an ElementDump object.
     *
     * The object is meant to be cast to a string to do its magic.
     *
     * @param Element $element  Element to dump.
     * @param int     $truncate Optional. Maximum length to truncate attributes and textContent to. Defaults to 120.
     */
    public function __construct(Element $element, $truncate = 120)
    {
        $this->element  = $element;
        $this->truncate = $truncate;
    }

    /**
     * Dump the provided element into a string.
     *
     * @return string Dump of the element.
     */
    public function __toString()
    {
        $attributes = $this->maybeTruncate(
            array_reduce(
                iterator_to_array($this->element->attributes, true),
                static function ($text, DOMAttr $attribute) {
                    return $text . " {$attribute->nodeName}=\"{$attribute->value}\"";
                },
                ''
            )
        );

        $textContent = $this->maybeTruncate($this->element->textContent);

        return sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $this->element->tagName,
            $attributes,
            $textContent
        );
    }

    /**
     * Truncate the provided text if needed.
     *
     * @param string $text Text to truncate.
     * @return string Potentially truncated text.
     */
    private function maybeTruncate($text)
    {
        if ($this->truncate <= 0) {
            return $text;
        }

        // Not checking for both mb_* functions, as we assume that if one mb_* function exists, the extension is
        // available and all mb_* functions will exist.
        if (function_exists('mb_strlen')) {
            if (mb_strlen($text) > $this->truncate) {
                return mb_substr($text, 0, $this->truncate - 1) . '…';
            }
        } elseif (strlen($text) > $this->truncate) {
            // Fall back to regular string functions, knowing that this might potentially miscalculate and/or split
            // multi-byte chars. We do so as we assume a site with special character encoding needs will have the
            // multi-byte extension loaded anyway.
            return substr($text, 0, $this->truncate - 1) . '…';
        }

        return $text;
    }
}
