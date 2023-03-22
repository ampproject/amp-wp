<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document;
use AmpProject\Dom\Document\AfterLoadFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Dom\Document\Option;
use AmpProject\Dom\Options;

/**
 * Filter for adapting the Libxml error behavior and options.
 *
 * @package ampproject/amp-toolbox
 */
final class LibxmlCompatibility implements BeforeLoadFilter, AfterLoadFilter
{
    /**
     * Options instance to use.
     *
     * @var Options
     */
    private $options;

    /**
     * Store the previous state fo the libxml "use internal errors" setting.
     *
     * @var bool
     */
    private $libxmlPreviousState;

    /**
     * LibxmlCompatibility constructor.
     *
     * @param Options $options Options instance to use.
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * Preprocess the HTML to be loaded into the Dom\Document.
     *
     * @param string $html String of HTML markup to be preprocessed.
     * @return string Preprocessed string of HTML markup.
     */
    public function beforeLoad($html)
    {
        $this->libxmlPreviousState = libxml_use_internal_errors(true);

        $this->options[Option::LIBXML_FLAGS] |= LIBXML_COMPACT;

        /*
         * LIBXML_HTML_NODEFDTD is only available for libxml 2.7.8+.
         * This should be the case for PHP 5.4+, but some systems seem to compile against a custom libxml version that
         * is lower than expected.
         */
        if (defined('LIBXML_HTML_NODEFDTD')) {
            $this->options[Option::LIBXML_FLAGS] |= constant('LIBXML_HTML_NODEFDTD');
        }

        /**
         * This flag prevents removing the closing tags used in inline JavaScript variables.
         */
        if (defined('LIBXML_SCHEMA_CREATE')) {
            $this->options[Option::LIBXML_FLAGS] |= constant('LIBXML_SCHEMA_CREATE');
        }

        return $html;
    }

    /**
     * Process the Document after the html loaded into the Dom\Document.
     *
     * @param Document $document Document to be processed.
     */
    public function afterLoad(Document $document)
    {
        libxml_clear_errors();
        libxml_use_internal_errors($this->libxmlPreviousState);
    }
}
