<?php

namespace Amp\Optimizer\Transformer;

use Amp\Amp;
use Amp\Attribute;
use Amp\Dom\Document;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Tests\MarkupComparison;
use Amp\Optimizer\Tests\TestMarkup;
use PHPUnit\Framework\TestCase;

/**
 * Test the AmpBoilerplate transformer.
 *
 * @package amp/optimizer
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
        $inputWithBoilerplate = static function ($html, $boilerplate) {
            return TestMarkup::DOCTYPE . $html . '<head>' .
                   TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME .
                   TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                   $boilerplate .
                   '</head><body></body></html>';
        };

        $inputWithoutBoilerplate = static function ($html) {
            return TestMarkup::DOCTYPE . $html . '<head>' .
                   TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME .
                   TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                   '</head><body></body></html>';
        };

        $expected = static function ($html, $boilerplate) {
            return TestMarkup::DOCTYPE . $html . '<head>' .
                   TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME .
                   TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL .
                   $boilerplate .
                   '</head><body></body></html>';
        };

        $ampBoilerplate = '<style ' . Attribute::AMP_BOILERPLATE . '>' . Amp::BOILERPLATE_CSS . '</style>' .
                          '<noscript><style ' . Attribute::AMP_BOILERPLATE . '>' . Amp::BOILERPLATE_NOSCRIPT_CSS . '</style></noscript>';

        $amp4AdsBoilerplate = '<style ' . Attribute::AMP4ADS_BOILERPLATE . '>' . Amp::AMP4ADS_AND_AMP4EMAIL_BOILERPLATE_CSS . '</style>';

        $amp4EmailBoilerplate = '<style ' . Attribute::AMP4EMAIL_BOILERPLATE . '>' . Amp::AMP4ADS_AND_AMP4EMAIL_BOILERPLATE_CSS . '</style>';

        return [
            'keeps boilerplate' => [
                $inputWithBoilerplate('<html ⚡>', $ampBoilerplate),
                $expected('<html ⚡>', $ampBoilerplate),
            ],

            'keeps boilerplate for amp4ads' => [
                $inputWithBoilerplate('<html amp4ads>', $amp4AdsBoilerplate),
                $expected('<html amp4ads>', $amp4AdsBoilerplate),
            ],

            'keeps boilerplate for ⚡4ads' => [
                $inputWithBoilerplate('<html ⚡4ads>', $amp4AdsBoilerplate),
                $expected('<html ⚡4ads>', $amp4AdsBoilerplate),
            ],

            'keeps boilerplate for amp4email' => [
                $inputWithBoilerplate('<html amp4email>', $amp4EmailBoilerplate),
                $expected('<html amp4email>', $amp4EmailBoilerplate),
            ],

            'keeps boilerplate for ⚡4email' => [
                $inputWithBoilerplate('<html ⚡4email>', $amp4EmailBoilerplate),
                $expected('<html ⚡4email>', $amp4EmailBoilerplate),
            ],

            'adds boilerplate if missing' => [
                $inputWithoutBoilerplate('<html ⚡>'),
                $expected('<html ⚡>', $ampBoilerplate),
            ],

            'adds boilerplate if missing for amp4ads' => [
                $inputWithoutBoilerplate('<html amp4ads>'),
                $expected('<html amp4ads>', $amp4AdsBoilerplate),
            ],

            'adds boilerplate if missing for ⚡4ads' => [
                $inputWithoutBoilerplate('<html ⚡4ads>'),
                $expected('<html ⚡4ads>', $amp4AdsBoilerplate),
            ],

            'adds boilerplate if missing for amp4email' => [
                $inputWithoutBoilerplate('<html amp4email>'),
                $expected('<html amp4email>', $amp4EmailBoilerplate),
            ],

            'adds boilerplate if missing for ⚡4email' => [
                $inputWithoutBoilerplate('<html ⚡4email>'),
                $expected('<html ⚡4email>', $amp4EmailBoilerplate),
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \Amp\Optimizer\Transformer\AmpBoilerplate::transform()
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
