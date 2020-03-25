<?php

namespace AmpProject\Common;

use AmpProject\DevMode;
use AmpProject\Dom\Document;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AmpProject\DevMode.
 *
 * @covers  \AmpProject\DevMode
 * @package ampproject/common
 */
class DevModeTest extends TestCase
{

    public function dataIsActiveForDocument()
    {
        return [
            [ '<html><body></body></html>', false ],
            [ '<html data-ampdevmode><body></body></html>', true ],
        ];
    }

    /** @dataProvider dataIsActiveForDocument */
    public function testIsActiveForDocument($html, $expected)
    {
        $document = Document::fromHtml($html);
        $this->assertEquals($expected, DevMode::isActiveForDocument($document));
    }

    public function dataHasExemptionForNode()
    {
        return [
            [ '<html><body id="node_to_test"><div data-ampdevmode></div></body></html>', false ],
            [ '<html data-ampdevmode><body><div id="node_to_test" data-ampdevmode></div></body></html>', true ],
        ];
    }

    /** @dataProvider dataHasExemptionForNode */
    public function testHasExemptionForNode($html, $expected)
    {
        $document = Document::fromHtml($html);
        $node = $document->xpath->query('//*[@id="node_to_test"]')->item(0);
        $this->assertEquals($expected, DevMode::hasExemptionForNode($node));
    }

    public function dataIsExemptFromValidation()
    {
        return [
            [ '<html><body id="node_to_test"><div data-ampdevmode></div></body></html>', false ],
            [ '<html><body><div id="node_to_test" data-ampdevmode></div></body></html>', false ],
            [ '<html data-ampdevmode><body id="node_to_test"><div data-ampdevmode></div></body></html>', false ],
            [ '<html data-ampdevmode><body><div id="node_to_test" data-ampdevmode></div></body></html>', true ],
        ];
    }

    /** @dataProvider dataIsExemptFromValidation */
    public function testIsExemptFromValidation($html, $expected)
    {
        $document = Document::fromHtml($html);
        $node = $document->xpath->query('//*[@id="node_to_test"]')->item(0);
        $this->assertEquals($expected, DevMode::isExemptFromValidation($node));
    }
}
