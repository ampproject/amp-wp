<?php

namespace AmpProject\Optimizer\Tests\TestTransformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\RemoteGetRequest;

final class ConfigurationThenRemoteRequest implements Transformer
{

    public function __construct(TransformerConfiguration $configuration, RemoteGetRequest $remoteRequest)
    {
    }

    public function transform(Document $document, ErrorCollection $errors)
    {
    }
}
