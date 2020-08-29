<?php

namespace AmpProject\Optimizer;

use DOMElement;

/**
 * Representation of a hero image.
 *
 * This is used by the PreloadHeroImages transformer to store (potential) hero images to optimize.
 *
 * @package ampproject/optimizer
 */
final class HeroImage
{

    /**
     * <amp-img> element wrapping the actual hero image.
     *
     * @var DOMElement|null
     */
    private $ampImg;

    /**
     * Image src tag pointing to the image file.
     *
     * @var string
     */
    private $src;

    /**
     * Image media attribute.
     *
     * @var string
     */
    private $media;

    /**
     * Image srcset attribute.
     *
     * @var string
     */
    private $srcset;

    /**
     * HeroImage constructor.
     *
     * @param string          $src    Image src tag pointing to the image file.
     * @param string          $media  Image media attribute.
     * @param string          $srcset Image srcset attribute.
     * @param DOMElement|null $ampImg <amp-img> element wrapping the actual hero image, or null if none.
     */
    public function __construct($src, $media, $srcset, $ampImg = null)
    {
        $this->src    = $src;
        $this->media  = $media;
        $this->srcset = $srcset;
        $this->ampImg = $ampImg;
    }

    /**
     * Get the <amp-img> element wrapping the actual hero image.
     *
     * @return DOMElement|null <amp-img> element or null if none.
     */
    public function getAmpImg()
    {
        return $this->ampImg;
    }

    /**
     * Get the image src tag pointing to the image file.
     *
     * @return string Image src tag pointing to the image file.
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Get the image media attribute.
     *
     * @return string Image media attribute.
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Get the image srcset attribute.
     *
     * @return string Image srcset attribute.
     */
    public function getSrcset()
    {
        return $this->srcset;
    }
}
