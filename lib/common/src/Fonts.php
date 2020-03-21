<?php

namespace AmpProject;

/**
 * Class for fonts.
 *
 * @package ampproject/common
 */
final class Fonts
{

    /**
     * Font stack to cycle through for displaying emojis reliably.
     *
     * @var string[]
     */
    const EMOJI_FONT_STACK = [
        'Apple Color Emoji',
        'Android Emoji',
        'Segoe UI Emoji',
        'Noto Color Emoji',
        'EmojiSymbols',
        'Symbola',
        'Segoe UI Symbol',
        'emoji',
    ];

    /**
     * Get emoji font family property value.
     *
     * @return string Font-family property value.
     */
    public static function getEmojiFontFamilyValue()
    {
        static $fontFamilyValue = null;

        if ($fontFamilyValue === null) {
            $fontFamilyValue = implode(
                ', ',
                array_map(
                    static function ($font) {
                        return '"' . $font . '"';
                    },
                    self::EMOJI_FONT_STACK
                )
            );
        }

        return $fontFamilyValue;
    }
}
