<?php

namespace AmpProject\Optimizer;

/**
 * Collection of CSS rules.
 *
 * This is used in conjunction with CssRule for deduplication of CSS when adding styles during transformations.
 *
 * Note: This is a simplistic representation of CSS rules built for a specific purpose.
 * Make sure it supports a given use case before including in new code!
 *
 * @package ampproject/optimizer
 */
final class CssRules
{

    /**
     * Internal array of CssRule objects.
     *
     * @var CssRule[]
     */
    private $cssRules = [];

    /**
     * Rendered CSS cache.
     *
     * @var string|null
     */
    private $renderedCss;

    /**
     * Byte count cache.
     *
     * @var int|null
     */
    private $byteCount;

    /**
     * Create a new CssRules collection from an array of CssRule objects.
     *
     * @param CssRule[] $cssRuleArray Array of CssRule objects.
     * @return CssRules CSS rules collection.
     */
    public static function fromCssRuleArray($cssRuleArray)
    {
        $cssRules = new self();

        array_walk(
            $cssRuleArray,
            static function (CssRule $cssRule) use (&$cssRules) {
                $cssRules = $cssRules->add($cssRule);
            }
        );

        return $cssRules;
    }

    /**
     * Add a CSS rule to the collection.
     *
     * @param CssRule $cssRule
     * @return CssRules Adapted collection with the added CSS rule.
     */
    public function add(CssRule $cssRule)
    {
        $clone = clone $this;

        if (empty($clone->cssRules)) {
            $clone->cssRules = [$cssRule];

            return $clone;
        }

        foreach ($clone->cssRules as $index => $existingCssRule) {
            if ($existingCssRule->canBeMerged($cssRule)) {
                $clone->cssRules[$index] = $existingCssRule->mergeWith($cssRule);
                // Rendered CSS and byte count need to be rebuilt, as some previously rendered CSS rule has changed.
                $clone->renderedCss = null;
                $clone->byteCount   = null;

                return $clone;
            }
        }

        $clone->cssRules[] = $cssRule;

        if ($clone->renderedCss !== null) {
            // As we didn't merge, we can save rerendering and just concat the single rule.
            $clone->renderedCss .= $cssRule->getCss();
        }

        if ($clone->byteCount !== null) {
            // As we didn't merge, we can save recounting and just add the bytes of the single rule.
            $clone->byteCount += $cssRule->getByteCount();
        }

        return $clone;
    }

    /**
     * Get the CSS for the entire collection of CSS rules.
     *
     * @return string String representation of the collection of CSS rules.
     */
    public function getCss()
    {
        if ($this->renderedCss === null) {
            $this->renderedCss = array_reduce(
                $this->cssRules,
                static function ($css, CssRule $cssRule) {
                    return $css . $cssRule->getCss();
                },
                ''
            );
        }

        return $this->renderedCss;
    }

    /**
     * Get the byte count for the entire collection of CSS rules.
     *
     * @return int Byte count of the collection of CSS rules.
     */
    public function getByteCount()
    {
        if ($this->byteCount === null) {
            $this->byteCount = array_reduce(
                $this->cssRules,
                static function ($byteCount, CssRule $cssRule) {
                    return $byteCount + $cssRule->getByteCount();
                },
                0
            );
        }

        return $this->byteCount;
    }
}
