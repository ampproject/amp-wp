<?php

namespace AmpProject\Optimizer;

use AmpProject\Dom\Document;
use AmpProject\RemoteGetRequest;
use AmpProject\RemoteRequest\CurlRemoteGetRequest;

/**
 * Transformation engine that accepts HTML and returns optimized HTML.
 *
 * @package ampproject/optimizer
 */
final class TransformationEngine
{

    /**
     * Internal storage for the configuration settings.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Transport to use for remote requests.
     *
     * @var RemoteGetRequest
     */
    private $remoteRequest;

    /**
     * Instantiate a TransformationEngine object.
     *
     * @param Configuration    $configuration Configuration data to use for setting up the transformers.
     * @param RemoteGetRequest $remoteRequest Optional. Transport to use for remote requests. Defaults to the
     *                                     CurlRemoteGetRequest implementation shipped with the library.
     */
    public function __construct(Configuration $configuration, RemoteGetRequest $remoteRequest = null)
    {
        $this->configuration = $configuration;
        $this->remoteRequest = isset($remoteRequest) ? $remoteRequest : new CurlRemoteGetRequest();
    }

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function optimizeDom(Document $document, ErrorCollection $errors)
    {
        foreach ($this->getTransformers() as $transformer) {
            $transformer->transform($document, $errors);
        }
    }

    /**
     * Apply transformations to the provided string of HTML markup.
     *
     * @param string          $html   HTML markup to apply the transformations to.
     * @param ErrorCollection $errors Collection of errors that are collected during transformation.
     * @return string Optimized HTML string.
     */
    public function optimizeHtml($html, ErrorCollection $errors)
    {
        $dom = Document::fromHtml($html);
        $this->optimizeDom($dom, $errors);

        return $dom->saveHTML();
    }

    /**
     * Get the array of transformers to use.
     *
     * @return Transformer[] Array of transformers to use.
     */
    private function getTransformers()
    {
        static $transformers = null;

        if (null === $transformers) {
            $transformers = [];
            foreach ($this->configuration->get(Configuration::KEY_TRANSFORMERS) as $transformerClass) {
                $arguments = [];

                if (is_a($transformerClass, MakesRemoteRequests::class, true)) {
                    $arguments[] = $this->remoteRequest;
                }

                if (is_a($transformerClass, Configurable::class, true)) {
                    $arguments[] = $this->configuration->getTransformerConfiguration($transformerClass);
                }

                $transformers[$transformerClass] = new $transformerClass(...$arguments);
            }
        }

        return $transformers;
    }
}
