<?php

namespace Amp\Optimizer\Transformer;

use Amp\Dom\Document;
use Amp\Optimizer\Configuration\AmpRuntimeCssConfiguration;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Tests\MarkupComparison;
use Amp\Optimizer\Tests\TestMarkup;
use Amp\RemoteRequest\StubbedRemoteRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test the AmpRuntimeCss transformer.
 *
 * @package amp/optimizer
 */
final class AmpRuntimeCssTest extends TestCase
{

    use MarkupComparison;

    /**
     * Provide the data to test the transform() method.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataTransform()
    {
        return [
            'inline regular css' => [
                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET . TestMarkup::STYLE_AMPRUNTIME . '</head></html>',

                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET .
                '<style amp-runtime="" i-amphtml-version="012345678900000">/* v0.css */</style>' .
                '</head><body></body></html>',
            ],

            'inline canary css' => [
                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET . TestMarkup::STYLE_AMPRUNTIME . '</head></html>',

                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET .
                '<style amp-runtime="" i-amphtml-version="023456789000000">/* v0.css */</style>' .
                '</head><body></body></html>',

                ['canary' => true],
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \Amp\Optimizer\Transformer\AmpRuntimeCss::transform()
     * @dataProvider dataTransform()
     *
     * @param string     $source       String of source HTML.
     * @param string     $expectedHtml String of expected HTML output.
     * @param array|null $config       Optional. Array of configuration data to use. Omit for default configuration.
     */
    public function testTransform($source, $expectedHtml, $config = [])
    {
        $document      = Document::fromHtml($source);
        $remoteRequest = new StubbedRemoteRequest(TestMarkup::STUBBED_REMOTE_REQUESTS);
        $transformer   = new AmpRuntimeCss($remoteRequest, new AmpRuntimeCssConfiguration($config));
        $errors        = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertEqualMarkup($expectedHtml, $document->saveHTML());
    }
}
