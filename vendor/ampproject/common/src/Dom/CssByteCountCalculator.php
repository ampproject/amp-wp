<?php

namespace AmpProject\Dom;

use DOMNodeList;

/**
 * Class AmpProject\Dom\CssByteCountCalculator.
 *
 * Calculates the total byte count of CSS styles in a given document.
 *
 * This can be used to check against the allowed limit of 75kB that AMP enforces.
 *
 * @package ampproject/common
 */
final class CssByteCountCalculator
{

    /**
     * XPath query to fetch the <style amp-custom> tag, relative to the <head> node.
     *
     * @var string
     */
    const AMP_CUSTOM_STYLE_TAG_XPATH = './/style[@amp-custom]';

    /**
     * XPath query to fetch the inline style attributes, relative to the <body> node.
     *
     * @var string
     */
    const INLINE_STYLE_ATTRIBUTES_XPATH = './/@style';

    /**
     * Document to calculate the total byte count for.
     *
     * @var Document
     */
    private $document;

    /**
     * Instantiate a CssByteCountCalculator object.
     *
     * @param Document $document Document to calculate the total byte count for.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Calculate the byte count for the provided document.
     *
     * @return int Total byte count of CSS styles in the document.
     */
    public function calculate()
    {
        $ampCustomStyle = $this->document->xpath->query(self::AMP_CUSTOM_STYLE_TAG_XPATH, $this->document->head);
        $inlineStyles   = $this->document->xpath->query(self::INLINE_STYLE_ATTRIBUTES_XPATH, $this->document->body);

        return $this->calculateForNodeList($ampCustomStyle) + $this->calculateForNodeList($inlineStyles);
    }

    /**
     * Calculate the byte count of CSS styles for a given node list.
     *
     * @param DOMNodeList $nodeList Node list to calculate the byte count of CSS styles for.
     * @return int Byte count of CSS styles for the given node list.
     */
    private function calculateForNodeList(DOMNodeList $nodeList)
    {
        $byteCount = 0;

        foreach ($nodeList as $node) {
            $byteCount += strlen($node->textContent);
        }

        return $byteCount;
    }
}
