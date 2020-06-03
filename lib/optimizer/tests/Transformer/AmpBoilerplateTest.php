<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Amp;
use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Tests\MarkupComparison;
use AmpProject\Optimizer\Tests\TestMarkup;
use PHPUnit\Framework\TestCase;

/**
 * Test the AmpBoilerplate transformer.
 *
 * @package ampproject/optimizer
 */
final class AmpBoilerplateTest extends TestCase
{
    use MarkupComparison;

    /**
     * Provide the data to test the transform() method.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataTransform()
    {
        $htmlDocument = static function ($html, $headEnd = '') {
            return TestMarkup::DOCTYPE . $html . '<head>' .
                   TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME .
                   TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                   $headEnd .
                   '</head><body></body></html>';
        };

        $repeatTwice = static function ($value) {
            return array_fill(0, 2, $value);
        };

        $ampBoilerplate = '<style ' . Attribute::AMP_BOILERPLATE . '>' . Amp::BOILERPLATE_CSS . '</style>' .
                          '<noscript><style ' . Attribute::AMP_BOILERPLATE . '>' . Amp::BOILERPLATE_NOSCRIPT_CSS . '</style></noscript>';

        $amp4AdsBoilerplate = '<style ' . Attribute::AMP4ADS_BOILERPLATE . '>' . Amp::AMP4ADS_AND_AMP4EMAIL_BOILERPLATE_CSS . '</style>';

        $amp4EmailBoilerplate = '<style ' . Attribute::AMP4EMAIL_BOILERPLATE . '>' . Amp::AMP4ADS_AND_AMP4EMAIL_BOILERPLATE_CSS . '</style>';

        return [
            'keeps boilerplate' => $repeatTwice(
                $htmlDocument('<html ⚡>', $ampBoilerplate)
            ),

            'keeps boilerplate again' => $repeatTwice(
                $htmlDocument('<html amp>', $ampBoilerplate)
            ),

            'removes incorrect boilerplates' => [
                $htmlDocument('<html amp>', $ampBoilerplate . $amp4AdsBoilerplate . $amp4EmailBoilerplate),
                $htmlDocument('<html amp>', $ampBoilerplate),
            ],

            'leaves out boilerplate' => $repeatTwice(
                $htmlDocument('<html amp i-amphtml-no-boilerplate>')
            ),

            'removes boilerplate' => [
                $htmlDocument('<html amp i-amphtml-no-boilerplate>', $ampBoilerplate),
                $htmlDocument('<html amp i-amphtml-no-boilerplate>'),
            ],

            'keeps boilerplate for amp4ads' => $repeatTwice(
                $htmlDocument('<html amp4ads>', $amp4AdsBoilerplate)
            ),

            'keeps boilerplate for ⚡4ads' => $repeatTwice(
                $htmlDocument('<html ⚡4ads>', $amp4AdsBoilerplate)
            ),

            'keeps boilerplate for amp4email' => $repeatTwice(
                $htmlDocument('<html amp4email>', $amp4EmailBoilerplate)
            ),

            'keeps boilerplate for ⚡4email' => $repeatTwice(
                $htmlDocument('<html ⚡4email>', $amp4EmailBoilerplate)
            ),

            'adds boilerplate if missing' => [
                $htmlDocument('<html ⚡>'),
                $htmlDocument('<html ⚡>', $ampBoilerplate),
            ],

            'adds boilerplate if missing for amp4ads' => [
                $htmlDocument('<html amp4ads>'),
                $htmlDocument('<html amp4ads>', $amp4AdsBoilerplate),
            ],

            'adds boilerplate if missing for ⚡4ads' => [
                $htmlDocument('<html ⚡4ads>'),
                $htmlDocument('<html ⚡4ads>', $amp4AdsBoilerplate),
            ],

            'adds boilerplate if missing for amp4email' => [
                $htmlDocument('<html amp4email>'),
                $htmlDocument('<html amp4email>', $amp4EmailBoilerplate),
            ],

            'adds boilerplate if missing for ⚡4email' => [
                $htmlDocument('<html ⚡4email>'),
                $htmlDocument('<html ⚡4email>', $amp4EmailBoilerplate),
            ],

            'leaves styles that lack boilerplate attribute' => $repeatTwice(
                $htmlDocument('<html ⚡>', '<style>h1{color:red}</style><noscript><style>h2{color:blue}</style></noscript>' . $ampBoilerplate)
            ),

            'leaves styles that lack boilerplate attribute and adds boilerplate' => [
                $htmlDocument('<html ⚡>', '<style>h1{color:red}</style><noscript><style>h2{color:blue}</style></noscript>'),
                $htmlDocument('<html ⚡>', '<style>h1{color:red}</style><noscript><style>h2{color:blue}</style></noscript>' . $ampBoilerplate),
            ],

            'leaves styles that lack boilerplate attribute and leaves out boilerplate' => $repeatTwice(
                $htmlDocument('<html amp i-amphtml-no-boilerplate>', '<style>h1{color:red}</style><noscript><style>h2{color:blue}</style></noscript>')
            ),
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \AmpProject\Optimizer\Transformer\AmpBoilerplate::transform()
     * @dataProvider dataTransform()
     *
     * @param string $source       String of source HTML.
     * @param string $expectedHtml String of expected HTML output.
     */
    public function testTransform($source, $expectedHtml)
    {
        $document    = Document::fromHtml($source);
        $transformer = new AmpBoilerplate();
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertSimilarMarkup($expectedHtml, $document->saveHTML());
    }
}
