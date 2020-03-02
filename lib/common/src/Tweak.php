<?php

namespace Amp;

/**
 * Interface for custom tweaks that adapt the HTML output string after it was generated.
 *
 * These tweaks can be added to the document via Document::addTweak().
 *
 * @package amp/common
 */
interface Tweak
{

    /**
     * Process the HTML output string and tweak it as needed.
     *
     * @param string $html HTML output string to tweak.
     * @return string Tweaked HTML output string.
     */
    public function process($html);
}
