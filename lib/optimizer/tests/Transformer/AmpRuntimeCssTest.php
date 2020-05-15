<?php

namespace AmpProject\Optimizer\Transformer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\Configuration\AmpRuntimeCssConfiguration;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\Tests\MarkupComparison;
use AmpProject\Optimizer\Tests\TestMarkup;
use AmpProject\RemoteRequest\StubbedRemoteGetRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test the AmpRuntimeCss transformer.
 *
 * @package ampproject/optimizer
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

                [AmpRuntimeCssConfiguration::CANARY => true],
            ],

            'override version via config' => [
                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET . TestMarkup::STYLE_AMPRUNTIME . '</head></html>',

                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET .
                '<style amp-runtime="" i-amphtml-version="012345678901234">/* 012345678901234/v0.css */</style>' .
                '</head><body></body></html>',

                [AmpRuntimeCssConfiguration::VERSION => '012345678901234'],
            ],

            'override styles via config' => [
                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET . TestMarkup::STYLE_AMPRUNTIME . '</head></html>',

                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET .
                '<style amp-runtime="" i-amphtml-version="012345678900000">/* custom-styles-v0.css */</style>' .
                '</head><body></body></html>',

                [AmpRuntimeCssConfiguration::STYLES => '/* custom-styles-v0.css */'],
            ],

            'trying to inline invalid version results in linked stylesheet' => [
                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET . TestMarkup::STYLE_AMPRUNTIME . '</head></html>',

                TestMarkup::DOCTYPE . '<html><head>' . TestMarkup::META_CHARSET .
                '<link rel="stylesheet" href="https://cdn.ampproject.org/v0.css">' .
                '</head><body></body></html>',

                [AmpRuntimeCssConfiguration::VERSION => '0000000000000000'],
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \AmpProject\Optimizer\Transformer\AmpRuntimeCss::transform()
     * @dataProvider dataTransform()
     *
     * @param string     $source       String of source HTML.
     * @param string     $expectedHtml String of expected HTML output.
     * @param array|null $config       Optional. Array of configuration data to use. Omit for default configuration.
     */
    public function testTransform($source, $expectedHtml, $config = [])
    {
        $document      = Document::fromHtml($source);
        $remoteRequest = new StubbedRemoteGetRequest(TestMarkup::STUBBED_REMOTE_REQUESTS);
        $transformer   = new AmpRuntimeCss(new AmpRuntimeCssConfiguration($config), $remoteRequest);
        $errors        = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertEqualMarkup($expectedHtml, $document->saveHTML());
    }
}
