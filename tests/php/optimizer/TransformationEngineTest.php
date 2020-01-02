<?php

namespace Amp\Optimizer;

use Amp\AmpWP\Dom\Document;
use PHPUnit\Framework\TestCase;

final class TransformationEngineTest extends TestCase
{

    const MINIMAL_HTML_MARKUP           = '<html></html>';
    const MINIMAL_OPTIMIZED_HTML_MARKUP = '<!DOCTYPE html><html i-amphtml-layout="" i-amphtml-no-boilerplate=""><head><meta charset="utf-8"></head><body></body></html>';

    /**
     * Provide data to test optimizing a string of HTML.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataOptimizeHtml()
    {
        return [
            'base_htm_conversion' => [
                self::MINIMAL_HTML_MARKUP,
                self::MINIMAL_OPTIMIZED_HTML_MARKUP,
            ],
        ];
    }

    /**
     * Test optimizing a string of HTML.
     *
     * @dataProvider dataOptimizeHtml
     *
     * @param string $source   Source HTML string to optimize.
     * @param string $expected Expected HTML output.
     */
    public function testOptimizeHtml($source, $expected)
    {
        $engine = new TransformationEngine(new Configuration());
        $this->assertEqualMarkup($expected, $engine->optimizeHtml($source));
    }

    /**
     * Test optimizing a DOM object directly.
     *
     * We're only testing the flow once here, to make sure all typing and plumbing works.
     * All conversion details will be the same as with optimizeHtml, so there's no point
     * in testing everything twice.
     */
    public function testOptimizeDom()
    {
        $dom    = Document::from_html(self::MINIMAL_HTML_MARKUP);
        $engine = new TransformationEngine(new Configuration([]));
        $engine->optimizeDom($dom);
        $this->assertEqualMarkup(self::MINIMAL_OPTIMIZED_HTML_MARKUP, $dom->saveHTML());
    }

    /**
     * Assert markup is equal.
     *
     * @param string $expected Expected markup.
     * @param string $actual   Actual markup.
     */
    public function assertEqualMarkup($expected, $actual)
    {
        $actual   = preg_replace('/\s+/', ' ', $actual);
        $expected = preg_replace('/\s+/', ' ', $expected);
        $actual   = preg_replace('/(?<=>)\s+(?=<)/', '', trim($actual));
        $expected = preg_replace('/(?<=>)\s+(?=<)/', '', trim($expected));

        $this->assertEquals(
            array_filter(preg_split('#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE)),
            array_filter(preg_split('#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE))
        );
    }
}
