<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Html\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Dom\Document\AfterSaveFilter;
use AmpProject\Dom\Document\BeforeLoadFilter;

/**
 * Filter for the emoji AMP symbol (⚡).
 *
 * @package ampproject/amp-toolbox
 */
final class AmpEmojiAttribute implements BeforeLoadFilter, AfterSaveFilter
{
    /**
     * Pattern to match an AMP emoji together with its variant (amp4ads, amp4email, ...).
     *
     * @var string
     */
    const AMP_EMOJI_ATTRIBUTE_PATTERN = '/<html\s([^>]*?(?:'
                                        . Attribute::AMP_EMOJI_ALT
                                        . '|'
                                        . Attribute::AMP_EMOJI
                                        . ')(4(?:ads|email))?[^>]*?)>/i';

    /**
     * Store the emoji that was used to represent the AMP attribute.
     *
     * There are a few variations, so we want to keep track of this.
     *
     * @see https://github.com/ampproject/amphtml/issues/25990
     *
     * @var string
     */
    private $usedAmpEmoji;

    /**
     * Covert the emoji AMP symbol (⚡) into pure text.
     *
     * The emoji symbol gets stripped by DOMDocument::loadHTML().
     *
     * @param string $html Source HTML string to convert the emoji AMP symbol in.
     * @return string Adapted source HTML string.
     */
    public function beforeLoad($html)
    {
        $this->usedAmpEmoji = '';

        $result = preg_replace_callback(
            self::AMP_EMOJI_ATTRIBUTE_PATTERN,
            function ($matches) {
                // Split into individual attributes.
                $attributes = array_map(
                    'trim',
                    array_filter(
                        preg_split(
                            '#(\s+[^"\'\s=]+(?:=(?:"[^"]+"|\'[^\']+\'|[^"\'\s]+))?)#',
                            $matches[1],
                            -1,
                            PREG_SPLIT_DELIM_CAPTURE
                        )
                    )
                );

                foreach ($attributes as $index => $attribute) {
                    $attributeMatches = [];
                    if (
                        preg_match(
                            '/^(' . Attribute::AMP_EMOJI_ALT . '|' . Attribute::AMP_EMOJI . ')(4(?:ads|email))?$/i',
                            $attribute,
                            $attributeMatches
                        )
                    ) {
                        $this->usedAmpEmoji = $attributeMatches[1];
                        $variant            = ! empty($attributeMatches[2]) ? $attributeMatches[2] : '';
                        $attributes[$index] = Document::EMOJI_AMP_ATTRIBUTE_PLACEHOLDER . "=\"{$variant}\"";
                        break;
                    }
                }

                return '<html ' . implode(' ', $attributes) . '>';
            },
            $html,
            1
        );

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Restore the emoji AMP symbol (⚡) from its pure text placeholder.
     *
     * @param string $html HTML string to restore the AMP emoji symbol in.
     * @return string Adapted HTML string.
     */
    public function afterSave($html)
    {
        if (empty($this->usedAmpEmoji)) {
            return $html;
        }

        $result = preg_replace(
            '/(<html\s[^>]*?)' . preg_quote(Document::EMOJI_AMP_ATTRIBUTE_PLACEHOLDER, '/') . '="([^"]*)"/i',
            '\1' . $this->usedAmpEmoji . '\2',
            $html,
            1
        );

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }
}
