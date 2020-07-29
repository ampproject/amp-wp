<?php

namespace AmpProject\Optimizer\Tests\TestTransformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;

final class ConfigurationOnly implements Transformer
{

    public function __construct(TransformerConfiguration $configuration)
    {
    }

    public function transform(Document $document, ErrorCollection $errors)
    {
    }
}
