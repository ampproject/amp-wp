<?php

namespace Amp\Optimizer\Transformer;

use Amp\AmpWP\Dom\Document;
use Amp\Optimizer\Error;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Tests\ErrorComparison;
use Amp\Optimizer\Tests\MarkupComparison;
use PHPUnit\Framework\TestCase;

final class ServerSideRenderingTest extends TestCase
{

    use ErrorComparison;
    use MarkupComparison;

    /**
     * Provide the data to test the transform() method.
     *
     * @return array[] Associative array of data arrays.
     */
    public function dataTransform()
    {
        return [
            'base_html_transform'                            => [
                '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
                '<!DOCTYPE html><html i-amphtml-layout="" i-amphtml-no-boilerplate=""><head><style amp-runtime=""></style><meta charset="utf-8"></head><body></body></html>',
                [],
            ],
            'boilerplate_is_stripped'                        => [
                '<!DOCTYPE html><html><head><meta charset="utf-8"><style amp-boilerplate="1">h1{color:red;}</style></head><body></body></html>',
                '<!DOCTYPE html><html i-amphtml-layout="" i-amphtml-no-boilerplate=""><head><style amp-runtime=""></style><meta charset="utf-8"></head><body></body></html>',
                [],
            ],
            'boilerplate_is_not_stripped_if_heights_present' => [
                '<!DOCTYPE html><html><head><meta charset="utf-8"><style amp-boilerplate="1">h1{color:red;}</style></head><body><amp-text heights="1"></amp-text></body></html>',
                '<!DOCTYPE html><html i-amphtml-layout=""><head><style amp-runtime=""></style><meta charset="utf-8"><style amp-boilerplate="1">h1{color:red;}</style></head><body><amp-text heights="1"></amp-text></body></html>',
                [Error\CannotRemoveBoilerplate::CODE],
            ],
        ];
    }

    /**
     * Test the transform() method.
     *
     * @covers       \Amp\Optimizer\Transformer\ServerSideRendering::transform()
     * @dataProvider dataTransform()
     *
     * @param string                  $source         String of source HTML.
     * @param string                  $expectedHtml   String of expected HTML output.
     * @param ErrorCollection|Error[] $expectedErrors Set of expected errors.
     */
    public function testTransform($source, $expectedHtml, $expectedErrors)
    {
        $document    = Document::from_html($source);
        $transformer = new ServerSideRendering();
        $errors      = new ErrorCollection();

        $transformer->transform($document, $errors);

        $this->assertEqualMarkup($expectedHtml, $document->saveHTML());
        $this->assertSameErrors($expectedErrors, $errors);
    }
}
