<?php

namespace AmpProject\Optimizer;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\Tests\MarkupComparison;
use AmpProject\Optimizer\Tests\TestMarkup;
use AmpProject\Optimizer\Transformer\AmpRuntimeCss;
use AmpProject\Optimizer\Transformer\ReorderHead;
use AmpProject\Optimizer\Transformer\ServerSideRendering;
use AmpProject\RemoteRequest\StubbedRemoteGetRequest;
use DirectoryIterator;
use PHPUnit\Framework\TestCase;

/**
 * Test the individual transformers against the NodeJS spec test suite.
 *
 * @package ampproject/optimizer
 */
final class SpecTest extends TestCase
{
    use MarkupComparison;

    const TRANSFORMER_SPEC_PATH = __DIR__ . '/spec/transformers/valid';

    const TESTS_TO_SKIP = [
        'ReorderHead - reorders_head_a4a'                => 'see https://github.com/ampproject/amp-toolbox/issues/583',
        'ReorderHead - reorders_head_amphtml'            => 'see https://github.com/ampproject/amp-toolbox/issues/583',
        'ReorderHead - preserves_amp_custom_style_order' => 'see https://github.com/ampproject/amp-toolbox/issues/604',
    ];

    const CLASS_SKIP_TEST = '__SKIP__';

    /**
     * Associative array of mapping data for stubbing remote requests for specific tests.
     *
     * @todo This is a temporary fix only to get the test to pass with our current transformer.
     *       We'll need to adapt the transformer to take the following changes into account:
     *       https://github.com/ampproject/amp-toolbox/commit/b154a73c6dc9231e4060434c562a90d983e2a46d
     *
     * @var array
     */
    const STUBBED_REMOTE_REQUESTS_FOR_TESTS = [
        'AmpRuntimeCss - always_inlines_v0css' => [
            'https://cdn.ampproject.org/v0.css' => '/* v0-prod.css */',
        ],
    ];

    public function dataTransformerSpecFiles()
    {
        $scenarios = [];
        $suites    = [
            'ReorderHead'         => [ReorderHead::class, self::TRANSFORMER_SPEC_PATH . '/ReorderHeadTransformer'],
            'ServerSideRendering' => [ServerSideRendering::class, self::TRANSFORMER_SPEC_PATH . '/ServerSideRendering'],
            'AmpRuntimeCss'       => [
                AmpRuntimeCss::class,
                self::TRANSFORMER_SPEC_PATH . '/AmpBoilerplateTransformer',
            ],
        ];

        foreach ($suites as $key => list($transformerClass, $specFileFolder)) {
            foreach (new DirectoryIterator($specFileFolder) as $subFolder) {
                if ($subFolder->isFile() || $subFolder->isDot()) {
                    continue;
                }

                $scenario = "{$key} - {$subFolder}";

                if (array_key_exists($scenario, self::TESTS_TO_SKIP)) {
                    $scenarios[$scenario] = [
                        $scenario,
                        self::CLASS_SKIP_TEST,
                        $scenario,
                        self::TESTS_TO_SKIP[$scenario],
                    ];

                    continue;
                }

                $scenarios[$scenario] = [
                    $scenario,
                    $transformerClass,
                    file_get_contents("{$subFolder->getPathname()}/input.html"),
                    file_get_contents("{$subFolder->getPathname()}/expected_output.html"),
                ];
            }
        }

        return $scenarios;
    }

    /**
     * Test the transformers against their spec files.
     *
     * @dataProvider dataTransformerSpecFiles
     *
     * @param string $scenario         Test scenario.
     * @param string $transformerClass Class of the transformer to test.
     * @param string $source           Source file to transform.
     * @param string $expected         Expected transformed result.
     */
    public function testTransformerSpecFiles($scenario, $transformerClass, $source, $expected)
    {
        if ($transformerClass === self::CLASS_SKIP_TEST) {
            // $source contains the scenario name, $expected the reason.
            $this->markTestSkipped("Skipping {$source}, {$expected}");
        }

        $document = Document::fromHtmlFragment($source);

        $transformer = $this->getTransformer($scenario, $transformerClass);
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertSimilarMarkup($expected, $document->saveHTMLFragment());
    }

    /**
     * Get the transformer to test.
     *
     * @param string $scenario         Test scenario.
     * @param string $transformerClass Class of the transformer to get.
     * @return Transformer Instantiated transformer object.
     */
    private function getTransformer($scenario, $transformerClass)
    {
        $arguments = [];

        if (is_a($transformerClass, MakesRemoteRequests::class, true)) {
            $stubbedRequests = TestMarkup::STUBBED_REMOTE_REQUESTS;

            if (array_key_exists($scenario, self::STUBBED_REMOTE_REQUESTS_FOR_TESTS)) {
                $stubbedRequests = array_merge($stubbedRequests, self::STUBBED_REMOTE_REQUESTS_FOR_TESTS[$scenario]);
            }

            $arguments[] = new StubbedRemoteGetRequest($stubbedRequests);
        }

        if (is_a($transformerClass, Configurable::class, true)) {
            $arguments[] = (new Configuration())->getTransformerConfiguration($transformerClass);
        }

        return new $transformerClass(...$arguments);
    }
}
