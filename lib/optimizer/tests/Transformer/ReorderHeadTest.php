<?php

namespace Amp\Optimizer\Transformer;

use Amp\Dom\Document;
use Amp\Optimizer\Error;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Tests\ErrorComparison;
use Amp\Optimizer\Tests\MarkupComparison;
use Amp\Optimizer\Tests\TestMarkup;
use PHPUnit\Framework\TestCase;

/**
 * Test the ReorderHead transformer.
 *
 * @package amp/optimizer
 */
final class ReorderHeadTest extends TestCase
{

    use ErrorComparison;
    use MarkupComparison;

    /**
     * Provide the data to test the transform() method.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataTransform()
    {
        return [
            'reorders head children for amp document'                    => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::TITLE . TestMarkup::STYLE_AMPBOILERPLATE .
                TestMarkup::SCRIPT_AMPEXPERIMENT . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::NOSCRIPT_AMPBOILERPLATE . TestMarkup::STYLE_AMPRUNTIME .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::LINK_STYLESHEET_GOOGLE_FONT .
                TestMarkup::LINK_GOOGLE_FONT_PRECONNECT . TestMarkup::META_CHARSET .
                TestMarkup::META_VIEWPORT . TestMarkup::STYLE_AMPCUSTOM . TestMarkup::LINK_CANONICAL .
                TestMarkup::LINK_FAVICON . TestMarkup::SCRIPT_AMPVIEWER_RUNTIME .
                TestMarkup::SCRIPT_AMPMUSTACHE . TestMarkup::SCRIPT_AMPMRAID .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                // (0) <meta charset> tag
                TestMarkup::META_CHARSET .
                // (1) <style amp-runtime> (inserted by ampruntimecss.go)
                TestMarkup::STYLE_AMPRUNTIME .
                // (2) remaining <meta> tags (those other than <meta charset>)
                TestMarkup::META_VIEWPORT .
                // (3) AMP runtime .js <script> tag
                TestMarkup::SCRIPT_AMPRUNTIME .
                // (4) AMP viewer runtime .js <script> tag (inserted by AmpViewerScript)
                TestMarkup::SCRIPT_AMPVIEWER_RUNTIME .
                // (5) <script> tags that are render delaying
                TestMarkup::SCRIPT_AMPEXPERIMENT .
                // (6) <script> tags for remaining extensions
                TestMarkup::SCRIPT_AMPMRAID .
                TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::SCRIPT_AMPMUSTACHE .
                // (7) <link> tag for favicons
                TestMarkup::LINK_FAVICON .
                // (8) <link> tag for resource hints
                TestMarkup::LINK_GOOGLE_FONT_PRECONNECT .
                // (9) <link rel=stylesheet> tags before <style amp-custom>
                TestMarkup::LINK_STYLESHEET_GOOGLE_FONT .
                // (10) <style amp-custom>
                TestMarkup::STYLE_AMPCUSTOM .
                // (11) any other tags allowed in <head>
                TestMarkup::TITLE .
                TestMarkup::LINK_CANONICAL .
                // (12) amp boilerplate (first style amp-boilerplate, then noscript)
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'reorders head children for amp4ads document'                => [
                TestMarkup::DOCTYPE . '<html ⚡4ads><head>' .
                TestMarkup::TITLE . TestMarkup::STYLE_AMP_4_ADS_BOILERPLATE . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::SCRIPT_AMP_4_ADS_RUNTIME . TestMarkup::LINK_STYLESHEET_GOOGLE_FONT .
                TestMarkup::LINK_GOOGLE_FONT_PRECONNECT . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::STYLE_AMPCUSTOM .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡4ads><head>' .
                // (0) <meta charset> tag
                TestMarkup::META_CHARSET .
                // (1) <style amp-runtime> (inserted by ampruntimecss.go)
                // n/a for AMP4ADS
                // (2) remaining <meta> tags (those other than <meta charset>)
                TestMarkup::META_VIEWPORT .
                // (3) AMP runtime .js <script> tag
                TestMarkup::SCRIPT_AMP_4_ADS_RUNTIME .
                // (4) AMP viewer runtime .js <script> tag (inserted by AmpViewerScript)
                // n/a for AMP4ADS, no viewer
                // (5) <script> tags that are render delaying
                // n/a for AMP4ADS, no render delaying <script> tags allowed
                // (6) <script tags> for remaining extensions
                TestMarkup::SCRIPT_AMPAUDIO .
                // (7) <link> tag for favicons
                // n/a for AMP4ADS, no favicons allowed
                // (8) <link> tag for resource hints
                TestMarkup::LINK_GOOGLE_FONT_PRECONNECT .
                // (9) <link rel=stylesheet> tags before <style amp-custom>
                TestMarkup::LINK_STYLESHEET_GOOGLE_FONT .
                // (10) <style amp-custom>
                TestMarkup::STYLE_AMPCUSTOM .
                // (11) any other tags allowed in <head>
                TestMarkup::TITLE .
                // (12) amp boilerplate (first style amp-boilerplate, then noscript)
                TestMarkup::STYLE_AMP_4_ADS_BOILERPLATE .
                '</head><body></body></html>',
            ],
            'preserves style sheet ordering'                             => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_STYLESHEET_GOOGLE_FONT . TestMarkup::STYLE_AMPCUSTOM .
                '<link href="another-font" rel="stylesheet">' .
                TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_STYLESHEET_GOOGLE_FONT . TestMarkup::STYLE_AMPCUSTOM .
                '<link href="another-font" rel="stylesheet">' .
                TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'amp runtime script is reordered as first script'            => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPAUDIO . TestMarkup::SCRIPT_AMPRUNTIME .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'render delaying scripts before non-render delaying scripts' => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO . TestMarkup::SCRIPT_AMPEXPERIMENT .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPEXPERIMENT . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'removes duplicate custom element script'                    => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'sorts custom element scripts'                               => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPEXPERIMENT . TestMarkup::SCRIPT_AMPDYNAMIC_CSSCLASSES .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPDYNAMIC_CSSCLASSES . TestMarkup::SCRIPT_AMPEXPERIMENT .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'removes duplicate custom template script'                   => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPMUSTACHE . TestMarkup::SCRIPT_AMPMUSTACHE .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPMUSTACHE .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'preserves multiple favicons'                                => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO .
                TestMarkup::LINK_FAVICON . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
            'case insensitive rel value'                                 => [
                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO . TestMarkup::LINK_CANONICAL .
                TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '<link href="https://example.com/favicon.ico" rel="Shortcut Icon">' .
                '</head><body></body></html>',

                TestMarkup::DOCTYPE . '<html ⚡><head>' .
                TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT .
                TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::SCRIPT_AMPAUDIO .
                '<link href="https://example.com/favicon.ico" rel="Shortcut Icon">' .
                TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE .
                '</head><body></body></html>',
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       ReorderHead::transform()
     * @dataProvider dataTransform()
     *
     * @param string                  $source         String of source HTML.
     * @param string                  $expectedHtml   String of expected HTML output.
     * @param ErrorCollection|Error[] $expectedErrors Set of expected errors.
     */
    public function testTransform($source, $expectedHtml, $expectedErrors = [])
    {
        $document    = Document::fromHtml($source);
        $transformer = new ReorderHead();
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertEqualMarkup($expectedHtml, $document->saveHTML());
        $this->assertSameErrors($expectedErrors, $errors);
    }
}
