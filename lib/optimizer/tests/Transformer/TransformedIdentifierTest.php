<?php

namespace Amp\Optimizer\Transformer;

use Amp\Dom\Document;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Tests\MarkupComparison;
use Amp\Optimizer\Tests\TestMarkup;
use PHPUnit\Framework\TestCase;

final class TransformedIdentifierTest extends TestCase
{

    use MarkupComparison;

    /**
     * Provide the data to test the transform() method.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataTransform()
    {
        $input = static function ($html) {
            return TestMarkup::DOCTYPE . $html . '<head>'
                   . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                   . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE
                   . '</head><body></body></html>';
        };

        $expected = static function ($html) {
            return TestMarkup::DOCTYPE . $html . '<head>'
                   . TestMarkup::META_CHARSET . TestMarkup::META_VIEWPORT . TestMarkup::SCRIPT_AMPRUNTIME
                   . TestMarkup::LINK_FAVICON . TestMarkup::LINK_CANONICAL . TestMarkup::STYLE_AMPBOILERPLATE . TestMarkup::NOSCRIPT_AMPBOILERPLATE
                   . '</head><body></body></html>';
        };

        return [
            'adds identifier with default version to html tag' => [
                $input('<html ⚡>'),
                $expected('<html ⚡ transformed="self;v=1">'),
            ],

            'adds identifier with custom version to html tag' => [
                $input('<html ⚡>'),
                $expected('<html ⚡ transformed="self;v=5">'),
                5,
            ],

            'adds identifier without version to html tag' => [
                $input('<html ⚡>'),
                $expected('<html ⚡ transformed="self">'),
                0,
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \Amp\Optimizer\Transformer\TransformedIdentifier::transform()
     * @dataProvider dataTransform()
     *
     * @param string   $source       String of source HTML.
     * @param string   $expectedHtml String of expected HTML output.
     * @param int|null $version      Version to use. Null to not specify a specific one and fall back to default.
     */
    public function testTransform($source, $expectedHtml, $version = null)
    {
        $document = Document::fromHtml($source);
        $config   = null;
        if ($version !== null) {
            $config = [TransformedIdentifier::CONFIG_KEY_VERSION => $version];
        }
        $transformer = new TransformedIdentifier($config);
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertEqualMarkup($expectedHtml, $document->saveHTML());
    }
}
