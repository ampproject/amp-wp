<?php

namespace AmpProject;

/**
 * Encoding constants that are used to control Dom\Document encoding.
 *
 * @package ampproject/amp-toolbox
 */
final class Encoding
{
    /**
     * UTF-8 encoding, which is the fallback.
     *
     * @var string
     */
    const UTF8 = 'utf-8';

    /**
     * AMP requires the HTML markup to be encoded in UTF-8.
     *
     * @var string
     */
    const AMP = self::UTF8;

    /**
     * Encoding detection order in case we have to guess.
     *
     * This list of encoding detection order is just a wild guess and might need fine-tuning over time.
     * If the charset was not provided explicitly, we can really only guess, as the detection can
     * never be 100% accurate and reliable.
     *
     * @var string
     */
    const DETECTION_ORDER = 'JIS, UTF-8, EUC-JP, eucJP-win, ISO-2022-JP, ISO-8859-15, ISO-8859-1, ASCII';

    /**
     * Encoding detection order for PHP 8.1.
     *
     * In PHP 8.1, mb_detect_encoding gives different result than the lower versions. This alternative detection order
     * list fixes this issue.
     */
    const DETECTION_ORDER_PHP81 = 'UTF-8, EUC-JP, eucJP-win, ISO-8859-15, JIS, ISO-2022-JP, ISO-8859-1, ASCII';

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

    /**
     * Current value of the encoding.
     *
     * @var string
     */
    private $encoding;

    /**
     * Encoding constructor.
     *
     * @param mixed $encoding Value of the encoding.
     */
    public function __construct($encoding)
    {
        if (! is_string($encoding)) {
            $encoding = self::UNKNOWN;
        }

        $this->encoding = $encoding;
    }

    /**
     * Check whether the encoding equals a provided encoding.
     *
     * @param Encoding|string $encoding Encoding to check against.
     * @return bool Whether the encodings are the same.
     */
    public function equals($encoding)
    {
        return strtolower($this->encoding) === strtolower((string)$encoding);
    }

    /**
     * Sanitize the encoding that was detected.
     *
     * If sanitization fails, it will return 'auto', letting the conversion
     * logic try to figure it out itself.
     */
    public function sanitize()
    {
        $this->encoding = strtolower($this->encoding);

        if ($this->encoding === self::UTF8) {
            return;
        }

        if (! function_exists('mb_list_encodings')) {
            return;
        }

        static $knownEncodings = null;

        if (null === $knownEncodings) {
            $knownEncodings = array_map('strtolower', mb_list_encodings());
        }

        if (array_key_exists($this->encoding, self::MAPPINGS)) {
            $this->encoding = self::MAPPINGS[$this->encoding];
        }

        if (! in_array($this->encoding, $knownEncodings, true)) {
            $this->encoding = self::UNKNOWN;
        }
    }

    /**
     * Return the value of the encoding as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->encoding;
    }
}
