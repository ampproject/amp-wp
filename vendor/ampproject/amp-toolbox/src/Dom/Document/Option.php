<?php

namespace AmpProject\Dom\Document;

/**
 * Option constants that can be used to configure a Dom\Document instance.
 *
 * @package ampproject/amp-toolbox
 */
interface Option
{
    /**
     * Option to configure the preferred amp-bind syntax.
     *
     * @var string
     */
    const AMP_BIND_SYNTAX = 'amp_bind_syntax';

    /**
     * Option to provide the encoding of the document.
     *
     * @var string
     */
    const ENCODING = 'encoding';

    /**
     * Option to provide additional libxml flags to configure parsing of the document.
     *
     * @var string
     */
    const LIBXML_FLAGS = 'libxml_flags';

    /**
     * Option to check encoding in order to detect invalid byte sequences.
     *
     * @var string
     */
    const CHECK_ENCODING = 'check_encoding';

    /**
     * Associative array of known options and their respective default value.
     *
     * @var array
     */
    const DEFAULTS = [
        self::AMP_BIND_SYNTAX => self::AMP_BIND_SYNTAX_AUTO,
        self::ENCODING        => null,
        self::LIBXML_FLAGS    => 0,
        self::CHECK_ENCODING  => false,
    ];

    /**
     * Possible value 'auto' for the 'amp_bind_syntax' option.
     *
     * @var string
     */
    const AMP_BIND_SYNTAX_AUTO = 'auto';

    /**
     * Possible value 'data_attribute' for the 'amp_bind_syntax' option.
     *
     * @var string
     */
    const AMP_BIND_SYNTAX_DATA_ATTRIBUTE = 'data_attribute';

    /**
     * Possible value 'square_brackets' for the 'amp_bind_syntax' option.
     *
     * @var string
     */
    const AMP_BIND_SYNTAX_SQUARE_BRACKETS = 'square_brackets';
}
