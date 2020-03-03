<?php

namespace Amp\Optimizer\Tweak;

use Amp\Tweak;

/**
 * Fix the src attribute on i-amphtml-sizer elements.
 *
 * They contain a data:img/svg string in a fixed size to provide intrinsic layout positioning.
 * The fix adapts the src attribute of the element because DOMDocument messes up the attribute by encoding it on
 * saveHTML().
 *
 * @package amp/optimizer
 */
final class SvgSourceAttributeEncodingFix implements Tweak
{

    const I_AMPHTML_SIZER_REGEX_PATTERN = '/(?<before_src><i-amphtml-sizer\s+[^>]*>\s*<img\s+[^>]*?\s+src=([\'"]))(?<src>.*?)(?<after_src>\2><\/i-amphtml-sizer>)/i';

    const SRC_SVG_REGEX_PATTERN = '/^\s*(?<type>[^<]+)(?<value><svg[^>]+>)\s*$/i';

    /**
     * Process the HTML output string and tweak it as needed.
     *
     * @param string $html HTML output string to tweak.
     * @return string Tweaked HTML output string.
     */
    public function process($html)
    {
        return preg_replace_callback(self::I_AMPHTML_SIZER_REGEX_PATTERN, [$this, 'adaptSizer'], $html);
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
