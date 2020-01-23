<?php
/**
 * Tests for Amp\Dom\Document.
 *
 * @package amp/common
 */

use Amp\Dom\Document;
use Amp\Extension;
use Amp\Tag;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Amp\Extension.
 *
 * @covers Extension
 */
class ExtensionTest extends TestCase
{

    /**
     * Provide data for the testIsRenderDelaying method.
     *
     * @return array[] Array
     */
    public function dataIsRenderDelayingExtension()
    {
        return [
            Extension::DYNAMIC_CSS_CLASSES => [Extension::DYNAMIC_CSS_CLASSES, true],
            Extension::EXPERIMENT          => [Extension::EXPERIMENT, true],
            Extension::STORY               => [Extension::STORY, true],
            'amp-custom'                   => ['amp-custom', false],
            'amp-bind'                     => ['amp-bind', false],
        ];
    }

    /**
     * Test the render delaying check method.
     *
     * @dataProvider dataIsRenderDelayingExtension
     * @covers       Extension::isRenderDelayingExtension()
     *
     * @param string $extensionName Name of the extension to check.
     * @param bool   $expected      Expected boolean result.
     */
    public function testIsRenderDelayingExtension($extensionName, $expected)
    {
        $dom     = new Document();
        $element = $dom->createElement(Tag::SCRIPT);
        $element->setAttribute(Extension::CUSTOM_ELEMENT, $extensionName);
        $this->assertEquals($expected, Extension::isRenderDelayingExtension($element));
    }

    /**
     * Provide data for the testIsCustomElement method.
     *
     * @return array[] Array
     */
    public function dataIsCustomElement()
    {
        $dom = new Document();
        return [
            Extension::EXPERIMENT => [$dom->createElement(Extension::EXPERIMENT), true],
            Extension::STORY      => [$dom->createElement(Extension::STORY), true],
            'div'                 => [$dom->createElement('div'), false],
            'custom-amp'          => [$dom->createElement('custom-amp'), false],
        ];
    }

    /**
     * Test the check whether a given node is an Amp custom element.
     *
     * @dataProvider dataIsCustomElement
     * @covers       Extension::isCustomElement()
     *
     * @param DOMNode $node     Node to check
     * @param bool    $expected Expected boolean result.
     */
    public function testIsCustomElement(DOMNode $node, $expected)
    {
        $this->assertEquals($expected, Extension::isCustomElement($node));
    }
}
