<?php

namespace AmpProject\Dom\Document\Filter;

use AmpProject\Dom\Document\AfterSaveFilter;

/**
 * Filter for fixing the mangled encoding of src attributes with SVG data.
 *
 * @package ampproject/amp-toolbox
 */
final class SvgSourceAttributeEncoding implements AfterSaveFilter
{
    /**
     * Regex pattern to match an SVG sizer element.
     *
     * @var string
     */
    const I_AMPHTML_SIZER_REGEX_PATTERN = '/(?<before_src><i-amphtml-sizer\s+[^>]*>\s*<img\s+[^>]*?\s+src=([\'"]))'
                                          . '(?<src>.*?)'
                                          . '(?<after_src>\2><\/i-amphtml-sizer>)/i';

    /**
     * Regex pattern for extracting fields to adapt out of a src attribute.
     *
     * @var string
     */
    const SRC_SVG_REGEX_PATTERN = '/^\s*(?<type>[^<]+)(?<value><svg[^>]+>)\s*$/i';

    /**
     * Process SVG sizers to ensure they match the required format to validate against AMP.
     *
     * @param string $html HTML output string to tweak.
     * @return string Tweaked HTML output string.
     */
    public function afterSave($html)
    {
        $result = preg_replace_callback(self::I_AMPHTML_SIZER_REGEX_PATTERN, [$this, 'adaptSizer'], $html);

        if (! is_string($result)) {
            return $html;
        }

        return $result;
    }

    /**
     * Adapt the sizer element so that it validates against the AMP spec.
     *
     * @param array $matches Matches that the regular expression collected.
     * @return string Adapted string to use as replacement.
     */
    private function adaptSizer($matches)
    {
        $src = $matches['src'];
        $src = htmlspecialchars_decode($src, ENT_NOQUOTES);
        $src = preg_replace_callback(self::SRC_SVG_REGEX_PATTERN, [$this, 'adaptSvg'], $src);

        if (! is_string($src)) {
            // The regex replace failed, so revert to the initial src.
            $src = $matches['src'];
        }

        return $matches['before_src'] . $src . $matches['after_src'];
    }

    /**
     * Adapt the SVG syntax within the sizer element so that it validates against the AMP spec.
     *
     * @param array $matches Matches that the regular expression collected.
     * @return string Adapted string to use as replacement.
     */
    private function adaptSvg($matches)
    {
        return $matches['type'] . urldecode($matches['value']);
    }
}
