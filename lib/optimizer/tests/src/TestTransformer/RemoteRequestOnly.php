<?php

namespace AmpProject\Optimizer\Tests\TestTransformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\RemoteGetRequest;

final class RemoteRequestOnly implements Transformer
{

    public function __construct(RemoteGetRequest $remoteRequest)
    {
    }

    public function transform(Document $document, ErrorCollection $errors)
    {
    }
}
