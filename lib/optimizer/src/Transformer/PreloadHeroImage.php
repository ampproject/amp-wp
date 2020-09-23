<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Extension;
use AmpProject\Layout;
use AmpProject\Optimizer\Configuration\PreloadHeroImageConfiguration;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\HeroImage;
use AmpProject\Optimizer\ImageDimensions;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\Tag;
use AmpProject\Url;
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
     * Class(es) to apply to a serverside-rendered image element.
     *
     * @var string
     */
    const SSR_IMAGE_CLASS = 'i-amphtml-fill-content i-amphtml-replaced-content';

    /**
     * List of attributes to copy onto an SSR'ed image.
     *
     * @var string[]
     */
    const ATTRIBUTES_TO_COPY = [
        Attribute::ALT,
        Attribute::ATTRIBUTION,
        Attribute::OBJECT_FIT,
        Attribute::OBJECT_POSITION,
        Attribute::REFERRERPOLICY,
        Attribute::SRC,
        Attribute::SRCSET,
        Attribute::SIZES,
        Attribute::TITLE,
    ];

    /**
     * Maximum number of hero images defined via data-hero attribute.
     *
     * @var int
     */
    const DATA_HERO_MAX = 2;

    /**
     * Configuration store to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * Instantiate a PreloadHeroImage object.
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
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        if ($this->configuration->get(PreloadHeroImageConfiguration::PRELOAD_HERO_IMAGE) === false) {
            return;
        }

        $heroImages    = $this->findHeroImages($document);
        $referenceNode = $document->viewport;

        $heroImageCount = count($heroImages);
        if ($heroImageCount > self::DATA_HERO_MAX) {
            $errors->add(Error\TooManyHeroImages::whenPastMaximum());
            $heroImageCount = self::DATA_HERO_MAX;
        }

        $isAmpStory = Amp::isAmpStory($document);

        for ($index = 0; $index < $heroImageCount; $index++) {
            $this->generatePreload($heroImages[$index], $document, $errors, $referenceNode);
            $this->generateImg($heroImages[$index], $document);
        }
    }

    /**
     * Find the hero images to optimize.
     *
     * @param Document $document Document to look for hero images in.
     * @return HeroImage[] Array of hero images to optimize.
     */
    private function findHeroImages(Document $document)
    {
        $heroImageCandidate = null;
        $heroImages         = [];
        $node               = $document->body;

        while ($node !== null) {
            if (! $node instanceof DOMElement) {
                $node = $this->nextNode($node);
                continue;
            }

            $heroImage = $this->detectImageWithDataHero($node);
            if ($heroImage) {
                $heroImages[] = $heroImage;
            }

            if (! $heroImageCandidate && count($heroImages) === 0) {
                $heroImageCandidate = $this->detectHeroImageCandidate($node);
            }
            if (Amp::isTemplate($node)) {
                // Ignore images inside templates.
                $node = $this->skipNodeAndChildren($node);
            } else {
                $node = $this->nextNode($node);
            }
        }

        // Optimize data-hero element if defined.
        if (count($heroImages) > 0) {
            return $heroImages;
        }

        // Fall back to auto-detected hero image if available.
        if ($heroImageCandidate) {
            return [$heroImageCandidate];
        }

        // No hero images to optimize.
        return [];
    }

    /**
     * Detect a hero image with the data-hero attribute.
     *
     * @param DOMElement $element Element to detect for.
     * @return HeroImage|null Detected hero image, or null if none detected.
     */
    private function detectImageWithDataHero(DOMElement $element)
    {
        if (
            $element->tagName === Extension::IMAGE
            && $element->hasAttribute(Attribute::DATA_HERO)
        ) {
            return new HeroImage(
                $element->getAttribute(Attribute::SRC),
                $element->getAttribute(Attribute::MEDIA),
                $element->getAttribute(Attribute::SRCSET),
                $element
            );
        }

        if (
            Amp::isAmpIframe($element)
            && $element->hasAttribute(Attribute::DATA_HERO)
        ) {
            return $this->getPlaceholderImage($element);
        }

        return null;
    }

    /**
     * Detect a hero image candidate.
     *
     * The hero image here can come from one of <amp-img>, <amp-video>, <amp-iframe>, <amp-video-iframe>.
     *
     * @param DOMElement $element Element to detect for.
     * @return HeroImage|null Detected hero image candidate, or null if none detected.
     */
    private function detectHeroImageCandidate(DOMElement $element)
    {
        if (
            $element->hasAttribute(Attribute::LAYOUT)
            && $element->getAttribute(Attribute::LAYOUT) === Layout::NODISPLAY
        ) {
            return null;
        }

        if ($element->tagName === Extension::IMAGE) {
            return $this->detectHeroImageCandidateForAmpImg($element);
        }

        if ($element->tagName === EXTENSION::VIDEO) {
            return $this->detectHeroImageCandidateForPosterImage($element);
        }

        if (Amp::isAmpIframe($element)) {
            return $this->detectHeroImageCandidateForIframePlaceholderImage($element);
        }

        return null;
    }

    /**
     * Detect a hero image candidate from an <amp-img> element.
     *
     * @param DOMElement $element Element to detect for.
     * @return HeroImage|null Detected hero image candidate, or null if none detected.
     */
    private function detectHeroImageCandidateForAmpImg(DOMElement $element)
    {
        $src = $element->getAttribute(Attribute::SRC);

        if (empty($src)) {
            return null;
        }

        if (! Url::isValidImageSrc($src)) {
            return null;
        }

        if ((new ImageDimensions($element))->isTiny()) {
            return null;
        }

        $srcset = $element->getAttribute(Attribute::SRCSET);
        $media  = $element->getAttribute(Attribute::MEDIA);

        return new HeroImage($src, $media, $srcset, $element);
    }

    /**
     * Detect a hero image candidate from a video's poster (= placeholder) image.
     *
     * @param DOMElement $element Element to detect for.
     * @return HeroImage|null Detected hero image candidate, or null if none detected.
     */
    private function detectHeroImageCandidateForPosterImage(DOMElement $element)
    {
        $poster = $element->getAttribute(Attribute::POSTER);

        if (! $poster) {
            return null;
        }

        if (! Url::isValidImageSrc($poster)) {
            return null;
        }

        if ((new ImageDimensions($element))->isTiny()) {
            return null;
        }

        $media = $element->getAttribute(Attribute::MEDIA);

        return new HeroImage($poster, $media, '');
    }

    /**
     * Detect a hero image candidate from an iframe's placeholder image.
     *
     * @param DOMElement $element Element to detect for.
     * @return HeroImage|null Detected hero image candidate, or null if none detected.
     */
    private function detectHeroImageCandidateForIframePlaceholderImage(DOMElement $element)
    {
        // A placeholder <amp-img> is required to preload an image for an iframe.
        if (! $element->hasChildNodes()) {
            return null;
        }

        if ((new ImageDimensions($element))->isTiny()) {
            return null;
        }

        return $this->getPlaceholderImage($element);
    }

    /**
     * Get the placeholder image for a given element.
     *
     * @param DOMElement $element Element to check the placeholder image for.
     * @return HeroImage|null Placeholder image to use or null if none found.
     */
    private function getPlaceholderImage(DOMElement $element)
    {
        foreach ($element->childNodes as $childNode) {
            if (
                ! $childNode instanceof DOMElement
                || $childNode->tagName !== Extension::IMAGE
                || ! $childNode->hasAttribute(Attribute::PLACEHOLDER)
            ) {
                continue;
            }

            $src = $childNode->getAttribute(Attribute::SRC);

            if (! Url::isValidImageSrc($src)) {
                continue;
            }

            return new HeroImage(
                $src,
                $childNode->getAttribute(Attribute::MEDIA),
                $childNode->getAttribute(Attribute::SRCSET),
                $childNode
            );
        }

        return null;
    }

    /**
     * Generate the preload link for a given hero image.
     *
     * @param HeroImage       $heroImage     Hero image to generate the preload link for.
     * @param Document        $document      Document to generate the preload link in.
     * @param ErrorCollection $errors        Collection of errors that are collected during transformation.
     * @param DOMNode|null    $referenceNode Reference node after which to insert the preload link. Null if none.
     */
    private function generatePreload(
        HeroImage $heroImage,
        Document $document,
        ErrorCollection $errors,
        DOMNode $referenceNode = null
    ) {
        if ($heroImage->getSrcset()) {
            $errors->add(Error\CannotPreloadImage::fromImageWithSrcsetAttribute($heroImage->getAmpImg()));
            return;
        }

        if ($this->hasExistingImagePreload($document, $heroImage->getSrc())) {
            return;
        }

        $preload = $document->createElement(Tag::LINK);
        $preload->setAttribute(Attribute::REL, Attribute::REL_PRELOAD);
        $preload->setAttribute(Attribute::HREF, $heroImage->getSrc());
        $preload->setAttribute('as', 'image');
        $preload->setAttribute(Attribute::DATA_HERO, null);

        if ($heroImage->getMedia()) {
            $preload->setAttribute(Attribute::MEDIA, $heroImage->getMedia());
        }

        if ($referenceNode) {
            $referenceNode->parentNode->insertBefore($preload, $referenceNode->nextSibling);
        } else {
            $document->head->appendChild($preload);
        }
    }

    /**
     * Generate the SSR image element for the hero image.
     *
     * @param HeroImage $heroImage Hero image to generate the SSR image element for.
     * @param Document  $document  Document in which to generate the SSR image element in.
     */
    private function generateImg(HeroImage $heroImage, Document $document)
    {
        $element = $heroImage->getAmpImg();

        if (! $element) {
            return;
        }

        $imgElement = $document->createElement(Tag::IMG);
        $imgElement->setAttribute(Attribute::CLASS_, self::SSR_IMAGE_CLASS);
        $imgElement->setAttribute(Attribute::DECODING, 'async');

        foreach (self::ATTRIBUTES_TO_COPY as $attribute) {
            if ($element->hasAttribute($attribute)) {
                $imgElement->setAttribute($attribute, $element->getAttribute($attribute));
            }
        }

        $element->setAttribute(Attribute::I_AMPHTML_SSR, null);
        $element->setAttribute(Attribute::DATA_HERO, null);

        $element->appendChild($imgElement);

        // Remove any noscript>img when an amp-img is pre-rendered.
        $noscript = $document->xpath->query('./noscript[ img ]', $element)->item(0);
        if ($noscript instanceof DOMElement) {
            $noscript->parentNode->removeChild($noscript);
        }
    }

    /**
     * Check whether an existing preload link exists for a given src.
     *
     * @param Document $document Document in which to check for an existing preload.
     * @param string   $src      Preload URL to look for.
     * @return bool Whether an existing preload already exists.
     */
    private function hasExistingImagePreload(Document $document, $src)
    {
        foreach ($document->head->childNodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            if ($node->getAttribute(Attribute::REL) !== Attribute::REL_PRELOAD) {
                continue;
            }

            if ($node->getAttribute('as') !== 'image') {
                continue;
            }

            if ($node->getAttribute(Attribute::HREF) === $src) {
                return true;
            }
        }

        return false;
    }

    /**
     * Depth-first walk through the DOM tree.
     *
     * @param DOMNode $node Node to start walking from.
     * @return DOMNode|null Next node, or null if none found.
     */
    private function nextNode(DOMNode $node)
    {
        // Walk downwards if there are children.
        if ($node->firstChild) {
            return $node->firstChild;
        }

        // Return direct sibling or walk upwards until we find a node with a sibling.
        while ($node) {
            if ($node->nextSibling) {
                return $node->nextSibling;
            }

            $node = $node->parentNode;
        }

        // Out of nodes, so we're done.
        return null;
    }

    /**
     * Skip the subtree that is descending from the provided node.
     *
     * @param DOMNode $node Node to skip the subtree of.
     * @return DOMNode|null The appropriate "next" node that will skip the current subtree, null if none found.
     */
    private function skipNodeAndChildren(DOMNode $node)
    {
        if ($node->nextSibling) {
            return $node->nextSibling;
        }

        return $this->skipNodeAndChildren($node->parentNode);
    }
}
