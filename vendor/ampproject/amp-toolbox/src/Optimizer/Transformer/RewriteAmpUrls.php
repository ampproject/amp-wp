<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Optimizer\Configuration\RewriteAmpUrlsConfiguration;
use AmpProject\Optimizer\Error\CannotAdaptDocumentForSelfHosting;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Exception\InvalidConfiguration;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\RuntimeVersion;
use AmpProject\Tag;
use AmpProject\Url;
use Exception;

/**
 * RewriteAmpUrls - rewrites AMP runtime URLs.
 *
 * This transformer supports five parameters:
 *
 * * `ampRuntimeVersion`: specifies a
 *   [specific version](https://github.com/ampproject/amp-toolbox/tree/main/runtime-version)
 *   version of the AMP runtime. For example: `ampRuntimeVersion: "001515617716922"` will result in AMP runtime URLs
 *   being re-written from `https://cdn.ampproject.org/v0.js` to `https://cdn.ampproject.org/rtv/001515617716922/v0.js`.
 *
 * * `ampUrlPrefix`: specifies an URL prefix for AMP runtime URLs. For example: `ampUrlPrefix: "/amp"` will result in
 *   AMP runtime URLs being re-written from `https://cdn.ampproject.org/v0.js` to `/amp/v0.js`. This option is
 *   experimental and not recommended.
 *
 * * `geoApiUrl`: specifies amp-geo API URL to use as a fallback when `amp-geo-0.1.js` is served unpatched, i.e. when
 *   `{{AMP_ISO_COUNTRY_HOTPATCH}}` is not replaced dynamically.
 *
 * * `lts`: Use long-term stable URLs. This option is not compatible with `rtv`, `ampRuntimeVersion` or `ampUrlPrefix`;
 *   an error will be thrown if these options are included together. Similarly, the `geoApiUrl` option is ineffective
 *   with the `lts` flag, but will simply be ignored rather than throwing an error.
 *
 * * `rtv`: Append the runtime version to the rewritten URLs. This option is not compatible with `lts`.
 *
 * * `esmModulesEnabled`: Use ES modules for loading the AMP runtime and components. Defaults to true.
 *
 * All parameters are optional. If no option is provided, runtime URLs won't be re-written. You can combine
 * `ampRuntimeVersion` and  `ampUrlPrefix` to rewrite AMP runtime URLs to versioned URLs on a different origin.
 *
 * This transformer also adds a preload header for the AMP runtime (v0.js) to trigger HTTP/2 push for CDNs (see
 * https://www.w3.org/TR/preload/#server-push-(http/2)).
 *
 * This is ported from the NodeJS optimizer while verifying against the Go version.
 *
 * NodeJS:
 * @version 7fbf187b3c7f07100e8911a52582b640b23490e5
 * @link https://github.com/ampproject/amp-toolbox/blob/7fbf187b3c7f07100e8911a52582b640b23490e5/packages/optimizer/lib/transformers/RewriteAmpUrls.js
 *
 * @package ampproject/amp-toolbox
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
        $host = $this->calculateHost();

        $this->adaptForEsmSupport($document, $host);
        $this->adaptForSelfHosting($document, $host, $errors);
    }

    /**
     * Adapt for ES modules support.
     *
     * @param Document $document Document to collect preload nodes for.
     * @param string   $host     Host URL to use.
     */
    private function adaptForEsmSupport(Document $document, $host)
    {
        $usesEsm = $this->configuration->get(RewriteAmpUrlsConfiguration::ESM_MODULES_ENABLED);

        $node = $document->head->firstChild;
        while ($node) {
            $nextSibling = $node->nextSibling;
            if (! $node instanceof Element) {
                $node = $nextSibling;
                continue;
            }

            $src  = $node->getAttribute(Attribute::SRC);
            $href = $node->getAttribute(Attribute::HREF);
            if ($node->tagName === Tag::SCRIPT && $this->usesAmpCacheUrl($src)) {
                $newUrl = $this->replaceUrl($src, $host);
                $node->setAttribute(Attribute::SRC, $newUrl);
                if ($usesEsm) {
                    $this->addEsm($document, $node);
                } else {
                    $this->addPreload($document, $newUrl, Tag::SCRIPT);
                }
            } elseif (
                $node->tagName === Tag::LINK
                &&
                $node->getAttribute(Attribute::REL) === Attribute::REL_STYLESHEET
                &&
                $this->usesAmpCacheUrl($href)
            ) {
                $newUrl = $this->replaceUrl($href, $host);
                $node->setAttribute(Attribute::HREF, $newUrl);
                $this->addPreload($document, $newUrl, Tag::STYLE);
            } elseif (
                $node->tagName === Tag::LINK
                &&
                $node->getAttribute(Attribute::REL) === Attribute::REL_PRELOAD
                &&
                $this->usesAmpCacheUrl($href)
            ) {
                if ($usesEsm && substr_compare($href, 'v0.js', -5) === 0) {
                    // Only preload .mjs runtime in ESM mode.
                    $node->parentNode->removeChild($node);
                } else {
                    $node->setAttribute(Attribute::HREF, $this->replaceUrl($href, $host));
                }
            }

            $node = $nextSibling;
        }
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
        // Preloads for scripts/styles that were already created for the adapted host need to be skipped,
        // otherwise we end up with the lts or rtv suffix being added twice.
        if (strpos($url, $host) === 0) {
            return $url;
        }

        return str_replace(Amp::CACHE_HOST, $host, $url);
    }

    /**
     * Replace <script> elements with their ES module counterparts.
     *
     * @param Document $document   Document to add the ES module scripts to.
     * @param Element  $scriptNode Script element to replace.
     */
    private function addEsm(Document $document, Element $scriptNode)
    {
        $scriptUrl    = $scriptNode->getAttribute(Attribute::SRC);
        $esmScriptUrl = preg_replace('/\.js$/', '.mjs', $scriptUrl);

        if ($this->shouldPreload($scriptUrl, $document)) {
            $document->links->addModulePreload($esmScriptUrl, Tag::SCRIPT, Attribute::CROSSORIGIN_ANONYMOUS);
        }

        $nomoduleNode = $document->createElement(Tag::SCRIPT);
        $nomoduleNode->addBooleanAttribute(Attribute::ASYNC);
        $nomoduleNode->addBooleanAttribute(Attribute::NOMODULE);
        $nomoduleNode->setAttribute(Attribute::SRC, $scriptUrl);
        $nomoduleNode->setAttribute(Attribute::CROSSORIGIN, Attribute::CROSSORIGIN_ANONYMOUS);

        $scriptNode->copyAttributes([Attribute::CUSTOM_ELEMENT, Attribute::CUSTOM_TEMPLATE], $nomoduleNode);

        $scriptNode->parentNode->insertBefore($nomoduleNode, $scriptNode);

        $scriptNode->setAttribute(Attribute::TYPE, Attribute::TYPE_MODULE);
        // Without crossorigin=anonymous browser loads the script twice because
        // of preload.
        $scriptNode->setAttribute(Attribute::CROSSORIGIN, Attribute::CROSSORIGIN_ANONYMOUS);
        $scriptNode->setAttribute(Attribute::SRC, $esmScriptUrl);
    }

    /**
     * Add a preload directive to the document.
     *
     * @param Document $document Document to add the preload to.
     * @param string   $href     Href to use for the preload.
     * @param string   $type     Type to use for the preload.
     */
    private function addPreload(Document $document, $href, $type)
    {
        if (! $this->shouldPreload($href, $document)) {
            return;
        }

        // TODO: Should the preloads be crossorigin here? Maybe conditionally based on host vs origin?
        $document->links->addPreload($href, $type, null, false);
    }

    /**
     * Add meta tags as needed to adapt for self-hosting the AMP runtime.
     *
     * @param Document        $document Document to add the meta tags to.
     * @param string          $host     Host URL to use.
     * @param ErrorCollection $errors   Error collection to add potential errors to.
     */
    private function adaptForSelfHosting(Document $document, $host, $errors)
    {
        // runtime-host and amp-geo-api meta tags should appear before the first script.
        if (
            ! $this->usesAmpCacheUrl($host)
            &&
            ! $this->configuration->get(RewriteAmpUrlsConfiguration::LTS)
        ) {
            try {
                $url = new Url($host);

                if (!empty($url->scheme) && !empty($url->host)) {
                    $origin = "{$url->scheme}://{$url->host}";
                    $this->addMeta($document, 'runtime-host', $origin);
                } else {
                    $errors->add(CannotAdaptDocumentForSelfHosting::forNonAbsoluteUrl($host));
                }
            } catch (Exception $exception) {
                $errors->add(CannotAdaptDocumentForSelfHosting::fromException($exception));
            }
        }
        if (
            ! empty($this->configuration->get(RewriteAmpUrlsConfiguration::GEO_API_URL))
            &&
            ! $this->configuration->get(RewriteAmpUrlsConfiguration::LTS)
        ) {
            $this->addMeta(
                $document,
                'amp-geo-api',
                $this->configuration->get(RewriteAmpUrlsConfiguration::GEO_API_URL)
            );
        }
    }

    /**
     * Check whether a given URL should be preloaded.
     *
     * @param string   $url      Url to check.
     * @param Document $document Document on which to check.
     * @return bool Whether the provided URL should be preloaded.
     */
    private function shouldPreload($url, Document $document)
    {
        if ($document->documentElement->hasAttribute(Amp::NO_BOILERPLATE_ATTRIBUTE)) {
            return false;
        }

        return substr_compare($url, 'v0.js', -5) === 0
               ||
               substr_compare($url, 'v0.css', -6) === 0;
    }

    /**
     * Calculate the host string to use.
     *
     * @return string Host to use.
     */
    private function calculateHost()
    {
        $lts = $this->configuration->get(RewriteAmpUrlsConfiguration::LTS);
        $rtv = $this->configuration->get(RewriteAmpUrlsConfiguration::RTV);

        if ($lts && $rtv) {
            throw InvalidConfiguration::forMutuallyExclusiveFlags(
                RewriteAmpUrlsConfiguration::LTS,
                RewriteAmpUrlsConfiguration::RTV
            );
        }

        $ampUrlPrefix      = $this->configuration->get(RewriteAmpUrlsConfiguration::AMP_URL_PREFIX);
        $ampRuntimeVersion = $this->configuration->get(RewriteAmpUrlsConfiguration::AMP_RUNTIME_VERSION);

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
