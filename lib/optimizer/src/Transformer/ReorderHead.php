<?php

namespace Amp\Optimizer\Transformer;

use Amp\Amp;
use Amp\Dom\Document;
use Amp\Extension;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Transformer;
use DOMElement;
use DOMNode;

/**
 * Transformer applying the head reordering transformations to the HTML input.
 *
 * This transformer will reorder the tags within the <head> node like so:
 * (0) meta charset, then remaining meta tags.
 * (1) <style amp-runtime> (inserted by ServerSideRenderingTransformer)
 * (2) AMP runtime .js <script> tag
 * (3) <script> tags for render delaying extensions
 * (4) <script> tags for remaining extensions
 * (5) <link> tag for favicon
 * (6) <link rel=stylesheet> tags before <style amp-custom>
 * (7) <style amp-custom>
 * (8) any other tags allowed in <head>
 * (9) amp boilerplate (first style amp-boilerplate, then noscript).
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
    private $styleAmpRuntime                   = null;
    private $linkStyleAmpRuntime               = null;
    private $metaCharset                       = null;
    private $scriptAmpEngine                   = null;
    private $metaOther                         = [];
    private $scriptRenderDelayingExtensions    = [];
    private $scriptNonRenderDelayingExtensions = [];
    private $resourceHintLinks                 = [];
    private $linkIcons                         = [];
    private $styleAmpCustom                    = null;
    private $linkStylesheetsBeforeAmpCustom    = [];
    private $others                            = [];
    private $styleAmpBoilerplate               = null;
    private $noscript                          = null;

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

        foreach ($nodes as $node) {
            $this->registerNode($document->head->removeChild($node));
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
        // Currently there are two amp engine tags: v0.js and amp4ads-v0.js.
        // According to validation rules they are the only script tags with a src attribute and do not have attributes
        // custom-element or custom-template. Record the amp engine tag so it can be emitted first among script tags.
        if ($node->hasAttribute('src') && ! $this->getName($node)) {
            $this->scriptAmpEngine = $node;
            return;
        }

        if ($node->hasAttribute(Amp::CUSTOM_ELEMENT)) {
            if (Extension::isRenderDelayingExtension($node)) {
                $this->scriptRenderDelayingExtensions[] = $node;
                return;
            }
            $this->scriptNonRenderDelayingExtensions[] = $node;
            return;
        }

        if ($node->hasAttribute(Amp::CUSTOM_TEMPLATE)) {
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
        switch ($node->getAttribute('rel')) {
            case 'stylesheet':
                $href = $node->getAttribute('href');
                if ($href && substr($href, -7) === '/v0.css') {
                    $this->linkStyleAmpRuntime = $node;
                    break;
                }
                if (! $this->styleAmpCustom) {
                    // We haven't seen amp-custom yet.
                    $this->linkStylesheetsBeforeAmpCustom[] = $node;
                }
                break;
            case 'icon':
            case 'shortcut icon':
            case 'icon shortcut':
                $this->linkIcons[] = $node;
                break;
            case 'preload':
            case 'prefetch':
            case 'dns-prefetch':
            case 'preconnect':
                $this->resourceHintLinks[] = $node;
                break;
            default:
                $this->others[] = $node;
        }
    }

    /**
     * Get the name of the custom node or template.
     *
     * @param DOMElement $node Node to get the name of.
     * @return string Name of the custom node or template. Empty string if none found.
     */
    private function getName(DOMElement $node)
    {
        if ($node->hasAttribute(Amp::CUSTOM_ELEMENT)) {
            return $node->getAttribute(Amp::CUSTOM_ELEMENT);
        }

        if ($node->hasAttribute(Amp::CUSTOM_TEMPLATE)) {
            return $node->getAttribute(Amp::CUSTOM_TEMPLATE);
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
            'styleAmpRuntime',
            'linkStyleAmpRuntime',
            'resourceHintLinks',
            'metaOther',
            'scriptAmpEngine',
            'scriptRenderDelayingExtensions',
            'scriptNonRenderDelayingExtensions',
            'linkIcons',
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

            foreach ((array)$this->$category as $node) {
                $head->appendChild($node);
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
