<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;
use AmpProject\Html\Tag;

/**
 * Filter to secure and restore self-closing tags.
 *
 * @package ampproject/amp-toolbox
 */
final class SelfClosingTags implements BeforeLoadFilter, AfterSaveFilter
{
    /**
     * Whether the self-closing tags were transformed and need to be restored.
     *
     * This avoids duplicating this effort (maybe corrupting the DOM) on multiple calls to saveHTML().
     *
     * @var bool
     */
    private $selfClosingTagsTransformed = false;

    /**
     * Force all self-closing tags to have closing tags.
     *
     * This is needed because DOMDocument isn't fully aware of these.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     */
    public function beforeLoad($html)
    {
        static $regexPattern = null;

        if (null === $regexPattern) {
            $regexPattern = '#<(' . implode('|', Tag::SELF_CLOSING_TAGS) . ')([^>]*?)(?>\s*(?<!\\\\)/)?>(?!</\1>)#';
        }

        $this->selfClosingTagsTransformed = true;

        $result = preg_replace($regexPattern, '<$1$2></$1>', $html);

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Restore all self-closing tags again.
     *
     * @param string $html HTML string to adapt.
     * @return string Adapted HTML string.
     */
    public function afterSave($html)
    {
        static $regexPattern = null;

        if (! $this->selfClosingTagsTransformed) {
            return $html;
        }

        if (null === $regexPattern) {
            $regexPattern = '#</(' . implode('|', Tag::SELF_CLOSING_TAGS) . ')>#i';
        }

        $this->selfClosingTagsTransformed = false;

        $result = preg_replace($regexPattern, '', $html);

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }
}
