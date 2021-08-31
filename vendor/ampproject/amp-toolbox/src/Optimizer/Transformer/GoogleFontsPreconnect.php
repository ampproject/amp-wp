<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Transformer;

/**
 * Transformer adding resource hints to preconnect to the Google Fonts domain.
 *
 * @package ampproject/amp-toolbox
 */
final class GoogleFontsPreconnect implements Transformer
{

    /**
     * Domain that the Google Fonts static files are loaded from.
     *
     * @var string
     */
    const GOOGLE_FONTS_STATIC_ORIGIN = 'https://fonts.gstatic.com';

    /**
     * Domain that the Google Fonts API is accepting requests from.
     *
     * @var string
     */
    const GOOGLE_FONTS_API_BASE_URL = 'https://fonts.googleapis.com/';

    /**
     * XPath query to fetch links pointing to the Google Fonts API.
     *
     * @var string
     */
    const XPATH_GOOGLE_FONTS_API_QUERY = './/link[starts-with(@href, "' . self::GOOGLE_FONTS_API_BASE_URL . '")]';

    /**
     * Apply transformations to the provided DOM document.
     *
     * @param Document        $document DOM document to apply the transformations to.
     * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
     * @return void
     */
    public function transform(Document $document, ErrorCollection $errors)
    {
        if ($this->usesGoogleFonts($document)) {
            $document->links->addPreconnect(self::GOOGLE_FONTS_STATIC_ORIGIN);
        }
    }

    /**
     * Check whether the document uses Google Fonts.
     *
     * @param Document $document Document to check for Google Fonts.
     * @return boolean Whether the provided document uses Google Fonts.
     */
    private function usesGoogleFonts(Document $document)
    {
        $links = $document->xpath->query(
            self::XPATH_GOOGLE_FONTS_API_QUERY,
            $document->head
        );

        return $links->length > 0;
    }
}
