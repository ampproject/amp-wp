<?php

namespace AmpProject\Optimizer\Tests\TestTransformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;

final class NoConstructor implements Transformer
{

    public function transform(Document $document, ErrorCollection $errors)
    {
    }
}
