<?php

namespace AmpProject\Optimizer\Tests\TestTransformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\RemoteGetRequest;

final class RemoteRequestThenConfiguration implements Transformer
{

    public function __construct(RemoteGetRequest $remoteRequest, TransformerConfiguration $configuration)
    {
    }

    public function transform(Document $document, ErrorCollection $errors)
    {
    }
}
