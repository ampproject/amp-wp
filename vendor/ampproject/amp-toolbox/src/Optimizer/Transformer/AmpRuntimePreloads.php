<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Html\RequestDestination;

/**
 * Transformer adding resource hints to preload the AMP runtime script and CSS.
 *
 * @package ampproject/amp-toolbox
 */
final class AmpRuntimePreloads implements Transformer
{
    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        if ($this->isAmpRuntimeScriptNeeded($document)) {
            $document->links->addPreload($this->getAmpRuntimeScriptHost(), RequestDestination::SCRIPT);
        }

        if ($this->isAmpRuntimeCssNeeded($document)) {
            $document->links->addPreload($this->getAmpRuntimeCssHost(), RequestDestination::STYLE);
        }
    }

    /**
     * Check whether the AMP runtime script is needed.
     *
     * @param Document $document Document to check in.
     * @return bool Whether the AMP runtime script is needed.
     */
    private function isAmpRuntimeScriptNeeded(Document $document)
    {
        // TODO: What condition should be used here... same as isAmpRuntimeCssNeeded()?
        return false;
    }

    /**
     * Get the host domain of the AMP runtime script to preload.
     *
     * @return string AMP runtime script host.
     */
    private function getAmpRuntimeScriptHost()
    {
        return Amp::CACHE_HOST . '/v0.js';
    }

    /**
     * Check whether the AMP runtime CSS is needed.
     *
     * If the document was serverside-rendered, it means the AMP Runtime CSS was inlined. In that case, we don't need to
     * preload the CSS, as it is of low priority, with a very low probability to have a meaningful impact at all.
     *
     * @param Document $document Document to check in.
     * @return bool Whether the AMP runtime CSS is needed.
     */
    private function isAmpRuntimeCssNeeded(Document $document)
    {
        $ampRuntimeStyle = $document->xpath
            ->query(AmpRuntimeCss::AMP_RUNTIME_STYLE_XPATH, $document->head)
            ->item(0);

        return empty($ampRuntimeStyle);
    }

    /**
     * Get the host domain of the AMP runtime CSS to preload.
     *
     * @return string AMP runtime CSS host.
     */
    private function getAmpRuntimeCssHost()
    {
        return Amp::CACHE_HOST . '/v0.css';
    }
}
