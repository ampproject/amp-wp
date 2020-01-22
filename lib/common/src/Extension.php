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

    // List of Amp extensions.
    const DYNAMIC_CSS_CLASSES = 'amp-dynamic-css-classes';
    const EXPERIMENT          = 'amp-experiment';
    const GEO                 = 'amp-geo';
    const STORY               = 'amp-story';

    // Custom element/template attributes.
    const CUSTOM_ELEMENT  = 'custom-element';
    const CUSTOM_TEMPLATE = 'custom-template';

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
     * List of dynamic components
     *
     * This list should be kept in sync with the list of dynamic components at:
     *
     * @see https://github.com/ampproject/amphtml/blob/master/spec/amp-cache-guidelines.md#guidelines-adding-a-new-cache-to-the-amp-ecosystem
     *
     * @var array[]
     */
    const DYNAMIC_COMPONENTS = [
        Extension::CUSTOM_ELEMENT  => [Extension::GEO],
        Extension::CUSTOM_TEMPLATE => [],
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

        if (! $element->hasAttribute(self::CUSTOM_ELEMENT)) {
            return false;
        }

        $customElement = $element->getAttribute(self::CUSTOM_ELEMENT);

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
