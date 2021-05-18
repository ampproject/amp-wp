<?php

namespace AmpProject\Optimizer;

use AmpProject\Dom\Document;

/**
 * A singular transformer that is part of the transformation engine.
 *
 * @package ampproject/amp-toolbox
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
