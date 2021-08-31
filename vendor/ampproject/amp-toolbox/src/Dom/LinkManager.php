<?php

namespace AmpProject\Dom;

use AmpProject\Attribute;
use AmpProject\Exception\FailedToCreateLink;
use AmpProject\RequestDestination;
use AmpProject\Tag;
use DOMNode;

/**
 * Link manager class that is used to manage the <link> tags within a document's <head>.
 *
 * These can be used for example to give the browser hints about how to prioritize resources.
 *
 * @package ampproject/amp-toolbox
 */
final class LinkManager
{

    /**
     * List of relations currently managed by the link manager.
     *
     * @var array<string>
     */
    const MANAGED_RELATIONS = [
        Attribute::REL_DNS_PREFETCH,
        Attribute::REL_MODULEPRELOAD,
        Attribute::REL_PRECONNECT,
        Attribute::REL_PREFETCH,
        Attribute::REL_PRELOAD,
        Attribute::REL_PRERENDER,
    ];

    /**
     * Document to manage the links for.
     *
     * @var Document
     */
    private $document;

    /**
     * Reference node to attach the resource hint to.
     *
     * @var DOMNode|null
     */
    private $referenceNode;

    /**
     * Collection of links already attached to the document.
     *
     * The key of the array is a concatenation of the HREF and the REL attributes.
     *
     * @var Element[]
     */
    private $links = [];

    /**
     * LinkManager constructor.
     *
     * @param Document $document Document to manage the links for.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->detectExistingLinks();
    }

    private function detectExistingLinks()
    {
        $node = $this->document->head->firstChild;
        while ($node) {
            $nextSibling = $node->nextSibling;
            if (
                ! $node instanceof Element
                ||
                $node->tagName !== Tag::LINK
            ) {
                $node = $nextSibling;
                continue;
            }

            $key = $this->getKey($node);

            if ($key !== '') {
                $this->links[$this->getKey($node)] = $node;
            }

            $node = $nextSibling;
        }
    }

    /**
     * Get the key to use for storing the element in the links cache.
     *
     * @param Element $element Element to get the key for.
     * @return string Key to use. Returns an empty string for invalid elements.
     */
    private function getKey(Element $element)
    {
        $href = $element->getAttribute(Attribute::HREF);
        $rel  = $element->getAttribute(Attribute::REL);

        if (empty($href) || ! in_array($rel, self::MANAGED_RELATIONS, true)) {
            return '';
        }

        return "{$href}{$rel}";
    }

    /**
     * Add a dns-prefetch resource hint.
     *
     * @see https://www.w3.org/TR/resource-hints/#dns-prefetch
     *
     * @param string $href Origin to prefetch the DNS for.
     */
    public function addDnsPrefetch($href)
    {
        $this->add(Attribute::REL_DNS_PREFETCH, $href);
    }

    /**
     * Add a modulepreload declarative fetch primitive.
     *
     * @see https://html.spec.whatwg.org/multipage/links.html#link-type-modulepreload
     *
     * @param string      $href        Modular resource to preload.
     * @param string|null $type        Optional. Type of the resource. Defaults to not specified, which equals 'script'.
     * @param bool|string $crossorigin Optional. Whether and how to configure CORS. Accepts a boolean for adding a
     *                                 boolean crossorigin flag, or a string to set a specific crossorigin strategy.
     *                                 Allowed values are 'anonymous' and 'use-credentials'. Defaults to true.
     */
    public function addModulePreload($href, $type = null, $crossorigin = true)
    {
        $attributes = [];

        if ($type !== null) {
            $attributes = [Attribute::AS_ => $type];
        }

        if ($crossorigin !== false) {
            $attributes[Attribute::CROSSORIGIN] = is_string($crossorigin) ? $crossorigin : null;
        }

        $this->add(Attribute::REL_MODULEPRELOAD, $href, $attributes);
    }

    /**
     * Add a preconnect resource hint.
     *
     * @see https://www.w3.org/TR/resource-hints/#dfn-preconnect
     *
     * @param string      $href        Origin to preconnect to.
     * @param bool|string $crossorigin Optional. Whether and how to configure CORS. Accepts a boolean for adding a
     *                                 boolean crossorigin flag, or a string to set a specific crossorigin strategy.
     *                                 Allowed values are 'anonymous' and 'use-credentials'. Defaults to true.
     */
    public function addPreconnect($href, $crossorigin = true)
    {
        $this->add(
            Attribute::REL_PRECONNECT,
            $href,
            $crossorigin !== false ? [Attribute::CROSSORIGIN => (is_string($crossorigin) ? $crossorigin : null)] : []
        );

        // Use dns-prefetch as fallback for browser that don't support preconnect.
        // See https://web.dev/preconnect-and-dns-prefetch/#resolve-domain-name-early-with-reldns-prefetch.
        $this->addDnsPrefetch($href);
    }

    /**
     * Add a prefetch resource hint.
     *
     * @see https://www.w3.org/TR/resource-hints/#prefetch
     *
     * @param string      $href        URL to the resource to prefetch.
     * @param string      $type        Optional. Type of the resource. Defaults to type 'image'.
     * @param bool|string $crossorigin Optional. Whether and how to configure CORS. Accepts a boolean for adding a
     *                                 boolean crossorigin flag, or a string to set a specific crossorigin strategy.
     *                                 Allowed values are 'anonymous' and 'use-credentials'. Defaults to true.
     */
    public function addPrefetch($href, $type = RequestDestination::IMAGE, $crossorigin = true)
    {
        // TODO: Should we enforce a valid $type here?

        $attributes = [Attribute::AS_ => $type];

        if ($crossorigin !== false) {
            $attributes[Attribute::CROSSORIGIN] = is_string($crossorigin) ? $crossorigin : null;
        }

        $this->add(Attribute::REL_PREFETCH, $href, $attributes);
    }

    /**
     * Add a preload declarative fetch primitive.
     *
     * @see https://www.w3.org/TR/preload/
     *
     * @param string      $href        Resource to preload.
     * @param string      $type        Optional. Type of the resource. Defaults to type 'image'.
     * @param string|null $media       Optional. Media query to add to the preload. Defaults to none.
     * @param bool|string $crossorigin Optional. Whether and how to configure CORS. Accepts a boolean for adding a
     *                                 boolean crossorigin flag, or a string to set a specific crossorigin strategy.
     *                                 Allowed values are 'anonymous' and 'use-credentials'. Defaults to true.
     */
    public function addPreload($href, $type = RequestDestination::IMAGE, $media = null, $crossorigin = true)
    {
        // TODO: Should we enforce a valid $type here?

        $attributes = [Attribute::AS_ => $type];

        if (!empty($media)) {
            $attributes[Attribute::MEDIA] = $media;
        }

        if ($crossorigin !== false) {
            $attributes[Attribute::CROSSORIGIN] = is_string($crossorigin) ? $crossorigin : null;
        }

        $this->add(Attribute::REL_PRELOAD, $href, $attributes);
    }

    /**
     * Add a prerender resource hint.
     *
     * @see https://www.w3.org/TR/resource-hints/#prerender
     *
     * @param string $href URL of the page to prerender.
     */
    public function addPrerender($href)
    {
        $this->add(Attribute::REL_PRERENDER, $href);
    }

    /**
     * Add a link to the document.
     *
     * @param string   $rel        A 'rel' string.
     * @param string   $href       URL to link to.
     * @param string[] $attributes Associative array of attributes and their values.
     */
    public function add($rel, $href, $attributes = [])
    {
        $link = $this->document->createElement(Tag::LINK);
        $link->setAttribute(Attribute::REL, $rel);
        $link->setAttribute(Attribute::HREF, $href);
        foreach ($attributes as $attribute => $value) {
            $link->setAttribute($attribute, $value);
        }

        $this->remove($rel, $href);

        if (!isset($this->referenceNode)) {
            $this->referenceNode = $this->document->viewport;
        }

        if ($this->referenceNode) {
            $link = $this->document->head->insertBefore($link, $this->referenceNode->nextSibling);
        } else {
            $link = $this->document->head->appendChild($link);
        }

        if (! $link instanceof Element) {
            throw FailedToCreateLink::forLink($link);
        }

        $this->links[$this->getKey($link)] = $link;

        $this->referenceNode = $link;
    }

    /**
     * Get a specific link from the link manager.
     *
     * @param string $rel  Relation to fetch.
     * @param string $href Reference to fetch.
     * @return Element|null Requested link as a Dom\Element, or null if not found.
     */
    public function get($rel, $href)
    {
        $key = "{$href}{$rel}";

        if (! array_key_exists($key, $this->links)) {
            return null;
        }

        return $this->links[$key];
    }

    /**
     * Remove a specific link from the document.
     *
     * @param string $rel  Relation of the link to remove.
     * @param string $href Reference of the link to remove.
     */
    public function remove($rel, $href)
    {
        $key = "{$href}{$rel}";

        if (! array_key_exists($key, $this->links)) {
            return;
        }

        $link = $this->links[$key];
        $link->parentNode->removeChild($link);

        unset($this->links[$key]);
    }
}
