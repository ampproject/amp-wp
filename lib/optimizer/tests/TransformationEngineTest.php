<?php

namespace Amp\Optimizer;

use Amp\Dom\Document;
use Amp\Optimizer\Tests\MarkupComparison;
use Amp\Optimizer\Tests\TestMarkup;
use PHPUnit\Framework\TestCase;

final class TransformationEngineTest extends TestCase
{

    use MarkupComparison;

    const MINIMAL_HTML_MARKUP           = '<html></html>';
    const MINIMAL_OPTIMIZED_HTML_MARKUP = TestMarkup::DOCTYPE . '<html transformed="self;v=1" i-amphtml-layout="" i-amphtml-no-boilerplate=""><head>' . TestMarkup::META_CHARSET . '</head><body></body></html>';

    /**
     * Provide data to test optimizing a string of HTML.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataOptimizeHtml()
    {
        return [
            'base_html_conversion' => [
                self::MINIMAL_HTML_MARKUP,
                self::MINIMAL_OPTIMIZED_HTML_MARKUP,
            ],
        ];
    }

    /**
     * Test optimizing a string of HTML.
     *
     * @covers \Amp\Optimizer\TransformationEngine::optimizeHtml()
     * @dataProvider dataOptimizeHtml
     *
     * @param string $source   Source HTML string to optimize.
     * @param string $expected Expected HTML output.
     */
    public function testOptimizeHtml($source, $expected)
    {
        $engine = new TransformationEngine(new Configuration());
        $errors = new ErrorCollection();
        $this->assertEqualMarkup($expected, $engine->optimizeHtml($source, $errors));
        $this->assertCount(0, $errors);
    }

    /**
     * Test optimizing a DOM object directly.
     *
     * We're only testing the flow once here, to make sure all typing and plumbing works.
     * All conversion details will be the same as with optimizeHtml, so there's no point
     * in testing everything twice.
     *
     * @covers \Amp\Optimizer\TransformationEngine::optimizeDom()
     */
    public function testOptimizeDom()
    {
        $dom    = Document::fromHtml(self::MINIMAL_HTML_MARKUP);
        $engine = new TransformationEngine(new Configuration([]));
        $errors = new ErrorCollection();
        $engine->optimizeDom($dom, $errors);
        $this->assertEqualMarkup(self::MINIMAL_OPTIMIZED_HTML_MARKUP, $dom->saveHTML());
        $this->assertCount(0, $errors);
    }
}
