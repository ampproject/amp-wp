<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\Error;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Tests\ErrorComparison;
use AmpProject\Optimizer\Tests\MarkupComparison;
use AmpProject\Optimizer\Tests\TestMarkup;
use PHPUnit\Framework\TestCase;

/**
 * Test the ServerSideRendering transformer.
 *
 * @package ampproject/optimizer
 */
final class ServerSideRenderingTest extends TestCase
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
        $input = static function ($body, $extraHead = '') {
            return TestMarkup::DOCTYPE . '<html ⚡><head>'
                   . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                   . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE
                   . $extraHead
                   . '</head><body>'
                   . $body
                   . '</body></html>';
        };

        $expectWithoutBoilerplate = static function ($body, $extraHead = '') {
            return TestMarkup::DOCTYPE . '<html ⚡ i-amphtml-layout="" i-amphtml-no-boilerplate=""><head>'
                   . TestMarkup::STYLE_AMPRUNTIME . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                   . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL
                   . $extraHead
                   . '</head><body>'
                   . $body
                   . '</body></html>';
        };

        $expectWithBoilerplate = static function ($body, $extraHead = '') {
            return TestMarkup::DOCTYPE . '<html ⚡ i-amphtml-layout=""><head>'
                   . TestMarkup::STYLE_AMPRUNTIME . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                   . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE
                   . $extraHead
                   . '</head><body>'
                   . $body
                   . '</body></html>';
        };

        return [
            'modifies document only once' => [
                $expectWithBoilerplate('<amp-img layout="container"></amp-img>'),
                /*
                 * The expected output is actually not correctly server-side rendered, but the presence of
                 * i-amphtml-layout attribute halts processing, so this is effectively a no-op.
                 */
                $expectWithBoilerplate('<amp-img layout="container"></amp-img>'),
            ],

            'boilerplate removed and preserves noscript in body' => [
                $input('<noscript><img src="lemur.png"></noscript>'),
                $expectWithoutBoilerplate('<noscript><img src="lemur.png"></noscript>'),
            ],

            'boilerplate removed and no changes within template tag' => [
                $input('<template><amp-img height="42" layout="responsive" width="42"></amp-img></template>'),
                $expectWithoutBoilerplate('<template><amp-img height="42" layout="responsive" width="42"></amp-img></template>'),
            ],

            'boilerplate removed and layout applied' => [
                $input('<amp-img class="" layout="container"></amp-img>'),
                $expectWithoutBoilerplate('<amp-img class="i-amphtml-layout-container" layout="container" i-amphtml-layout="container"></amp-img>'),
            ],

            'amp4Email boilerplate removed and layout applied' => [
                TestMarkup::DOCTYPE . '<html ⚡4email><head>'
                . TestMarkup::META_CHARSET . TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::STYLE_AMP_4_EMAIL_BOILERPLATE
                . '</head><body>'
                . '<amp-img layout="container"></amp-img>'
                . '</body></html>',

                TestMarkup::DOCTYPE . '<html ⚡4email i-amphtml-layout="" i-amphtml-no-boilerplate=""><head>'
                . TestMarkup::STYLE_AMPRUNTIME . TestMarkup::META_CHARSET . TestMarkup::SCRIPT_AMPRUNTIME
                . '</head><body>'
                . '<amp-img layout="container" class="i-amphtml-layout-container" i-amphtml-layout="container"></amp-img>'
                . '</body></html>',
            ],

            'amp4Ads boilerplate removed and layout applied' => [
                TestMarkup::DOCTYPE . '<html ⚡4ads><head>'
                . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME . TestMarkup::STYLE_AMP_4_ADS_BOILERPLATE
                . '</head><body>'
                . '<amp-img layout="container"></amp-img>'
                . '</body></html>',

                TestMarkup::DOCTYPE . '<html ⚡4ads i-amphtml-layout="" i-amphtml-no-boilerplate=""><head>'
                . TestMarkup::STYLE_AMPRUNTIME . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                . '</head><body>'
                . '<amp-img layout="container" class="i-amphtml-layout-container" i-amphtml-layout="container"></amp-img>'
                . '</body></html>',
            ],

            'boilerplate removed despite sizes (in head though)' => [
                $input('<link rel="shortcut icon" type="a" href="b" sizes="c">'),
                $expectWithoutBoilerplate('<link rel="shortcut icon" type="a" href="b" sizes="c">'),
            ],

            'boilerplate removed when amp-experiment is present but empty' => [
                $input('<amp-experiment><script type="application/json">{ }</script></amp-experiment>'),
                $expectWithoutBoilerplate('<amp-experiment class="i-amphtml-layout-container" i-amphtml-layout="container"><script type="application/json">{ }</script></amp-experiment>'),
            ],

            'amp-audio' => [
                $input('<amp-audio></amp-audio>'),
                $expectWithBoilerplate('<amp-audio></amp-audio>'),
                [Error\CannotRemoveBoilerplate::class],
            ],

            'amp-experiment is non-empty' => [
                $input('<amp-experiment><script type="application/json">{ "exp": { "variants": { "a": 25, "b": 25 } } }</script></amp-experiment>'),
                $expectWithBoilerplate('<amp-experiment class="i-amphtml-layout-container" i-amphtml-layout="container"><script type="application/json">{ "exp": { "variants": { "a": 25, "b": 25 } } }</script></amp-experiment>'),
                [Error\CannotRemoveBoilerplate::class],
            ],

            'amp-story' => [
                $input('', TestMarkup::SCRIPT_AMPSTORY),
                $expectWithBoilerplate('', TestMarkup::SCRIPT_AMPSTORY),
                [Error\CannotRemoveBoilerplate::class],
            ],

            'amp-dynamic-css-classes' => [
                $input('', TestMarkup::SCRIPT_AMPDYNAMIC_CSSCLASSES),
                $expectWithBoilerplate('', TestMarkup::SCRIPT_AMPDYNAMIC_CSSCLASSES),
                [Error\CannotRemoveBoilerplate::class],
            ],

            'media attribute' => [
                $input('<amp-img height="355" layout="fixed" media="(min-width: 650px) and handheld" src="wide.jpg" width="466"></amp-img>'),
                $expectWithBoilerplate('<amp-img height="355" layout="fixed" media="(min-width: 650px) and handheld" src="wide.jpg" width="466" class="i-amphtml-layout-fixed i-amphtml-layout-size-defined" style="width:466px;height:355px;" i-amphtml-layout="fixed"></amp-img>'),
                [Error\CannotRemoveBoilerplate::class],
            ],

            'sizes attribute without amp-custom' => [
                $input('<amp-img height="300" layout="responsive" sizes="(min-width: 320px) 320px, 100vw" src="https://acme.org/image1.png" width="400"></amp-img>'),
                $expectWithoutBoilerplate(
                    '<amp-img height="300" layout="responsive" src="https://acme.org/image1.png" width="400" id="i-amp-id" class="i-amphtml-layout-responsive i-amphtml-layout-size-defined" i-amphtml-layout="responsive"><i-amphtml-sizer style="display:block;padding-top:75.0000%;"></i-amphtml-sizer></amp-img>',
                    '<style amp-custom>#i-amp-id{width:100vw};@media (min-width: 320px){#i-amp-id{width:320px;}}</style>'
                ),
                [],
            ],

            'sizes attribute with amp-custom' => [
                $input(
                    '<amp-img height="300" layout="responsive" sizes="(min-width: 320px) 320px, 100vw" src="https://acme.org/image1.png" width="400"></amp-img>',
                    '<style amp-custom>h1:red;</style>'
                ),
                $expectWithoutBoilerplate(
                    '<amp-img height="300" layout="responsive" src="https://acme.org/image1.png" width="400" id="i-amp-id" class="i-amphtml-layout-responsive i-amphtml-layout-size-defined" i-amphtml-layout="responsive"><i-amphtml-sizer style="display:block;padding-top:75.0000%;"></i-amphtml-sizer></amp-img>',
                    '<style amp-custom>h1:red;#i-amp-id{width:100vw};@media (min-width: 320px){#i-amp-id{width:320px;}}</style>'
                ),
                [],
            ],

            'bad sizes attribute' => [
                $input('<amp-img height="300" layout="responsive" sizes=",,," src="https://acme.org/image1.png" width="400"></amp-img>'),
                $expectWithBoilerplate('<amp-img height="300" layout="responsive" sizes=",,,"  src="https://acme.org/image1.png" width="400"></amp-img>'),
                [Error\CannotRemoveBoilerplate::class],
            ],

            'heights attribute without amp-custom' => [
                $input('<amp-img height="256" heights="(min-width: 500px) 200px, 80%" layout="responsive" width="320"></amp-img>'),
                $expectWithoutBoilerplate(
                    '<amp-img height="256" layout="responsive" width="320" id="i-amp-id" class="i-amphtml-layout-responsive i-amphtml-layout-size-defined" i-amphtml-layout="responsive"><i-amphtml-sizer style="display:block;padding-top:80.0000%;"></i-amphtml-sizer></amp-img>',
                    '<style amp-custom>#i-amp-id>i-amphtml-sizer{height:80%};@media (min-width: 500px){#i-amp-id>i-amphtml-sizer{height:200px;}}</style>'
                ),
                [],
            ],


            'heights attribute with amp-custom' => [
                $input(
                    '<amp-img height="256" heights="(min-width: 500px) 200px, 80%" layout="responsive" width="320"></amp-img>',
                    '<style amp-custom>h1:red;</style>'
                ),
                $expectWithoutBoilerplate(
                    '<amp-img height="256" layout="responsive" width="320" id="i-amp-id" class="i-amphtml-layout-responsive i-amphtml-layout-size-defined" i-amphtml-layout="responsive"><i-amphtml-sizer style="display:block;padding-top:80.0000%;"></i-amphtml-sizer></amp-img>',
                    '<style amp-custom>h1:red;#i-amp-id>i-amphtml-sizer{height:80%};@media (min-width: 500px){#i-amp-id>i-amphtml-sizer{height:200px;}}</style>'
                ),
                [],
            ],

            'bad heights attribute' => [
                $input('<amp-img height="256" heights=",,," layout="responsive" width="320"></amp-img>'),
                $expectWithBoilerplate('<amp-img height="256" heights=",,," layout="responsive" width="320"></amp-img>'),
                [Error\CannotRemoveBoilerplate::class],
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \AmpProject\Optimizer\Transformer\ServerSideRendering::transform()
     * @dataProvider dataTransform()
     *
     * @param string                  $source         String of source HTML.
     * @param string                  $expectedHtml   String of expected HTML output.
     * @param ErrorCollection|Error[] $expectedErrors Set of expected errors.
     */
    public function testTransform($source, $expectedHtml, $expectedErrors = [])
    {
        $document    = Document::fromHtml($source);
        $transformer = new ServerSideRendering();
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertSimilarMarkup($expectedHtml, $document->saveHTML());
        $this->assertSameErrors($expectedErrors, $errors);
    }
}
