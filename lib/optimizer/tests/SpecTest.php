<?php

namespace Amp\Optimizer;

use Amp\Dom\Document;
use Amp\Optimizer\Tests\MarkupComparison;
use Amp\Optimizer\Transformer\ReorderHead;
use Amp\Optimizer\Transformer\ServerSideRendering;
use DirectoryIterator;
use PHPUnit\Framework\TestCase;

final class SpecTest extends TestCase
{

    use MarkupComparison;

    const TRANSFORMER_SPEC_PATH = __DIR__ . '/spec/transformers/valid';

    public function dataTransformerSpecFiles()
    {
        $scenarios = [];
        $suites    = [
            'ReorderHead'         => [ReorderHead::class, self::TRANSFORMER_SPEC_PATH . '/ReorderHeadTransformer'],
            'ServerSideRendering' => [ServerSideRendering::class, self::TRANSFORMER_SPEC_PATH . '/ServerSideRendering'],
        ];

        foreach ($suites as $key => $suite) {
            list($transformerClass, $specFileFolder) = $suite;

            foreach (new DirectoryIterator($specFileFolder) as $subFolder) {
                if ($subFolder->isFile() || $subFolder->isDot()) {
                    continue;
                }

                $scenarios["{$key} - {$subFolder}"] = [
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
     * @param string $source   Source file to transform.
     * @param string $expected Expected transformed result.
     */
    public function testTransformerSpecFiles($transformerClass, $source, $expected)
    {
        $document    = Document::fromHtmlFragment($source);
        $transformer = new $transformerClass();
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertSimilarMarkup($expected, $document->saveHTMLFragment());
    }
}
