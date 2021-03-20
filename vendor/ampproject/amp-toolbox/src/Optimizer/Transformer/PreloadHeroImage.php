<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Element;
use AmpProject\Extension;
use AmpProject\Layout;
use AmpProject\Optimizer\Configuration\PreloadHeroImageConfiguration;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\HeroImage;
use AmpProject\Optimizer\ImageDimensions;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\RequestDestination;
use AmpProject\Tag;
use AmpProject\Url;
use DOMNode;

/**
 * PreloadHeroImage - this transformer optimizes image rendering times for hero images. For hero images it will:
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
 * @package ampproject/amp-toolbox
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
        Attribute::REFERRERPOLICY,
        Attribute::SRC,
        Attribute::SRCSET,
        Attribute::SIZES,
        Attribute::TITLE,
    ];

    /**
     * List of attributes to inline onto an SSR'ed image.
     *
     * @var string[]
     */
    const ATTRIBUTES_TO_INLINE = [
        Attribute::OBJECT_FIT,
        Attribute::OBJECT_POSITION,
    ];

    /**
     * Maximum number of hero images defined via data-hero attribute.
     *
     * @var int
     */
    const DATA_HERO_MAX = 2;

    /**
     * List of AMP elements that are an embed that can have a placeholder.
     *
     * The array has values assigned so that we can do a fast hash lookup on the element name.
     *
     * @var bool[]
     */
    const AMP_EMBEDS = [
        Extension::AD            => true,
        Extension::ANIM          => true,
        Extension::BRIGHTCOVE    => true,
        Extension::DAILYMOTION   => true,
        Extension::FACEBOOK      => true,
        Extension::GFYCAT        => true,
        Extension::IFRAME        => true,
        Extension::IMGUR         => true,
        Extension::INSTAGRAM     => true,
        Extension::PINTEREST     => true,
        Extension::REDDIT        => true,
        Extension::TWITTER       => true,
        Extension::VIDEO         => true,
        Extension::VIDEO_IFRAME  => true,
        Extension::VIMEO         => true,
        Extension::WISTIA_PLAYER => true,
        Extension::YOUTUBE       => true,
    ];

    /**
     * XPath query to relatively fetch all noscript > img elements.
     *
     * @var string
     */
    const NOSCRIPT_IMG_XPATH_QUERY = './/noscript[ img ]';

    /**
     * Regular expression pattern to extract the URL from a CSS background-image property.
     *
     * @var string
     */
    const CSS_BACKGROUND_IMAGE_URL_REGEX_PATTERN = '/background-image\s*:\s*url\(\s*(?<url>[^)]*\s*)/i';

    /**
     * Configuration store to use.
     *
     * @var TransformerConfiguration
     */
    private $configuration;

    /**
     * Reference node to attach preload links to.
     *
     * @var Element|null
     */
    private $preloadReferenceNode;

    /**
     * Inline style backup attribute that stores inline styles that are being moved to <style amp-custom>.
     *
     * An empty string signifies that no inline style backup is available.
     *
     * @var string
     */
    private $inlineStyleBackupAttribute;

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

        $this->inlineStyleBackupAttribute = $this->configuration->get(
            PreloadHeroImageConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE
        );

        $heroImages     = $this->findHeroImages($document);
        $heroImageCount = count($heroImages);
        if ($heroImageCount > self::DATA_HERO_MAX) {
            $errors->add(Error\TooManyHeroImages::whenPastMaximum());
            $heroImageCount = self::DATA_HERO_MAX;
        }

        for ($index = 0; $index < $heroImageCount; $index++) {
            $this->generatePreload($heroImages[$index], $document, $errors);
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
        $heroImages                = [];
        $heroImageCandidates       = [];
        $heroImageFallbacks        = [];
        $previousHeroImageFallback = null;
        $node                      = $document->body;

        while ($node !== null) {
            if (! $node instanceof Element) {
                $node = $this->nextNode($node);
                continue;
            }

            $heroImage = $this->detectImageWithAttribute($node, Attribute::DATA_HERO);
            if ($heroImage) {
                $heroImages[] = $heroImage;
            } elseif (count($heroImageCandidates) < self::DATA_HERO_MAX) {
                $heroImageCandidate = $this->detectImageWithAttribute($node, Attribute::DATA_HERO_CANDIDATE);
                if ($heroImageCandidate) {
                    $heroImageCandidates[] = $heroImageCandidate;
                } elseif (count($heroImageFallbacks) < self::DATA_HERO_MAX) {
                    $heroImageFallback = $this->detectPossibleHeroImageFallbacks($node);

                    // Ensure we don't flag the same image twice. This can happen for placeholder images, which are
                    // flagged on their own and as their parent's placeholder.
                    if (
                        $heroImageFallback
                        && (
                            ! $previousHeroImageFallback
                            || $heroImageFallback->getAmpImg() !== $previousHeroImageFallback->getAmpImg()
                        )
                    ) {
                        $heroImageFallbacks[]      = $heroImageFallback;
                        $previousHeroImageFallback = $heroImageFallback;
                    }
                }
            }

            if (Amp::isTemplate($node)) {
                // Ignore images inside templates.
                $node = $this->skipNodeAndChildren($node);
            } else {
                $node = $this->nextNode($node);
            }
        }

        if (count($heroImages) > 0) {
            return $heroImages;
        }

        while (count($heroImages) < self::DATA_HERO_MAX && count($heroImageCandidates) > 0) {
            $heroImages[] = array_shift($heroImageCandidates);
        }

        if (count($heroImages) < 1 && count($heroImageFallbacks) > 0) {
            $heroImages[] = array_shift($heroImageFallbacks);
        }

        return $heroImages;
    }

    /**
     * Detect a hero image with a specific attribute.
     *
     * This is used for detecting an image marked with data-hero or data-hero-candidate
     *
     * @param Element $element   Element to detect for.
     * @param string  $attribute Attribute to look for.
     * @return HeroImage|null Detected hero image, or null if none detected.
     */
    private function detectImageWithAttribute(Element $element, $attribute)
    {
        if (!$element->hasAttribute($attribute)) {
            return null;
        }

        $src = $element->getAttribute(Attribute::SRC);
        if ($element->tagName === Extension::IMG && Url::isValidNonDataUrl($src)) {
            return new HeroImage(
                $src,
                $element->getAttribute(Attribute::MEDIA),
                $element->getAttribute(Attribute::SRCSET),
                $element
            );
        }

        if ($this->isAmpEmbed($element)) {
            $placeholderImage = $this->getPlaceholderImage($element);
            if (null !== $placeholderImage) {
                return $placeholderImage;
            }
        }

        $cssBackgroundImage = $this->getCssBackgroundImageUrl($element);

        if (Url::isValidNonDataUrl($cssBackgroundImage)) {
            return new HeroImage(
                $cssBackgroundImage,
                $element->getAttribute(Attribute::MEDIA),
                $element->getAttribute(Attribute::SRCSET),
                $element
            );
        }

        return null;
    }

    /**
     * Detect a possible hero image fallback.
     *
     * The hero image here can come from one of <amp-img>, <amp-video>, <amp-iframe>, <amp-video-iframe>.
     *
     * @param Element $element Element to detect for.
     * @return HeroImage|null Detected hero image fallback, or null if none detected.
     */
    private function detectPossibleHeroImageFallbacks(Element $element)
    {
        if (
            $element->hasAttribute(Attribute::LAYOUT)
            && $element->getAttribute(Attribute::LAYOUT) === Layout::NODISPLAY
        ) {
            return null;
        }

        if ($element->tagName === Extension::IMG || $element->tagName === Tag::IMG) {
            return $this->detectPossibleHeroImageFallbackForAmpImg($element);
        }

        if ($element->tagName === Extension::VIDEO) {
            return $this->detectPossibleHeroImageFallbackForPosterImage($element);
        }

        if ($this->isAmpEmbed($element)) {
            return $this->detectPossibleHeroImageFallbackForPlaceholderImage($element);
        }

        return null;
    }

    /**
     * Detect a possible hero image fallback from an <amp-img> element.
     *
     * @param Element $element Element to detect for.
     * @return HeroImage|null Detected hero image fallback, or null if none detected.
     */
    private function detectPossibleHeroImageFallbackForAmpImg(Element $element)
    {
        $src = $element->getAttribute(Attribute::SRC);

        if (empty($src)) {
            return null;
        }

        if (! Url::isValidNonDataUrl($src)) {
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
     * Detect a possible hero image fallback from a video's poster (= placeholder) image.
     *
     * @param Element $element Element to detect for.
     * @return HeroImage|null Detected hero image fallback, or null if none detected.
     */
    private function detectPossibleHeroImageFallbackForPosterImage(Element $element)
    {
        $poster = $element->getAttribute(Attribute::POSTER);

        if (! $poster) {
            return null;
        }

        if (! Url::isValidNonDataUrl($poster)) {
            return null;
        }

        if ((new ImageDimensions($element))->isTiny()) {
            return null;
        }

        $media = $element->getAttribute(Attribute::MEDIA);

        return new HeroImage($poster, $media, '');
    }

    /**
     * Detect a possible hero image fallback from a placeholder image.
     *
     * @param Element $element Element to detect for.
     * @return HeroImage|null Detected hero image fallback, or null if none detected.
     */
    private function detectPossibleHeroImageFallbackForPlaceholderImage(Element $element)
    {
        // The placeholder will be a child node of the element.
        if (! $element->hasChildNodes()) {
            return null;
        }

        // Don't bother if the element is too small.
        if ((new ImageDimensions($element))->isTiny()) {
            return null;
        }

        return $this->getPlaceholderImage($element);
    }

    /**
     * Get the placeholder image for a given element.
     *
     * @param Element $element Element to check the placeholder image for.
     * @return HeroImage|null Placeholder image to use or null if none found.
     */
    private function getPlaceholderImage(Element $element)
    {
        foreach ($element->childNodes as $childNode) {
            if (
                ! $childNode instanceof Element
                || ! $childNode->hasAttribute(Attribute::PLACEHOLDER)
            ) {
                continue;
            }

            $placeholder = $childNode;

            while ($placeholder !== null) {
                if (! $placeholder instanceof Element) {
                    $placeholder = $this->nextNode($placeholder);
                    continue;
                }

                if (
                    $placeholder->tagName === Extension::IMG
                    || $placeholder->tagName === Tag::IMG
                ) {
                    // Found valid candidate for placeholder image.
                    break;
                }

                if (Amp::isTemplate($placeholder)) {
                    // Ignore images inside templates.
                    $placeholder = $this->skipNodeAndChildren($placeholder);
                } else {
                    $placeholder = $this->nextNode($placeholder);
                }
            }

            if (!$placeholder instanceof Element) {
                break;
            }

            $src = $placeholder->getAttribute(Attribute::SRC);

            if (! Url::isValidNonDataUrl($src)) {
                break;
            }

            return new HeroImage(
                $src,
                $element->getAttribute(Attribute::MEDIA),
                $placeholder->getAttribute(Attribute::SRCSET),
                $placeholder
            );
        }

        return null;
    }

    /**
     * Generate the preload link for a given hero image.
     *
     * @param HeroImage       $heroImage Hero image to generate the preload link for.
     * @param Document        $document  Document to generate the preload link in.
     * @param ErrorCollection $errors    Collection of errors that are collected during transformation.
     */
    private function generatePreload(
        HeroImage $heroImage,
        Document $document,
        ErrorCollection $errors
    ) {
        if ($heroImage->getSrcset() && ! $this->supportsSrcset()) {
            $errors->add(Error\CannotPreloadImage::fromImageWithSrcsetAttribute($heroImage->getAmpImg()));
            return;
        }

        $img = $heroImage->getAmpImg();

        if ($img && $img->getAttribute(Attribute::LOADING) === 'lazy') {
            $img->removeAttribute(Attribute::LOADING);
        }

        if ($this->hasExistingImagePreload($document, $heroImage->getSrc())) {
            return;
        }

        if ($this->preloadReferenceNode === null) {
            $this->preloadReferenceNode = $document->viewport;
        }

        $preload = $document->createElement(Tag::LINK);
        $preload->setAttribute(Attribute::REL, Attribute::REL_PRELOAD);
        $preload->setAttribute(Attribute::HREF, $heroImage->getSrc());
        $preload->setAttribute(Attribute::AS_, RequestDestination::IMAGE);
        $preload->appendChild($document->createAttribute(Attribute::DATA_HERO));
        if ($heroImage->getSrcset()) {
            $preload->setAttribute(Attribute::IMAGESRCSET, $heroImage->getSrcset());
            if ($img && $img->hasAttribute(Attribute::SIZES)) {
                $preload->setAttribute(Attribute::IMAGESIZES, $img->getAttribute(Attribute::SIZES));
            }
        }

        if (empty($heroImage->getMedia())) {
            // We can only safely preload a hero image if there's a media attribute
            // as we can't detect whether it's hidden on certain viewport sizes otherwise.
            return;
        }

        $preload->setAttribute(Attribute::MEDIA, $heroImage->getMedia());

        if ($this->preloadReferenceNode) {
            $this->preloadReferenceNode->parentNode->insertBefore(
                $preload,
                $this->preloadReferenceNode->nextSibling
            );
        } else {
            $document->head->appendChild($preload);
        }

        $this->preloadReferenceNode = $preload;
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

        if (! $element || $element->tagName !== Extension::IMG) {
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

        foreach (self::ATTRIBUTES_TO_INLINE as $attribute) {
            if ($element->hasAttribute($attribute)) {
                $value = $element->getAttribute($attribute);
                $style = empty($value) ? '' : "{$attribute}:{$element->getAttribute($attribute)}";
                $imgElement->addInlineStyle($style);
            }
        }

        $element->appendChild($document->createAttribute(Attribute::I_AMPHTML_SSR));
        $element->appendChild($document->createAttribute(Attribute::DATA_HERO));

        $element->appendChild($imgElement);

        // Remove any noscript>img when an amp-img is pre-rendered.
        $noscript = $document->xpath->query(self::NOSCRIPT_IMG_XPATH_QUERY, $element)->item(0);
        if ($noscript instanceof Element) {
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
            if (! $node instanceof Element) {
                continue;
            }

            if ($node->getAttribute(Attribute::REL) !== Attribute::REL_PRELOAD) {
                continue;
            }

            if ($node->getAttribute(Attribute::AS_) !== RequestDestination::IMAGE) {
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

    /**
     * Check whether a given element is an AMP embed.
     *
     * @param Element $element Element to check.
     * @return bool Whether the given element is an AMP embed.
     */
    private function isAmpEmbed(Element $element)
    {
        return array_key_exists($element->tagName, self::AMP_EMBEDS);
    }

    /**
     * Get the URL of the CSS background-image property.
     *
     * This falls back to the data-amp-original-style attribute if the inline
     * style was already extracted by the CSS tree-shaking.
     *
     * @param Element $element
     * @return string URL of the background image, or an empty string if not found.
     */
    private function getCssBackgroundImageUrl(Element $element)
    {
        $matches = [];

        if (
            preg_match(
                self::CSS_BACKGROUND_IMAGE_URL_REGEX_PATTERN,
                $element->getAttribute(Attribute::STYLE),
                $matches
            )
        ) {
            return trim($matches['url'], '\'" ');
        }

        if (
            !empty($this->inlineStyleBackupAttribute)
            && preg_match(
                self::CSS_BACKGROUND_IMAGE_URL_REGEX_PATTERN,
                $element->getAttribute($this->inlineStyleBackupAttribute),
                $matches
            )
        ) {
            return trim($matches['url'], '\'" ');
        }

        return '';
    }

    /**
     * Whether srcset preloading is supported.
     *
     * @return bool
     */
    private function supportsSrcset()
    {
        return $this->configuration->get(PreloadHeroImageConfiguration::PRELOAD_SRCSET);
    }
}
