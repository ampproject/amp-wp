<?php

namespace Amp\Optimizer;

use Amp\Dom\Document;

final class TransformationEngine
{

    /**
     * Internal storage for the configuration settings.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Instantiate a TransformationEngine object.
     *
     * @param Configuration $configuration Configuration data to use for setting up the transformers.
     */
    public function __construct(Configuration $configuration)
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
                if (is_a($transformerClass, Configurable::class) && $this->configuration->has($transformerClass)) {
                    $transformers[$transformerClass] = new $transformerClass($this->configuration->get($transformerClass));
                } else {
                    $transformers[$transformerClass] = new $transformerClass();
                }
            }
        }

        return $transformers;
    }
}
