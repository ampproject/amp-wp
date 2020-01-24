<?php

namespace Amp\Optimizer\Transformer;

use Amp\Amp;
use Amp\Attribute;
use Amp\Dom\Document;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Transformer;
use DOMElement;
use DOMNode;

/**
 * Transformer applying the head reordering transformations to the HTML input.
 *
 * ReorderHead reorders the children of <head>. Specifically, it
 * orders the <head> like so:
 * (0) <meta charset> tag
 * (1) <style amp-runtime> (inserted by ampruntimecss.go)
 * (2) remaining <meta> tags (those other than <meta charset>)
 * (3) AMP runtime .js <script> tag
 * (4) AMP viewer runtime .js <script>
 * (5) <script> tags that are render delaying
 * (6) <script> tags for remaining extensions
 * (7) <link> tag for favicons
 * (8) <link> tag for resource hints
 * (9) <link rel=stylesheet> tags before <style amp-custom>
 * (10) <style amp-custom>
 * (11) any other tags allowed in <head>
 * (12) AMP boilerplate (first style amp-boilerplate, then noscript)
 *
 * This is ported from the NodeJS optimizer while verifying against the Go version.
 *
 * NodeJS:
 *
 * @version c92d6023ea4c9edadff593742a992da2b400a75d
 * @link    https://github.com/ampproject/amp-toolbox/blob/c92d6023ea4c9edadff593742a992da2b400a75d/packages/optimizer/lib/transformers/ServerSideRendering.js
 *
 * Go:
 * @version ea0959046c179953de43077eafaeb720f9b20bdf
 * @link    https://github.com/ampproject/amppackager/blob/ea0959046c179953de43077eafaeb720f9b20bdf/transformer/transformers/transformedidentifier.go
 *
 * @package Amp\Optimizer
 */
final class ReorderHead implements Transformer
{

    /*
     * Different categories of <head> tags to track and reorder.
     */
    private $linkIcons                         = [];
    private $linkStyleAmpRuntime               = null;
    private $linkStylesheetsBeforeAmpCustom    = [];
    private $metaCharset                       = null;
    private $metaOther                         = [];
    private $noscript                          = null;
    private $others                            = [];
    private $resourceHintLinks                 = [];
    private $scriptAmpRuntime                  = null;
    private $scriptAmpViewer                   = null;
    private $scriptNonRenderDelayingExtensions = [];
    private $scriptRenderDelayingExtensions    = [];
    private $styleAmpBoilerplate               = null;
    private $styleAmpCustom                    = null;
    private $styleAmpRuntime                   = null;

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        $nodes = $document->head->childNodes;

        if (! $nodes) {
            return;
        }

        while ($document->head->hasChildNodes()) {
            $node = $document->head->removeChild($document->head->firstChild);
            $this->registerNode($node);
        }

        $this->deduplicateAndSortCustomNodes();
        $this->appendToHead($document->head);
    }

    /**
     * Register a given node in the appropriate category.
     *
     * @param DOMNode $node Node to register.
     */
    private function registerNode(DOMNode $node)
    {
        if (! $node instanceof DOMElement) {
            if ($node->nodeType === XML_TEXT_NODE) {
                $nodeContent = trim($node->textContent);
                if (empty($nodeContent)) {
                    return;
                }
            }
            $this->others[] = $node;
            return;
        }

        switch ($node->tagName) {
            case 'meta':
                $this->registerMeta($node);
                break;
            case 'script':
                $this->registerScript($node);
                break;
            case 'style':
                $this->registerStyle($node);
                break;
            case 'link':
                $this->registerLink($node);
                break;
            case 'noscript':
                $this->noscript = $node;
                break;
            default:
                $this->others[] = $node;
        }
    }

    /**
     * Register a <meta> node.
     *
     * @param DOMElement $node Node to register.
     */
    private function registerMeta(DOMElement $node)
    {
        if ($node->hasAttribute('charset')) {
            $this->metaCharset = $node;
            return;
        }

        $this->metaOther[] = $node;
    }

    /**
     * Register a <script> node.
     *
     * @param DOMElement $node Node to register.
     */
    private function registerScript(DOMElement $node)
    {
        if ( Amp::isRuntimeScript( $node ) ) {
            $this->scriptAmpRuntime = $node;
            return;
        }

        if ( Amp::isViewerScript( $node ) ) {
            $this->scriptAmpViewer = $node;
            return;
        }

        if ($node->hasAttribute(Attribute::CUSTOM_ELEMENT)) {
            if (Amp::isRenderDelayingExtension($node)) {
                $this->scriptRenderDelayingExtensions[] = $node;
                return;
            }
            $this->scriptNonRenderDelayingExtensions[] = $node;
            return;
        }

        if ($node->hasAttribute(Attribute::CUSTOM_TEMPLATE)) {
            $this->scriptNonRenderDelayingExtensions[] = $node;
            return;
        }

        $this->others[] = $node;
    }

    /**
     * Register a <style> node.
     *
     * @param DOMElement $node Node to register.
     */
    private function registerStyle(DOMElement $node)
    {
        if ($node->hasAttribute('amp-runtime')) {
            $this->styleAmpRuntime = $node;
            return;
        }

        if ($node->hasAttribute('amp-custom')) {
            $this->styleAmpCustom = $node;
            return;
        }

        if ($node->hasAttribute('amp-boilerplate')
            || $node->hasAttribute('amp4ads-boilerplate')) {
            $this->styleAmpBoilerplate = $node;
            return;
        }

        $this->others[] = $node;
    }

    /**
     * Register a <link> node.
     *
     * @param DOMElement $node Node to register.
     */
    private function registerLink(DOMElement $node)
    {
        switch (strtolower($node->getAttribute('rel'))) {
            case 'stylesheet':
                $href = $node->getAttribute('href');
                if ($href && substr($href, -7) === '/v0.css') {
                    $this->linkStyleAmpRuntime = $node;
                    return;
                }
                if (! $this->styleAmpCustom) {
                    // We haven't seen amp-custom yet.
                    $this->linkStylesheetsBeforeAmpCustom[] = $node;
                    return;
                }
                break;
            case 'icon':
            case 'shortcut icon':
            case 'icon shortcut':
                $this->linkIcons[] = $node;
                return;
            case 'preload':
            case 'prefetch':
            case 'dns-prefetch':
            case 'preconnect':
                $this->resourceHintLinks[] = $node;
                return;
        }

        $this->others[] = $node;
    }

    /**
     * Get the name of the custom node or template.
     *
     * @param DOMElement $node Node to get the name of.
     * @return string Name of the custom node or template. Empty string if none found.
     */
    private function getName(DOMElement $node)
    {
        if ($node->hasAttribute(Attribute::CUSTOM_ELEMENT)) {
            return $node->getAttribute(Attribute::CUSTOM_ELEMENT);
        }

        if ($node->hasAttribute(Attribute::CUSTOM_TEMPLATE)) {
            return $node->getAttribute(Attribute::CUSTOM_TEMPLATE);
        }

        return '';
    }

    /**
     * Append all registered nodes to the <head> node.
     *
     * @param DOMElement $head Head element to append the registered nodes to.
     */
    private function appendToHead(DOMElement $head)
    {
        $categories = [
            'metaCharset',
            'linkStyleAmpRuntime',
            'styleAmpRuntime',
            'metaOther',
            'scriptAmpRuntime',
            'scriptAmpViewer',
            'scriptRenderDelayingExtensions',
            'scriptNonRenderDelayingExtensions',
            'linkIcons',
            'resourceHintLinks',
            'linkStylesheetsBeforeAmpCustom',
            'styleAmpCustom',
            'others',
            'styleAmpBoilerplate',
            'noscript',
        ];

        foreach ($categories as $category) {
            if ($this->$category === null) {
                continue;
            }

            if ($this->$category instanceof DOMNode) {
                $head->appendChild($this->$category);
            } elseif (is_array($this->$category)) {
                foreach ($this->$category as $node) {
                    $head->appendChild($node);
                }
            }
        }
    }

    /**
     * Deduplicate and sort custom extensions.
     */
    private function deduplicateAndSortCustomNodes()
    {
        foreach (['scriptRenderDelayingExtensions', 'scriptNonRenderDelayingExtensions'] as $set) {
            $sortedNodes = [];
            foreach ($this->$set as $node) {
                $sortedNodes[$this->getName($node)] = $node;
            }
            ksort($sortedNodes);
            $this->$set = array_values($sortedNodes);
        }
    }
}
