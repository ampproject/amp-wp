<?php

namespace AmpProject\Optimizer\Tests\TestTransformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;

final class NoDependencies implements Transformer
{

    public function __construct()
    {
    }

    public function transform(Document $document, ErrorCollection $errors)
    {
    }
}
