<?php

namespace AmpProject\Optimizer;

use AmpProject\Dom\Document;
use AmpProject\RemoteGetRequest;
use AmpProject\RemoteRequest\CurlRemoteGetRequest;
use AmpProject\RemoteRequest\TemporaryFileCachedRemoteGetRequest;
use AmpProject\Validator\Spec;
use ReflectionClass;
use ReflectionException;

/**
 * Transformation engine that accepts HTML and returns optimized HTML.
 *
 * @package ampproject/amp-toolbox
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
     * Collection of transformers that were initialized.
     *
     * @var Transformer[]
     */
    private $transformers;

    /**
     * Validator Spec instance to use.
     *
     * @var Spec
     */
    private $spec;

    /**
     * Instantiate a TransformationEngine object.
     *
     * @param Configuration|null    $configuration Optional. Configuration data to use for setting up the transformers.
     * @param RemoteGetRequest|null $remoteRequest Optional. Transport to use for remote requests. Defaults to the
     *                                             CurlRemoteGetRequest implementation shipped with the library.
     * @param Spec                  $spec          Optional. Validator spec instance to use.
     */
    public function __construct(
        Configuration $configuration = null,
        RemoteGetRequest $remoteRequest = null,
        Spec $spec = null
    ) {
        $this->configuration = isset($configuration) ? $configuration : new DefaultConfiguration();
        $this->remoteRequest = $remoteRequest;
        $this->spec          = $spec;

        $this->initializeTransformers();
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
        foreach ($this->transformers as $transformer) {
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
     * Initialize the array of transformers to use.
     */
    private function initializeTransformers()
    {
        $this->transformers = [];

        foreach ($this->configuration->get(Configuration::KEY_TRANSFORMERS) as $transformerClass) {
            $this->transformers[$transformerClass] = new $transformerClass(
                ...$this->getTransformerDependencies($transformerClass)
            );
        }
    }

    /**
     * Get the dependencies of a transformer and put them in the correct order.
     *
     * @param string $transformerClass Class of the transformer to get the dependencies for.
     * @return array Array of dependencies in the order as they appear in the transformer's constructor.
     * @throws ReflectionException If the transformer could not be reflected upon.
     */
    private function getTransformerDependencies($transformerClass)
    {
        $constructor = (new ReflectionClass($transformerClass))->getConstructor();

        if ($constructor === null) {
            return [];
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $dependencyType = null;

            // The use of `ReflectionParameter::getClass()` is deprecated in PHP 8, and is superseded
            // by `ReflectionParameter::getType()`. See https://github.com/php/php-src/pull/5209.
            if (PHP_VERSION_ID >= 70100) {
                if ($parameter->getType()) {
                    /** @var \ReflectionNamedType $returnType */
                    $returnType = $parameter->getType();
                    $dependencyType = new ReflectionClass($returnType->getName());
                }
            } else {
                $dependencyType = $parameter->getClass();
            }

            if ($dependencyType === null) {
                // No type provided, so we pass `null` in the hopes that the argument is optional.
                $dependencies[] = null;
                continue;
            }

            if (is_a($dependencyType->name, TransformerConfiguration::class, true)) {
                $dependencies[] = $this->configuration->getTransformerConfiguration($transformerClass);
                continue;
            }

            if (is_a($dependencyType->name, RemoteGetRequest::class, true)) {
                if ($this->remoteRequest === null) {
                    $this->remoteRequest = new TemporaryFileCachedRemoteGetRequest(new CurlRemoteGetRequest());
                }
                $dependencies[] = $this->remoteRequest;
                continue;
            }

            if (is_a($dependencyType->name, Spec::class, true)) {
                if ($this->spec === null) {
                    $this->spec = new Spec();
                }
                $dependencies[] = $this->spec;
                continue;
            }

            // Unknown dependency type, so we pass `null` in the hopes that the argument is optional.
            $dependencies[] = null;
        }

        return $dependencies;
    }
}
