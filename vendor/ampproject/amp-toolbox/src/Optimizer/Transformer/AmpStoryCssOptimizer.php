<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Dom\NodeWalker;
use AmpProject\Extension;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\Optimizer\Configuration\AmpStoryCssOptimizerConfiguration;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;

/**
 * AmpStoryCssOptimizer - CSS Optimizer for AMP Story
 *
 * This transformer will:
 * - append `link[rel=stylesheet]` to `amp-story-1.0.css`.
 * - modify the `amp-custom` CSS to use `--amp-story-${vh/vw/vmin/vmax}`.
 * - append inline `<script>` for the `dvh` polyfill.
 * - SSR `data-story-supports-landscape`.
 * - SSR `aspect-ratio` into style.
 *
 * @package ampproject/amp-toolbox
 */
final class AmpStoryCssOptimizer implements Transformer
{
    /**
     * AMP Story dvh pollyfill script.
     *
     * @var string
     */
    const AMP_STORY_DVH_POLYFILL_CONTENT = '"use strict";if(!self.CSS||!CSS.supports||!CSS.supports("height:1dvh"))'
        . '{function e(){document.documentElement.style.setProperty("--story-dvh",innerHeight/100+"px","important")}'
        . 'addEventListener("resize",e,{passive:!0}),e()}';

    /**
     * Configuration store to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * Instantiate an AmpStoryCssOptimizer object.
     *
     * @param TransformerConfiguration $configuration Configuration store to use.
     */
    public function __construct(TransformerConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        if (!$this->configuration->get(AmpStoryCssOptimizerConfiguration::OPTIMIZE_AMP_STORY)) {
            return;
        }

        $hasAmpStoryScript            = false;
        $hasAmpStoryDvhPolyfillScript = false;
        $styleAmpCustom               = null;

        foreach ($document->head->childNodes as $childNode) {
            if (! $childNode instanceof Element) {
                continue;
            }

            if ($this->isAmpStoryScript($childNode)) {
                $hasAmpStoryScript = true;
                continue;
            }

            if ($this->isAmpStoryDvhPolyfillScript($childNode)) {
                $hasAmpStoryDvhPolyfillScript = true;
                continue;
            }

            if ($this->isStyleAmpCustom($childNode)) {
                $styleAmpCustom = $childNode;
                continue;
            }
        }

        // We can return early if no amp-story script is found.
        if (! $hasAmpStoryScript) {
            return;
        }

        $this->appendAmpStoryCssLink($document);

        if ($styleAmpCustom) {
            $this->modifyAmpCustomCSS($styleAmpCustom);
            // Make sure to not install the dvh polyfill twice.
            if (! $hasAmpStoryDvhPolyfillScript) {
                $this->appendAmpStoryDvhPolyfillScript($document);
            }
        }

        $this->supportsLandscapeSSR($document);
        $this->aspectRatioSSR($document);
    }

    /**
     * Check whether the element is an AMP Story element.
     *
     * @param Element $element Element to check.
     * @return bool Whether the given element is an AMP story.
     */
    private function isAmpStoryScript(Element $element)
    {
        return $element->tagName === Tag::SCRIPT
            && $element->getAttribute(Attribute::CUSTOM_ELEMENT) === Extension::STORY;
    }

    /**
     * Check whether the element is a script[amp-story-dvh-polyfill] element.
     *
     * @param Element $element Element to check.
     * @return bool Whether the element is a script[amp-story-dvh-polyfill] element.
     */
    private function isAmpStoryDvhPolyfillScript(Element $element)
    {
        return $element->tagName === Tag::SCRIPT
            && $element->hasAttribute(Attribute::AMP_STORY_DVH_POLLYFILL);
    }

    /**
     * Check whether the element is a style[amp-custom] element.
     *
     * @param Element $element Element to check.
     * @return bool Whether the element is a style[amp-custom] element.
     */
    private function isStyleAmpCustom(Element $element)
    {
        return $element->tagName === Tag::STYLE
            && $element->hasAttribute(Attribute::AMP_CUSTOM);
    }

    /**
     * Insert a link element with amp-story css source.
     *
     * @param Document $document Document to append the link.
     */
    private function appendAmpStoryCssLink(Document $document)
    {
        // @TODO Need to take the following into account when deciding on a version:
        // - latest stable version available,
        // - the channel that the runtime is locked to, i.e. whether LTS is active.
        $href = Amp::CACHE_HOST . '/v0/amp-story-1.0.css';

        $ampStoryCssLink = $document->createElementWithAttributes(Tag::LINK, [
            Attribute::REL           => Attribute::REL_STYLESHEET,
            Attribute::AMP_EXTENSION => Extension::STORY,
            Attribute::HREF          => $href,
        ]);

        $document->head->appendChild($ampStoryCssLink);
    }

    /**
     * Replace viewport units in custom css with related css variables.
     *
     * @param Element $style The style element to modify.
     */
    private function modifyAmpCustomCSS(Element $style)
    {
        $style->nodeValue = preg_replace(
            '/(-?[\d.]+)v(w|h|min|max)/',
            'calc($1 * var(--story-page-v$2))',
            $style->nodeValue
        );
    }

    /**
     * Append an inline script tag for the dvh polyfill
     *
     * @param Document $document The document in which we need to append the script tag.
     * @return void
     */
    private function appendAmpStoryDvhPolyfillScript(Document $document)
    {
        $ampStoryDvhPolyfillScript = $document->createElementWithAttributes(
            Tag::SCRIPT,
            [
                Attribute::AMP_STORY_DVH_POLLYFILL => '',
            ],
            self::AMP_STORY_DVH_POLYFILL_CONTENT
        );

        $document->head->appendChild($ampStoryDvhPolyfillScript);
    }

    /**
     * Add data-story-supports-landscape attribute to support landscape.
     *
     * @param Document $document The document in which we need to add the attribute.
     */
    private function supportsLandscapeSSR(Document $document)
    {
        $story = $document->body->getElementsByTagName(Extension::STORY)->item(0);

        if (! $story instanceof Element) {
            return;
        }

        if ($story->hasAttribute(Attribute::SUPPORTS_LANDSCAPE)) {
            $document->html->setAttribute(Attribute::DATA_STORY_SUPPORTS_LANDSCAPE, '');
        }
    }

    /**
     * Add aspect-ratio inline style for amp-story-grid-layer.
     *
     * @param Document $document The document in which we need to add the style.
     */
    private function aspectRatioSSR(Document $document)
    {
        for ($node = $document->body; $node !== null; $node = NodeWalker::nextNode($node)) {
            if (! $node instanceof Element) {
                continue;
            }

            if (Amp::isTemplate($node)) {
                $node = NodeWalker::skipNodeAndChildren($node);
                continue;
            }

            if ($node->tagName !== Extension::STORY_GRID_LAYER) {
                continue;
            }

            if (! $node->hasAttribute(Attribute::ASPECT_RATIO)) {
                continue;
            }

            $aspectRatio = str_replace(':', '/', $node->getAttribute(Attribute::ASPECT_RATIO));

            $node->addInlineStyle("--aspect-ratio:{$aspectRatio}", true);
        }
    }
}
