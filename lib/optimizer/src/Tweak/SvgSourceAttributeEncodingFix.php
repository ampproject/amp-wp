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

    const REGEX_PATTERN = '/(?<before_src><i-amphtml-sizer\s+[^>]*>\s*<img\s+[^>]*?\s+src=([\'"]))(?<src>.*?)(?<after_src>\2><\/i-amphtml-sizer>)/i';

    /**
     * Process the HTML output string and tweak it as needed.
     *
     * @param string $html HTML output string to tweak.
     * @return string Tweaked HTML output string.
     */
    public function process($html)
    {
        return preg_replace_callback(self::REGEX_PATTERN, [$this, 'adaptSource'], $html);
    }

    /**
     * Adapt the src attribute so that it validates against the AMP spec.
     *
     * @param array $matches Matches that the regular expression collected.
     * @return string Adapted string to use as replacement.
     */
    private function adaptSource($matches)
    {
        return $matches['before_src'] . urldecode(htmlspecialchars_decode($matches['src'], ENT_NOQUOTES)) . $matches['after_src'];
    }
}
