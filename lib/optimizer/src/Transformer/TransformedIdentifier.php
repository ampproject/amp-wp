<?php

namespace Amp\Optimizer\Transformer;

use Amp\Dom\Document;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Transformer;

final class TransformedIdentifier implements Transformer
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
