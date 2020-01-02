<?php

namespace Amp\Optimizer\Transformer;

use Amp\AmpWP\Dom\Document;
use Amp\Optimizer\Transformer;

final class ServerSideRendering implements Transformer
{

	const LAYOUT_ATTRIBUTE         = 'i-amphtml-layout';
	const NO_BOILERPLATE_ATTRIBUTE = 'i-amphtml-no-boilerplate';

	/**
	 * Apply transformations to the provided DOM document.
	 *
	 * @param Document $document DOM document to apply the transformations to.
	 * @return void
	 */
	public function transform(Document $document)
	{
		if ($this->isAlreadyTransformed($document)) {
			return;
		}

		/*
		 * Within the loop we apply the layout to the custom tags (amp-foo...)
		 * where possible, but while we're at this we also look for reasons
		 * not to remove the boilerplate.
		 */
		$can_remove_boilerplate = true;
		foreach ($document->amp_elements as $amp_element) {
            // @todo Do something here.
		}

		/*
		 * Below, we're only concerned about removing the boilerplate.
		 * If we've already determined that we can't, we're done here.
		 */
		if ( ! $can_remove_boilerplate) {
			return;
		}

		// The boilerplate can be removed, note it on the <html> tag.
		$document->html->setAttribute(self::NO_BOILERPLATE_ATTRIBUTE, '');
	}

	/**
	 * Check whether the document was already transformed.
	 *
	 * We want to ensure we don't apply server-side rendering
	 * modifications more than once.
	 *
	 * @param Document $document DOM document to apply the transformations to.
	 * @return bool Whether the document was already transformed.
	 */
	private function isAlreadyTransformed(Document $document)
	{
		if ($document->html->hasAttribute(self::LAYOUT_ATTRIBUTE)) {
			return true;
		}

		// Mark the document as "already transformed".
		$document->html->setAttribute(self::LAYOUT_ATTRIBUTE, '');

		return false;
	}
}
