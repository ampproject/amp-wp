<?php

namespace Amp\Optimizer;

// @todo The Document class should not be WP-specific.
use Amp\AmpWP\Dom\Document;

interface Transformer
{

	/**
	 * Apply transformations to the provided DOM document.
	 *
	 * @param Document $document DOM document to apply the transformations to.
	 * @return void
	 */
	public function transform(Document $document);
}
