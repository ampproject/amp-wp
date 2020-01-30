<?php

namespace Amp\Optimizer\Transformer;

use Amp\Dom\Document;
use Amp\Optimizer\Configurable;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Transformer;

/**
 * Transformer applying the server-side rendering transformations to the HTML input.
 *
 * This is ported from the NodeJS optimizer while verifying against the Go version.
 *
 * NodeJS:
 * @version 2ca65a94b77130c91ac11fcc32c94b93cbd2b7a0
 * @link https://github.com/ampproject/amp-toolbox/blob/2ca65a94b77130c91ac11fcc32c94b93cbd2b7a0/packages/optimizer/lib/transformers/AddTransformedFlag.js
 *
 * Go:
 * @version b26a35142e0ed1458158435b252a0fcd659f93c4
 * @link https://github.com/ampproject/amppackager/blob/b26a35142e0ed1458158435b252a0fcd659f93c4/transformer/transformers/serversiderendering.go
 *
 * @package Amp\Optimizer
 */
final class TransformedIdentifier implements Transformer, Configurable
{

    const TRANSFORMED_KEY    = 'transformed';
    const TRANSFORMED_ORIGIN = 'self';

    const CONFIG_KEY_VERSION = 'version';

    /**
     * Associative array of configuration values.
     *
     * @var array
     */
    private $configuration;

    /**
     * Instantiate a TransformedIdentifier object.
     *
     * @param array $configuration Optional. Associative array of configuration values.
     */
    public function __construct($configuration = [])
    {
        $this->configuration = $configuration;
    }

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        $document->html->setAttribute(self::TRANSFORMED_KEY, $this->getOrigin());
    }

    /**
     * Get the origin that transformed the AMP document.
     *
     * @return string Origin of the transformation.
     */
    private function getOrigin()
    {
        $version = isset($this->configuration[self::CONFIG_KEY_VERSION]) ? (int)$this->configuration[self::CONFIG_KEY_VERSION] : 1;
        $origin  = self::TRANSFORMED_ORIGIN;

        if ($version > 0) {
            $origin .= ";v={$version}";
        }

        return $origin;
    }
}
