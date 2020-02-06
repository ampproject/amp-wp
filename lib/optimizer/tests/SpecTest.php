<?php

namespace Amp\Optimizer;

use Amp\Dom\Document;
use Amp\Optimizer\Tests\MarkupComparison;
use Amp\Optimizer\Tests\TestMarkup;
use Amp\Optimizer\Transformer\AmpRuntimeCss;
use Amp\Optimizer\Transformer\ReorderHead;
use Amp\Optimizer\Transformer\ServerSideRendering;
use Amp\RemoteRequest\StubbedRemoteRequest;
use DirectoryIterator;
use PHPUnit\Framework\TestCase;

/**
 * Test the individual transformers against the NodeJS spec test suite.
 *
 * @package amp/optimizer
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
                        self::CLASS_SKIP_TEST,
                        $scenario,
                        self::TESTS_TO_SKIP[$scenario],
                    ];

                    continue;
                }

                $scenarios[$scenario] = [
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
     * @param string $transformerClass Class of the transformer to test.
     * @param string $source           Source file to transform.
     * @param string $expected         Expected transformed result.
     */
    public function testTransformerSpecFiles($transformerClass, $source, $expected)
    {
        if ($transformerClass === self::CLASS_SKIP_TEST) {
            // $source contains the scenario name, $expected the reason.
            $this->markTestSkipped("Skipping {$source}, {$expected}");
        }

        $document = Document::fromHtmlFragment($source);

        $transformer = $this->getTransformer($transformerClass);
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertSimilarMarkup($expected, $document->saveHTMLFragment());
    }

    /**
     * Get the transformer to test.
     *
     * @param string $transformerClass Class of the transformer to get.
     * @return Transformer Instantiated transformer object.
     */
    private function getTransformer($transformerClass)
    {
        $arguments = [];

        if (is_a($transformerClass, MakesRemoteRequests::class, true)) {
            $arguments[] = new StubbedRemoteRequest(TestMarkup::STUBBED_REMOTE_REQUESTS);
        }

        if (is_a($transformerClass, Configurable::class, true)) {
            $arguments[] = (new Configuration())->getTransformerConfiguration($transformerClass);
        }

        return new $transformerClass(...$arguments);
    }
}
