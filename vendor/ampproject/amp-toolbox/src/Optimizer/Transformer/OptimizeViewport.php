<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Html\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Optimizer\Configuration\OptimizeViewportConfiguration;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\Html\Tag;

/**
 * OptimizeViewport - Transformer that normalizes and optimizes the viewport meta tag.
 *
 * This transformer will:
 * * default to 'width=device-width' when the viewport is missing, which is the bare minimum that AMP requires;
 * * extract properties from multiple viewport tags and merge them into a single tag;
 * * remove the initial-scale=1 attribute if applicable to avoid unnecessary tap delay.
 *
 * @package ampproject/amp-toolbox
 */
final class OptimizeViewport implements Transformer
{
    /**
     * Viewport settings to use for AMP markup.
     *
     * @var string
     */
    const AMP_VIEWPORT = 'width=device-width';

    /**
     * The viewport content property that controls the zoom level when the page is first loaded.
     *
     * @var string
     */
    const INITIAL_SCALE = 'initial-scale';

    /**
     * Xpath query to fetch the viewport meta tags.
     *
     * This transformer does not make use of the `Dom\Document::$viewport` helper, as it needs to
     * deal properly with multiple viewport tags as well.
     *
     * @var string
     */
    const XPATH_QUERY = './/meta[@name="viewport"]';

    /**
     * Configuration store to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * Instantiate a MinifyHtml object.
     *
     * @param TransformerConfiguration $configuration Configuration store to use.
     */
    public function __construct(TransformerConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        $viewport = '';

        $metaTags = $document->xpath->query(self::XPATH_QUERY);

        if (! $metaTags->length) {
            $viewport = self::AMP_VIEWPORT;
        } else {
            // Merge one or more meta[name=viewport] tags into one.
            $parsedRules = [];

            foreach ($metaTags as $metaTag) {
                $propertyPairs = explode(',', $metaTag->getAttribute('content'));

                foreach ($propertyPairs as $propertyPair) {
                    $explodedPair = explode('=', $propertyPair, 2);
                    if (isset($explodedPair[1])) {
                        $parsedRules[ trim($explodedPair[0]) ] = trim($explodedPair[1]);
                    }
                }

                $metaTag->parentNode->removeChild($metaTag);
            }

            // Remove initial-scale=1 to leave just width=device-width in order to avoid a tap delay that hurts FID.
            if (
                $this->configuration->get(OptimizeViewportConfiguration::REMOVE_INITIAL_SCALE_VIEWPORT_PROPERTY)
                && isset($parsedRules[self::INITIAL_SCALE])
                && abs((float) $parsedRules[self::INITIAL_SCALE] - 1.0) < 0.0001
            ) {
                unset($parsedRules[self::INITIAL_SCALE]);
            }

            $viewport = implode(
                ',',
                array_map(
                    static function ($ruleName) use ($parsedRules) {
                        return "{$ruleName}={$parsedRules[$ruleName]}";
                    },
                    array_keys($parsedRules)
                )
            );
        }

        $element = $this->createViewportElement($document, $viewport);
        $document->head->appendChild($element);
    }

    /**
     * Create a new meta tag for the viewport setting.
     *
     * @param Document $document DOM document to apply the transformations to.
     * @param string   $viewport Viewport setting to use.
     * @return Element New meta tag with requested viewport setting.
     */
    protected function createViewportElement(Document $document, $viewport)
    {
        $element = $document->createElement(Tag::META);
        $element->setAttribute(Attribute::NAME, Attribute::VIEWPORT);
        $element->setAttribute(Attribute::CONTENT, $viewport);

        return $element;
    }
}
