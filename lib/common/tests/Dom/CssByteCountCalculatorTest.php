<?php

namespace AmpProject\Common;

use AmpProject\Dom\CssByteCountCalculator;
use AmpProject\Dom\Document;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AmpProject\Dom\CssByteCountCalculator.
 *
 * @covers  CssByteCountCalculator
 * @package ampproject/common
 */
class CssByteCountCalculatorTest extends TestCase
{

    /**
     * Data provider for testing the calculate() method.
     *
     * @return array Testing data.
     */
    public function dataCalculate()
    {
        return [
            'amp_custom_style_tag' => [
                '<html><head><style amp-custom>12345</style>', 5,
            ],
            'one_inline_style_attribute' => [
                '<html><body><div style="12345"></div></body></html>', 5,
            ],
            'multiple_inline_style_attributes' => [
                '<html><body><div style="1234"></div><div style="567"><div style="89"></body></html>', 9,
            ],
            'amp_custom_style_tag_and_multiple_inline_style_attributes' => [
                '<html><head><style amp-custom>12345</style></head><body><div style="1234"></div><div style="567"><div style="89"></body></html>', 14,
            ],
            'amp_custom_style_tag_outside_head' => [
                '<html><head><style amp-custom>12345</style></head><body><style amp-custom>123</style></body></html>', 5,
            ],
            'multibyte_chars_are_counted_in_bytes_not_chars' => [
                '<html><head><style amp-custom>Iñtërnâtiônàlizætiøn</style></head><body><div style="Iñtërnâtiônàlizætiøn"></div></body></html>', 54,
            ],
        ];
    }

    /**
     * Test the calculate() method.
     *
     * @dataProvider dataCalculate
     * @covers       CssByteCountCalculator::calculate()
     */
    public function testCalculate($html, $expected)
    {
        $document = Document::fromHtml($html);
        $this->assertEquals($expected, (new CssByteCountCalculator($document))->calculate());
    }
}
