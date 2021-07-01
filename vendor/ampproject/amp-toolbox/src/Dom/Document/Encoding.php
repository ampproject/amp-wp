<?php

namespace AmpProject\Dom\Document;

/**
 * Encoding constants that are used to control Dom\Document encoding.
 *
 * @package ampproject/amp-toolbox
 */
interface Encoding
{

    /**
     * AMP requires the HTML markup to be encoded in UTF-8.
     *
     * @var string
     */
    const AMP = 'utf-8';

    /**
     * Encoding detection order in case we have to guess.
     *
     * This list of encoding detection order is just a wild guess and might need fine-tuning over time.
     * If the charset was not provided explicitly, we can really only guess, as the detection can
     * never be 100% accurate and reliable.
     *
     * @var string
     */
    const DETECTION_ORDER = 'UTF-8, EUC-JP, eucJP-win, JIS, ISO-2022-JP, ISO-8859-15, ISO-8859-1, ASCII';

    /**
     * Associative array of encoding mappings.
     *
     * Translates HTML charsets into encodings PHP can understand.
     *
     * @var string[]
     */
    const MAPPINGS = [
        // Assume ISO-8859-1 for some charsets.
        'latin-1' => 'ISO-8859-1',
        // US-ASCII is one of the most popular ASCII names and used as HTML charset,
        // but mb_list_encodings contains only "ASCII".
        'us-ascii' => 'ascii',
    ];

    /**
     * Encoding identifier to use for an unknown encoding.
     *
     * "auto" is recognized by mb_convert_encoding() as a special value.
     *
     * @var string
     */
    const UNKNOWN = 'auto';
}
