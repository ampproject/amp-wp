<?php

namespace AmpProject\Optimizer\Error;

use DOMAttr;
use DOMElement;

/**
 * Dump an element with its attributes.
 *
 * This is meant for quick identification of an element and does not dump the child element or other inner content
 * from that element.
 */
final class ElementDump
{

    /**
     * Element to dump.
     *
     * @var DOMElement
     */
    private $element;

    /**
     *
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
     * @param DOMElement $element  Element to dump.
     * @param int        $truncate Optional. Maximum length of the dumped string to truncate to. Defaults to 120.
     */
    public function __construct(DOMElement $element, $truncate = 120)
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
        static $dump = null;

        if ($dump === null) {
            $dump = sprintf(
                '<%s%s',
                $this->element->tagName,
                array_reduce(
                    iterator_to_array($this->element->attributes, true),
                    static function ($text, DOMAttr $attribute) {
                        return $text . " {$attribute->nodeName}=\"{$attribute->value}\"";
                    },
                    ''
                )
            );

            if (mb_strlen($dump) > $this->truncate) {
                $dump = mb_substr($dump, 0, $this->truncate - 1) . '…';
            }
            $dump .= '>';
        }

        return (string)$dump;
    }
}
