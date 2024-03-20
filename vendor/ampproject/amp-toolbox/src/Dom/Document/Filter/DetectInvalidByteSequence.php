<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Dom\Document\Option;
use AmpProject\Dom\Options;
use AmpProject\Exception\InvalidByteSequence;

/**
 * Filter for checking if the markup contains invalid byte sequences.
 *
 * If invalid byte sequences are passed to `DOMDocument`, it fails silently and produces Mojibake.
 *
 * @package ampproject/amp-toolbox
 */
final class DetectInvalidByteSequence implements BeforeLoadFilter
{
    /**
     * Options instance to use.
     *
     * @var Options
     */
    private $options;

    /**
     * DetectInvalidByteSequence constructor.
     *
     * @param Options $options Options instance to use.
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * Check if the markup contains invalid byte sequences.
     *
     * @param string $html String of HTML markup to be preprocessed.
     * @return string Preprocessed string of HTML markup.
     */
    public function beforeLoad($html)
    {
        if (
            $this->options[Option::CHECK_ENCODING]
            && function_exists('mb_check_encoding')
            && ! mb_check_encoding($html)
        ) {
            throw InvalidByteSequence::forHtml();
        }

        return $html;
    }
}
