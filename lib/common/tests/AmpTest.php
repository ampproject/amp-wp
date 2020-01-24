<?php
/**
 * Tests for Amp\Amp.
 *
 * @package amp/common
 */

use Amp\Amp;
use Amp\Attribute;
use Amp\Dom\Document;
use Amp\Extension;
use Amp\Tag;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Amp\Amp.
 *
 * @covers Amp
 */
class AmpTest extends TestCase
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
            Extension::BIND                => [Extension::BIND, false],
            'amp-custom'                   => ['amp-custom', false],
        ];
    }

    /**
     * Test the render delaying check method.
     *
     * @dataProvider dataIsRenderDelayingExtension
     * @covers       Amp::isRenderDelayingExtension()
     *
     * @param string $extensionName Name of the extension to check.
     * @param bool   $expected      Expected boolean result.
     */
    public function testIsRenderDelayingExtension($extensionName, $expected)
    {
        $dom     = new Document();
        $element = $dom->createElement(Tag::SCRIPT);
        $element->setAttribute(Attribute::CUSTOM_ELEMENT, $extensionName);
        $this->assertEquals($expected, Amp::isRenderDelayingExtension($element));
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
     * @covers       Amp::isCustomElement()
     *
     * @param DOMNode $node     Node to check
     * @param bool    $expected Expected boolean result.
     */
    public function testIsCustomElement(DOMNode $node, $expected)
    {
        $this->assertEquals($expected, Amp::isCustomElement($node));
    }

    /**
     * Provide data for the testGetExtensionName method.
     *
     * @return array[] Array
     */
    public function dataGetExtensionName()
    {
        $dom           = new Document();
        $customElement = $dom->createElement(Tag::SCRIPT);
        $customElement->setAttribute(Attribute::CUSTOM_ELEMENT, 'amp-custom-element-example');

        $customTemplate = $dom->createElement(Tag::SCRIPT);
        $customTemplate->setAttribute(Attribute::CUSTOM_TEMPLATE, 'amp-custom-template-example');

        return [
            Attribute::CUSTOM_ELEMENT  => [$customElement, 'amp-custom-element-example'],
            Attribute::CUSTOM_TEMPLATE => [$customTemplate, 'amp-custom-template-example'],
            'script-without-attribute' => [$dom->createElement(Tag::SCRIPT), ''],
            'template-tag'             => [$dom->createElement(Tag::TEMPLATE), ''],
            'non-element'              => [$dom->createTextNode(Attribute::CUSTOM_ELEMENT), ''],
        ];
    }

    /**
     * Test the check whether a given node is an Amp custom element.
     *
     * @dataProvider dataGetExtensionName
     * @covers       Amp::isCustomElement()
     *
     * @param DOMNode $node     Node to check
     * @param bool    $expected Expected boolean result.
     */
    public function testGetExtensionName(DOMNode $node, $expected)
    {
        $this->assertEquals($expected, Amp::getExtensionName($node));
    }
}
