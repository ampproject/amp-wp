<?php

namespace Amp\Optimizer;

use Amp\AmpWP\Dom\Document;

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
	 * @param Document $document DOM document to apply the transformations to.
	 * @return void
	 */
	public function optimizeDom(Document $document)
	{
		foreach ($this->getTransformers() as $transformer) {
			$transformer->transform($document);
		}
	}

	/**
	 * Apply transformations to the provided string of HTML markup.
	 *
	 * @param string html HTML markup to apply the transformations to.
	 * @return string Optimized HTML string.
	 */
	public function optimizeHtml($html)
	{
		$dom = Document::from_html($html);
		$this->optimizeDom($dom);
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
				$transformers[$transformerClass] = new $transformerClass();
			}
		}

		return $transformers;
	}
}
