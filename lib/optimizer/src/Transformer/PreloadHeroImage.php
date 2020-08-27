<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Tag;
use DOMElement;
use DOMNode;

/**
 * PreloadHeroImage - this transformers optimizes image rendering times for hero images. For hero images it will:
 *
 * 1. Inject a preload hint (if possible)
 * 2. Generate an img tag enabling the browser to render the image without the AMP runtime being loaded.
 *
 * Hero images are either identified automatically or can be explicitly defined by adding an `data-hero` attribute to
 * the element.
 *
 * This transformer supports the following options:
 *
 * * `preloadHeroImage`: [true|false] - enables or disables hero image preloading. The default is `true`.
 *
 * This is ported from the NodeJS optimizer.
 *
 * @version 3429af9d91e2c9efe1af85757499e5a308755f5f
 * @link    https://github.com/ampproject/amp-toolbox/blob/3429af9d91e2c9efe1af85757499e5a308755f5f/packages/optimizer/lib/transformers/PreloadHeroImage.js
 *
 * @package ampproject/optimizer
 */
final class PreloadHeroImage implements Transformer
{

    /**
     * Images smaller than 150px are considered tiny.
     *
     * @var int
     */
    const TINY_IMG_THRESHOLD = 150;

    /**
     * Maximum number of hero images defined via data-hero attribute.
     *
     * @var int
     */
    const DATA_HERO_MAX = 2;

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        $heroImages    = $this->findHeroImages($document);
        $referenceNode = $document->viewport;

        $heroImageCount = count($heroImages);
        if ($heroImageCount > self::DATA_HERO_MAX) {
            // TODO: Throw error.
            $heroImageCount = self::DATA_HERO_MAX;
        }

        $isAmpStory = Amp::isAmpStory($document);

        for ($index = 0; $index < $heroImageCount; $index++) {
            $this->generatePreload($heroImages[$index], $document, $referenceNode);
            if (! $isAmpStory) {
                // AMP Stories don't support SSR'd <amp-img> yet.
                // See https://github.com/ampproject/amphtml/issues/29850.
                $this->generateImg($heroImages[$index]);
            }
        }
    }

    private function findHeroImages(Document $document)
    {
        // TODO: Find the potential hero images.
        return [];
    }

    private function generatePreload(DOMElement $heroImage, Document $document, DOMNode $referenceNode = null)
    {
        if ($heroImage->hasAttribute(Attribute::SRCSET)) {
            // TODO: Throw error.
            return;
        }

        if ($this->hasExistingImagePreload($document->head, $heroImage->getAttribute(Attribute::SRC))) {
            return;
        }

        $preload = $document->createElement(Tag::LINK);
        $preload->setAttribute(Attribute::REL, Attribute::REL_PRELOAD);
        $preload->setAttribute(Attribute::HREF, $heroImage->getAttribute(Attribute::SRC));
        $preload->setAttribute('as', 'image');
        $preload->setAttribute(Attribute::DATA_HERO, null);

        if ($heroImage->hasAttribute(Attribute::MEDIA)) {
            $preload->setAttribute(Attribute::MEDIA, $heroImage->getAttribute(Attribute::MEDIA));
        }

        if ($referenceNode) {
            $referenceNode->parentNode->insertBefore($preload, $referenceNode->nextSibling);
        } else {
            $document->head->appendChild($preload);
        }
    }

    private function generateImg(DOMElement $heroImage)
    {
        // TODO: Actually generate the image.
    }

    private function hasExistingImagePreload()
    {
        // TODO: Check for existing preloads.
        return false;
    }
}
