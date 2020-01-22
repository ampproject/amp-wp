<?php

namespace Amp;

use DOMElement;
use DOMNode;

final class Extension
{

    /**
     * Prefix of an Amp extension.
     *
     * @var string.
     */
    const PREFIX = 'amp-';

    /**
     * Tag name for custom elements.
     *
     * @var string
     */
    const CUSTOM_ELEMENT_TAG_NAME = 'custom-element';

    // List of Amp extensions.
    const DYNAMIC_CSS_CLASSES = 'amp-dynamic-css-classes';
    const EXPERIMENT          = 'amp-experiment';
    const GEO                 = 'amp-geo';
    const STORY               = 'amp-story';

    /**
     * Array of custom element names that delay rendering.
     *
     * @var string[]
     */
    const RENDER_DELAYING_EXTENSIONS = [
        Extension::DYNAMIC_CSS_CLASSES,
        Extension::EXPERIMENT,
        Extension::STORY,
    ];

    /**
     * Check whether a given element is a script for a render-delaying extension.
     *
     * @param DOMElement $element Element to check.
     * @return bool Whether the element is a script for a render-delaying extension.
     */
    public static function isRenderDelayingExtension(DOMElement $element)
    {
        if ($element->tagName !== Tag::SCRIPT) {
            return false;
        }

        if (! $element->hasAttribute(self::CUSTOM_ELEMENT_TAG_NAME)) {
            return false;
        }

        $customElement = $element->getAttribute(self::CUSTOM_ELEMENT_TAG_NAME);

        return in_array($customElement, self::RENDER_DELAYING_EXTENSIONS, true);
    }

    /**
     * Check whether a given DOM node is an Amp custom element.
     *
     * @param DOMNode $node DOM node to check.
     * @return bool Whether the checked DOM node is an Amp custom element.
     */
    public static function isCustomElement(DOMNode $node)
    {
        return $node instanceof DOMElement && strpos($node->tagName, self::PREFIX) === 0;
    }
}
