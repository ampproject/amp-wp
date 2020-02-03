<?php

namespace Amp\Optimizer;

use Amp\Dom\Document;

/**
 * A singular transformer that is part of the transformation engine.
 *
 * @package amp/optimizer
 */
interface Transformer
{

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors);
}
