<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Extension;
use AmpProject\Optimizer\Configuration\OptimizeHeroImagesConfiguration;
use AmpProject\Optimizer\Configuration\PreloadHeroImageConfiguration;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;
use AmpProject\Optimizer\TransformerConfiguration;

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
 * @deprecated since version 0.6.0
 * @see \AmpProject\Optimizer\Transformer\OptimizeHeroImages
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
    const NOSCRIPT_IMG_XPATH_QUERY = './noscript/img';

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
        $errors->add(Error\DeprecatedTransformer::withReplacement(self::class, OptimizeHeroImages::class));

        if ($this->configuration->get(PreloadHeroImageConfiguration::PRELOAD_HERO_IMAGE) === false) {
            return;
        }

        $inlineStyleBackupAttribute = $this->configuration->get(
            PreloadHeroImageConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE
        );

        $preloadSrcset = $this->configuration->get(PreloadHeroImageConfiguration::PRELOAD_SRCSET);

        $configuration = new OptimizeHeroImagesConfiguration(
            [
                OptimizeHeroImagesConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE => $inlineStyleBackupAttribute,
                OptimizeHeroImagesConfiguration::PRELOAD_SRCSET                => $preloadSrcset,
            ]
        );

        $transformer = new OptimizeHeroImages($configuration);
        $transformer->transform($document, $errors);
    }
}
