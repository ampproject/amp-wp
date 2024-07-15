<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;

/**
 * Protect the esi tags from being broken.
 *
 * @package ampproject/amp-toolbox
 */
final class ProtectEsiTags implements BeforeLoadFilter, AfterSaveFilter
{
    /**
     * List of self-closing ESI tags.
     *
     * @link https://www.w3.org/TR/esi-lang/
     *
     * @var string[]
     */
    const SELF_CLOSING_TAGS = [
        'esi:include',
        'esi:comment',
    ];

    /**
     * Preprocess the HTML to be loaded into the Dom\Document.
     *
     * @param string $html String of HTML markup to be preprocessed.
     * @return string Preprocessed string of HTML markup.
     */
    public function beforeLoad($html)
    {
        $patterns = [
            '#<(' . implode('|', self::SELF_CLOSING_TAGS) . ')([^>]*?)(?>\s*(?<!\\\\)/)?>(?!</\1>)#',
            '/(<esi:include.+?)(src)=/',
            '/(<\/?)esi:/',
        ];

        $replacements = [
            '<$1$2></$1>',
            '$1esi-src=',
            '$1esi-',
        ];

        $result = preg_replace($patterns, $replacements, $html);

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Process the Dom\Document after being saved from Dom\Document.
     *
     * @param string $html String of HTML markup to be preprocessed.
     * @return string Preprocessed string of HTML markup.
     */
    public function afterSave($html)
    {
        $patterns = [
            '/(<\/?)esi-/',
            '/(<esi:include.+?)(esi-src)=/',
            '#></(' . implode('|', self::SELF_CLOSING_TAGS) . ')>#i',
        ];

        $replacements = [
            '$1esi:',
            '$1src=',
            '/>',
        ];

        $result = preg_replace($patterns, $replacements, $html);

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }
}
