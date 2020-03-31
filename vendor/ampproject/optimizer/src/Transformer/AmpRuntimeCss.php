<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\Configurable;
use AmpProject\Optimizer\Configuration\AmpRuntimeCssConfiguration;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\MakesRemoteRequests;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\RemoteGetRequest;
use AmpProject\Optimizer\Transformer;
use AmpProject\RuntimeVersion;
use AmpProject\Tag;
use DOMElement;
use Exception;

/**
 * Transformer adding https://cdn.ampproject.org/v0.css if server-side-rendering is applied (known by the presence of
 * <style amp-runtime> tag). AMP runtime css (v0.css) will always be inlined as it'll get automatically updated to the
 * latest version once the AMP runtime has loaded.
 *
 * This is ported from the NodeJS optimizer while verifying against the Go version.
 *
 * NodeJS:
 *
 * @version 6f465eb24b05acf74d39541151c17b8d8d97450d
 * @link    https://github.com/ampproject/amp-toolbox/blob/6f465eb24b05acf74d39541151c17b8d8d97450d/packages/optimizer/lib/transformers/AmpBoilerplateTransformer.js
 *
 * Go:
 * @version c9993b8ac4d17d1f05d3a1289956dadf3f9c370a
 * @link    https://github.com/ampproject/amppackager/blob/c9993b8ac4d17d1f05d3a1289956dadf3f9c370a/transformer/transformers/ampruntimecss.go
 *
 * @package ampproject/optimizer
 */
final class AmpRuntimeCss implements Transformer, Configurable, MakesRemoteRequests
{

    /**
     * XPath query to fetch the <style amp-runtime> element.
     *
     * @var string
     */
    const AMP_RUNTIME_STYLE_XPATH = './/style[ @amp-runtime ]';

    /**
     * Name of the boilerplate style file.
     *
     * @var string
     */
    const V0_CSS = 'v0.css';

    /**
     * URL of the boilerplate style file.
     *
     * @var string
     */
    const V0_CSS_URL = Amp::CACHE_HOST . '/' . self::V0_CSS;

    /**
     * Transport to use for remote requests.
     *
     * @var RemoteGetRequest
     */
    private $remoteRequest;

    /**
     * Configuration store to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * Instantiate an AmpRuntimeCss object.
     *
     * @param RemoteGetRequest         $remoteRequest Transport to use for remote requests.
     * @param TransformerConfiguration $configuration Configuration store to use.
     */
    public function __construct(RemoteGetRequest $remoteRequest, TransformerConfiguration $configuration)
    {
        $this->remoteRequest = $remoteRequest;
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
        $ampRuntimeStyle = $this->findAmpRuntimeStyle($document, $errors);

        if (! $ampRuntimeStyle) {
            return;
        }

        $this->addStaticCss($document, $ampRuntimeStyle, $errors);
    }

    /**
     * Find the <style amp-runtime> element.
     *
     * @param Document        $document Document to find the element in.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return DOMElement|false DOM element for the <style amp-runtime> tag, or false if not found.
     */
    private function findAmpRuntimeStyle(Document $document, ErrorCollection $errors)
    {
        $ampRuntimeStyle = $document->xpath
            ->query(self::AMP_RUNTIME_STYLE_XPATH, $document->head)
            ->item(0);

        if (! $ampRuntimeStyle instanceof DOMElement) {
            $version = $this->configuration->get(AmpRuntimeCssConfiguration::VERSION);
            $errors->add(Error\CannotInlineRuntimeCss::fromMissingAmpRuntimeStyle($version));
            return false;
        }

        return $ampRuntimeStyle;
    }

    /**
     * Add the static boilerplate CSS to the <style amp-runtime> element.
     *
     * @param Document        $document        Document to add the static CSS to.
     * @param DOMElement      $ampRuntimeStyle DOM element for the <style amp-runtime> tag to add the static CSS to.
     * @param ErrorCollection $errors          Error collection to add errors to.
     */
    private function addStaticCss(Document $document, DOMElement $ampRuntimeStyle, ErrorCollection $errors)
    {
        $version = $this->configuration->get(AmpRuntimeCssConfiguration::VERSION);

        // We can always inline v0.css as the AMP runtime will take care of keeping v0.css in sync.
        try {
            $this->inlineCss($ampRuntimeStyle, $version);
        } catch (Exception $exception) {
            $errors->add(Error\CannotInlineRuntimeCss::fromException($exception, $ampRuntimeStyle, $version));
            $this->linkCss($document, $ampRuntimeStyle);
        }
    }

    /**
     * Insert the boilerplate style as inline CSS.
     *
     * @param DOMElement $ampRuntimeStyle DOM element for the <style amp-runtime> tag to inline the CSS into.
     * @param string     $version         Version of the boilerplate style to use.
     */
    private function inlineCss(DOMElement $ampRuntimeStyle, $version)
    {
        // Use version passed in via params if available, otherwise fetch the current prod version.
        if (! empty($version)) {
            $v0CssUrl = $this->appendRuntimeVersion(Amp::CACHE_HOST, $version) . '/' . self::V0_CSS;
        } else {
            $v0CssUrl = self::V0_CSS_URL;
            $options  = [RuntimeVersion::OPTION_CANARY => $this->configuration->get(AmpRuntimeCssConfiguration::CANARY)];
            $version  = (new RuntimeVersion($this->remoteRequest))->currentVersion($options);
        }

        $ampRuntimeStyle->setAttribute(Attribute::I_AMPHTML_VERSION, $version);
        $response   = $this->remoteRequest->get($v0CssUrl);
        $statusCode = $response->getStatusCode();

        if (200 < $statusCode || $statusCode >= 300) {
            return;
        }

        $ampRuntimeStyle->textContent = $response->getBody();
    }

    /**
     * Insert the boilerplate style as inline CSS.
     *
     * @param Document   $document        Document to link the CSS in.
     * @param DOMElement $ampRuntimeStyle DOM element for the <style amp-runtime> tag to inline the CSS into.
     */
    private function linkCss(Document $document, DOMElement $ampRuntimeStyle)
    {
        $cssStyleNode = $document->createElement(Tag::LINK);
        $cssStyleNode->setAttribute(Attribute::REL, Attribute::REL_STYLESHEET);
        $cssStyleNode->setAttribute(Attribute::HREF, self::V0_CSS_URL);

        $ampRuntimeStyle->parentNode->insertBefore($cssStyleNode, $ampRuntimeStyle);
    }

    /**
     * Append the runtime version to the host URL.
     *
     * @param string $host    Host domain to use.
     * @param string $version Version to use.
     * @return string Runtime version URL.
     */
    private function appendRuntimeVersion($host, $version)
    {
        return "{$host}/rtv/{$version}";
    }
}
