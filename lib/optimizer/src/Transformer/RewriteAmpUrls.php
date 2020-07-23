<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\RuntimeVersion;
use AmpProject\Tag;
use DOMElement;
use DOMNode;
use Exception;

/**
 * RewriteAmpUrls - rewrites AMP runtime URLs.
 *
 * This transformer supports two parameters:
 *
 * * `ampRuntimeVersion`: specifies a
 *   [specific version](https://github.com/ampproject/amp-toolbox/tree/main/runtime-version)
 *   version</a> of the AMP runtime. For example: `ampRuntimeVersion:
 *   "001515617716922"` will result in AMP runtime URLs being re-written
 *   from `https://cdn.ampproject.org/v0.js` to
 *   `https://cdn.ampproject.org/rtv/001515617716922/v0.js`.
 *
 * * `ampUrlPrefix`: specifies an URL prefix for AMP runtime
 *   URLs. For example: `ampUrlPrefix: "/amp"` will result in AMP runtime
 *   URLs being re-written from `https://cdn.ampproject.org/v0.js` to
 *   `/amp/v0.js`. This option is experimental and not recommended.
 *
 * * `geoApiUrl`: specifies amp-geo API URL to use as a fallback when
 *   amp-geo-0.1.js is served unpatched, i.e. when
 *   {{AMP_ISO_COUNTRY_HOTPATCH}} is not replaced dynamically.
 *
 * * `lts`: Use long-term stable URLs. This option is not compatible with
 *   `ampRuntimeVersion` or `ampUrlPrefix`; an error will be thrown if
 *   these options are included together. Similarly, the `geoApiUrl`
 *   option is ineffective with the lts flag, but will simply be ignored
 *   rather than throwing an error.
 *
 * All parameters are optional. If no option is provided, runtime URLs won't be
 * re-written. You can combine `ampRuntimeVersion` and  `ampUrlPrefix` to
 * rewrite AMP runtime URLs to versioned URLs on a different origin.
 *
 * This transformer also adds a preload header for the AMP runtime (v0.js) to trigger HTTP/2
 * push for CDNs (see https://www.w3.org/TR/preload/#server-push-(http/2)).
 *
 * @package AmpProject\Optimizer\Transformer
 */
final class RewriteAmpUrls implements Transformer
{

    /**
     * Configuration to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * RewriteAmpUrls constructor.
     *
     * @param TransformerConfiguration $configuration Configuration to use.
     */
    public function __construct(TransformerConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the
     *                                  transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected
     *                                  during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        $host          = $this->calculateHost();
        $referenceNode = $document->xpath->query('./meta[ @name="viewport" ]', $document->head)->item(0);

        $preloadNodes = array_filter($this->collectPreloadNodes($document, $host));
        foreach ($preloadNodes as $preloadNode) {
            $document->head->insertBefore(
                $preloadNode,
                $referenceNode instanceof DOMNode ? $referenceNode->nextSibling : null
            );
        }

        $this->adaptForSelfHosting($document, $host);
    }

    /**
     * Collect all the preload nodes to be added to the <head>.
     *
     * @param Document $document Document to collect preload nodes for.
     * @return DOMNode[] Preload nodes.
     */
    private function collectPreloadNodes(Document $document, $host)
    {
        $usesEsm      = $this->configuration->get(Configuration\RewriteAmpUrlsConfiguration::EXPERIMENTAL_ESM);
        $preloadNodes = [];

        $node = $document->head->firstChild;
        while ($node) {
            if (! $node instanceof DOMElement) {
                $node = $node->nextSibling;
                continue;
            }

            $src = $node->getAttribute(Attribute::SRC);

            if ($node->tagName === Tag::SCRIPT && $this->usesAmpCacheUrl($src)) {
                $node->setAttribute(Attribute::SRC, $this->replaceUrl($src, $host));
                if ($usesEsm) {
                    $preloadNodes[] = $this->addEsm($document, $node);
                } else {
                    $preloadNodes[] = $this->createPreload($document, $src, Tag::SCRIPT);
                }
            } elseif ($node->tagName === Tag::LINK) {
                $href = $node->getAttribute(Attribute::HREF);

                if (
                    $node->getAttribute(Attribute::REL) === Attribute::REL_STYLESHEET
                    && $this->usesAmpCacheUrl($href)
                ) {
                    $node->setAttribute(
                        Attribute::HREF,
                        $this->replaceUrl($href, $host)
                    );
                    $preloadNodes[] = $this->createPreload($document, $href, Tag::STYLE);
                } elseif (
                    $node->getAttribute(Attribute::REL) === Attribute::REL_PRELOAD
                    && $this->usesAmpCacheUrl($href)
                ) {
                    if ($usesEsm && $this->shouldPreload($href)) {
                        // Only preload .mjs runtime in ESM mode.
                        $node->parentNode->removeChild($node);
                    } else {
                        $node->setAttribute(
                            Attribute::HREF,
                            $this->replaceUrl($href, $host)
                        );
                    }
                }
            }

            $node = $node->nextSibling;
        }

        return $preloadNodes;
    }

    /**
     * Check if a given URL uses the AMP Cache.
     *
     * @param string $url Url to check.
     * @return bool Whether the provided URL uses the AMP Cache.
     */
    private function usesAmpCacheUrl($url)
    {
        if (! $url) {
            return false;
        }

        return strpos($url, Amp::CACHE_HOST) === 0;
    }

    /**
     * Replace URL root with provided host.
     *
     * @param string $url  Url to replace.
     * @param string $host Host to use.
     * @return string Adapted URL.
     */
    private function replaceUrl($url, $host)
    {
        return str_replace(Amp::CACHE_HOST, $host, $url);
    }

    private function addEsm(Document $document, DOMElement $scriptNode)
    {
        $preloadNode  = null;
        $scriptUrl    = $scriptNode->getAttribute(Attribute::SRC);
        $esmScriptUrl = preg_replace('/\.js$/', '.mjs', $scriptUrl);

        if ($this->shouldPreload($scriptUrl)) {
            $preloadNode = $document->createElement(Tag::LINK);
            $preloadNode->setAttribute('as', Tag::SCRIPT);
            $preloadNode->setAttribute(Attribute::CROSSORIGIN, Attribute::CROSSORIGIN_ANONYMOUS);
            $preloadNode->setAttribute(Attribute::HREF, $esmScriptUrl);
            $preloadNode->setAttribute(Attribute::REL, Attribute::REL_PRELOAD);
        }

        $nomoduleNode = $document->createElement(Tag::SCRIPT);
        $nomoduleNode->setAttribute(Attribute::ASYNC, null);
        $nomoduleNode->setAttribute(Attribute::NOMODULE, null);
        $nomoduleNode->setAttribute(Attribute::SRC, $scriptUrl);

        $scriptNode->parentNode->insertBefore($nomoduleNode, $scriptNode);

        $scriptNode->setAttribute(Attribute::TYPE, Attribute::TYPE_MODULE);
        // Without crossorigin=anonymous browser loads the script twice because
        // of preload.
        $scriptNode->setAttribute(Attribute::CROSSORIGIN, Attribute::CROSSORIGIN_ANONYMOUS);
        $scriptNode->setAttribute(Attribute::SRC, $esmScriptUrl);

        return $preloadNode;
    }

    /**
     * Create a preload element to add to the head.
     *
     * @param Document $document Document to create the element in.
     * @param string   $href     Href to use for the preload.
     * @param string   $type     Type to use for the preload.
     * @return DOMElement|null Preload element, or null if not created.
     */
    private function createPreload(Document $document, $href, $type)
    {
        if (! $this->shouldPreload($href)) {
            return null;
        }

        $preloadNode = $document->createElement(Tag::LINK);

        $preloadNode->setAttribute(Attribute::REL, Attribute::REL_PRELOAD);
        $preloadNode->setAttribute(Attribute::HREF, $href);
        $preloadNode->setAttribute('as', $type);

        return $preloadNode;
    }

    private function adaptForSelfHosting(Document $document, $host)
    {
        // runtime-host and amp-geo-api meta tags should appear before the first script
        if (
            ! $this->usesAmpCacheUrl($host) && ! $this->configuration->get(
                Configuration\RewriteAmpUrlsConfiguration::LTS
            )
        ) {
            try {
                $urlParts = parse_url($host);
                $origin   = "{$urlParts['scheme']}://{$urlParts['host']}";
                $this->addMeta($document, 'runtime-host', $origin);
            } catch (Exception $exception) {
                // TODO: Log issue.
            }
        }
        if (
            ! empty(
                $this->configuration->get(
                    Configuration\RewriteAmpUrlsConfiguration::GEO_API_URL
                )
            ) && ! $this->configuration->get(
                Configuration\RewriteAmpUrlsConfiguration::LTS
            )
        ) {
            $this->addMeta(
                $document,
                'amp-geo-api',
                $this->configuration->get(Configuration\RewriteAmpUrlsConfiguration::GEO_API_URL)
            );
        }
    }

    /**
     * Check whether a given URL should be preloaded.
     *
     * @param string $url Url to check.
     * @return bool Whether the provided URL should be preloaded.
     */
    private function shouldPreload($url)
    {
        return substr_compare($url, 'v0.js', -5) === 0
               || substr_compare($url, 'v0.css', -6) === 0;
    }

    /**
     * Calculate the host string to use.
     *
     * @return string Host to use.
     */
    private function calculateHost()
    {
        $ampUrlPrefix      = $this->configuration->get(Configuration\RewriteAmpUrlsConfiguration::AMP_URL_PREFIX);
        $ampRuntimeVersion = $this->configuration->get(Configuration\RewriteAmpUrlsConfiguration::AMP_RUNTIME_VERSION);
        $lts               = $this->configuration->get(Configuration\RewriteAmpUrlsConfiguration::LTS);
        $rtv               = $this->configuration->get(Configuration\RewriteAmpUrlsConfiguration::RTV);

        if ($lts && $rtv) {
            // TODO: Throw logic exception.
        }

        $ampUrlPrefix = rtrim($ampUrlPrefix, '/');

        if ($ampRuntimeVersion && $rtv) {
            $ampUrlPrefix = RuntimeVersion::appendRuntimeVersion($ampUrlPrefix, $ampRuntimeVersion);
        } elseif ($lts) {
            $ampUrlPrefix .= '/lts';
        }

        return $ampUrlPrefix;
    }

    /**
     * Add meta element to the document head.
     *
     * @param Document $document Document to add the meta to.
     * @param string   $name     Name of the meta element.
     * @param string   $content  Value of the meta element.
     */
    private function addMeta(Document $document, $name, $content)
    {
        $meta = $document->createElement(Tag::META);
        $meta->setAttribute(Attribute::NAME, $name);
        $meta->setAttribute(Attribute::CONTENT, $content);
        $firstScript = $document->xpath->query('./script', $document->head)->item(0);
        $document->head->insertBefore($meta, $firstScript);
    }
}
