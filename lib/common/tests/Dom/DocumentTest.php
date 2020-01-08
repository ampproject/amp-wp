<?php
/**
 * Tests for Amp\Dom\Document.
 *
 * @package amp/common
 */

use Amp\Dom\Document;
use Amp\Tests\AssertContainsCompatibility;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Amp\Dom\Document.
 *
 * @covers Document
 */
class DocumentTest extends TestCase
{

    use AssertContainsCompatibility;

    /**
     * Data for Amp\Dom\Document test.
     *
     * @return array Data.
     */
    public function dataDomDocument()
    {
        $head = '<head><meta charset="utf-8"></head>';

        return [
            'minimum_valid_document'                   => [
                'utf-8',
                '<!DOCTYPE html><html><head></head><body></body></html>',
                '<!DOCTYPE html><html>' . $head . '<body></body></html>',
            ],
            'valid_document_with_attributes'           => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
            ],
            'missing_head'                             => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en"><body class="some-class"><p>Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
            ],
            'missing_body'                             => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<p>Text</p></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body><p>Text</p></body></html>',
            ],
            'missing_head_and_body'                    => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en"><p>Text</p></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body><p>Text</p></body></html>',
            ],
            'missing_html_and_head_and_body'           => [
                'utf-8',
                '<!DOCTYPE html><p>Text</p>',
                '<!DOCTYPE html><html>' . $head . '<body><p>Text</p></body></html>',
            ],
            'content_only'                             => [
                'utf-8',
                '<p>Text</p>',
                '<!DOCTYPE html><html>' . $head . '<body><p>Text</p></body></html>',
            ],
            'missing_doctype'                          => [
                'utf-8',
                '<html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
            ],
            'html_4_doctype'                           => [
                'utf-8',
                '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
            ],
            'lots_of_whitespace'                       => [
                'utf-8',
                " \n <!DOCTYPE \n html \n > \n <html \n amp \n lang=\"en\"   \n  >  \n   <head >   \n<meta \n   charset=\"utf-8\">  \n  </head>  \n  <body   \n class=\"some-class\"  \n >  \n  <p>  \n  Text  \n  </p>  \n\n  </body  >  \n  </html  >  \n  ",
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p> Text </p></body></html>',
            ],
            'utf_8_encoding_predefined'                => [
                'utf-8',
                '<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>',
                '<!DOCTYPE html><html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
            ],
            'utf_8_encoding_guessed_via_charset'       => [
                '',
                '<html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body>',
                '<!DOCTYPE html><html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
            ],
            'utf_8_encoding_guessed_via_content'       => [
                '',
                '<p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p>',
                '<!DOCTYPE html><html>' . $head . '<body><p>مرحبا بالعالم! Check out ‘this’ and “that” and—other things.</p></body></html>',
            ],
            'iso_8859_1_encoding_predefined'           => [
                'iso-8859-1',
                utf8_decode('<!DOCTYPE html><html><head></head><body><p>ÄÖÜ</p></body></html>'),
                '<!DOCTYPE html><html>' . $head . '<body><p>ÄÖÜ</p></body></html>',
            ],
            'iso_8859_1_encoding_guessed_via_charset'  => [
                '',
                utf8_decode('<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /></head><body><p>ÄÖÜ</p></body></html>'),
                '<!DOCTYPE html><html>' . $head . '<body><p>ÄÖÜ</p></body></html>',
            ],
            'iso_8859_1_encoding_guessed_via_content'  => [
                '',
                utf8_decode('<!DOCTYPE html><html><body><p>ÄÖÜ</p></body></html>'),
                '<!DOCTYPE html><html>' . $head . '<body><p>ÄÖÜ</p></body></html>',
            ],
            'raw_iso_8859_1'                           => [
                '',
                utf8_decode('ÄÖÜ'),
                '<!DOCTYPE html><html>' . $head . '<body>ÄÖÜ</body></html>',
            ],
            // Make sure we correctly identify the ISO-8859 sub-charsets ("€" does not exist in ISO-8859-1).
            'iso_8859_15_encoding_predefined'          => [
                'iso-8859-1',
                mb_convert_encoding('<!DOCTYPE html><html><head></head><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8'),
                '<!DOCTYPE html><html>' . $head . '<body><p>€</p></body></html>',
            ],
            'iso_8859_15_encoding_guessed_via_charset' => [
                '',
                mb_convert_encoding('<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-15" /></head><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8'),
                '<!DOCTYPE html><html>' . $head . '<body><p>€</p></body></html>',
            ],
            'iso_8859_15_encoding_guessed_via_content' => [
                '',
                mb_convert_encoding('<!DOCTYPE html><html><body><p>€</p></body></html>', 'ISO-8859-15', 'UTF-8'),
                '<!DOCTYPE html><html>' . $head . '<body><p>€</p></body></html>',
            ],
            'raw_iso_8859_15'                          => [
                '',
                mb_convert_encoding('€', 'ISO-8859-15', 'UTF-8'),
                '<!DOCTYPE html><html>' . $head . '<body>€</body></html>',
            ],
        ];
    }

    /**
     * Tests loading and saving the content via Amp\Dom\Document.
     *
     * @param string $charset  Charset to use.
     * @param string $source   Source content.
     * @param string $expected Expected target content.
     *
     * @dataProvider dataDomDocument
     * @covers       Document::loadHTML()
     * @covers       Document::saveHTML()
     */
    public function testDomDocument($charset, $source, $expected)
    {
        $document = Document::fromHtml($source, $charset);
        $this->assertEqualMarkup($expected, $document->saveHTML());
    }

    /**
     * Assert markup is equal.
     *
     * @param string $expected Expected markup.
     * @param string $actual   Actual markup.
     */
    public function assertEqualMarkup($expected, $actual)
    {
        $actual   = preg_replace('/\s+/', ' ', $actual);
        $expected = preg_replace('/\s+/', ' ', $expected);
        $actual   = preg_replace('/(?<=>)\s+(?=<)/', '', trim($actual));
        $expected = preg_replace('/(?<=>)\s+(?=<)/', '', trim($expected));

        $this->assertEquals(
            array_filter(preg_split('#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE)),
            array_filter(preg_split('#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE))
        );
    }

    /**
     * Test convertAmpBindAttributes.
     *
     * @covers Document::convertAmpBindAttributes()
     */
    public function testAmpBindConversion()
    {
        $original  = '<amp-img width=300 height="200" data-foo="bar" selected src="/img/dog.jpg" [src]="myAnimals[currentAnimal].imageUrl"></amp-img>';
        $converted = Document::fromHtml($original)->saveHTML();
        $this->assertNotEquals($original, $converted);
        $this->assertStringContains(Document::AMP_BIND_DATA_ATTR_PREFIX . 'src="myAnimals[currentAnimal].imageUrl"', $converted);
        $this->assertStringContains('width="300" height="200" data-foo="bar" selected', $converted);

        // Check tag with self-closing attribute.
        $original  = '<input type="text" role="textbox" class="calc-input" id="liens" name="liens" [value]="(result1 != null) ? result1.liens : \'verifying…\'" />';
        $converted = Document::fromHtml($original)->saveHTML();
        $this->assertNotEquals($original, $converted);

        // Preserve trailing slash that is actually the attribute value.
        $original  = '<a href=/>Home</a>';
        $dom       = Document::fromHtml($original);
        $converted = $dom->saveHTML($dom->body->firstChild);
        $this->assertEquals('<a href="/">Home</a>', $converted);

        // Test malformed.
        $malformed_html = [
            '<amp-img width="123" [text]="..."</amp-img>',
            '<amp-img width="123" [text="..."]></amp-img>',
            '<amp-img width="123" [text]="..." *bad*></amp-img>',
        ];
        foreach ($malformed_html as $html) {
            $converted = Document::fromHtml($html)->saveHTML();
            $this->assertNotContains(Document::AMP_BIND_DATA_ATTR_PREFIX, $converted, "Source: {$html}");
        }
    }

    /**
     * Get Table Row Iterations.
     *
     * @return array An array of arrays holding an integer representation of iterations.
     */
    public function getTableRowIterations()
    {
        return [
            [1],
            [10],
            [100],
            [1000],
            [10000],
            [100000],
        ];
    }

    /**
     * Tests attribute conversions on content with iframe srcdocs of variable lengths.
     *
     * @dataProvider getTableRowIterations
     *
     * @param int $iterations The number of table rows to append to iframe srcdoc.
     */
    public function testAttributeConversionOnLongIframeSrcdocs($iterations)
    {
        $html = '<html amp><head><meta charset="utf-8"></head><body><table>';

        for ($i = 0; $i < $iterations; $i++) {
            $html .= '
				<tr>
				<td class="rank" style="width:2%;">1453</td>
				<td class="text" style="width:10%;">1947</td>
				<td class="text">Pittsburgh Ironmen</td>
				<td class="boolean" style="width:10%;text-align:center;"></td>
				<td class="number" style="width:10%;">1242</td>
				<td class="number">1192</td>
				<td class="number">1111</td>
				<td class="number highlight">1182</td>
				</tr>
			';
        }

        $html .= '</table></body></html>';

        $to_convert = sprintf(
            '<amp-iframe sandbox="allow-scripts" srcdoc="%s"> </amp-iframe>',
            htmlentities($html)
        );

        Document::fromHtml($to_convert)->saveHTML();

        $this->assertSame(PREG_NO_ERROR, preg_last_error(), 'Probably failed when backtrack limit was exhausted.');
    }

    /**
     * Test that HEAD and BODY elements are always present.
     *
     * @covers Document::normalizeDocumentStructure()
     */
    public function testEnsuringHeadBody()
    {
        // The meta charset tag that is automatically added needs to always be taken into account.

        $html = '<html><body><p>Hello</p></body></html>';
        $dom  = Document::fromHtml($html);
        $this->assertEquals('head', $dom->documentElement->firstChild->nodeName);
        $this->assertEquals(1, $dom->head->childNodes->length);
        $this->assertEquals('body', $dom->documentElement->lastChild->nodeName);
        $this->assertEquals($dom->body, $dom->getElementsByTagName('p')->item(0)->parentNode);

        $html = '<html><head><title>foo</title></head></html>';
        $dom  = Document::fromHtml($html);
        $this->assertEquals('head', $dom->documentElement->firstChild->nodeName);
        $this->assertEquals($dom->head->firstChild, $dom->getElementsByTagName('meta')->item(0));
        $this->assertEquals($dom->head->firstChild->nextSibling, $dom->getElementsByTagName('title')->item(0));
        $this->assertEquals('body', $dom->documentElement->lastChild->nodeName);
        $this->assertEquals(0, $dom->body->childNodes->length);

        $html = '<html><head><title>foo</title></head><p>no body</p></html>';
        $dom  = Document::fromHtml($html);
        $this->assertEquals('head', $dom->documentElement->firstChild->nodeName);
        $this->assertEquals($dom->head->firstChild, $dom->getElementsByTagName('meta')->item(0));
        $this->assertEquals($dom->head->firstChild->nextSibling, $dom->getElementsByTagName('title')->item(0));
        $p = $dom->getElementsByTagName('p')->item(0);
        $this->assertEquals($dom->body, $p->parentNode);
        $this->assertEquals('no body', $p->textContent);

        $html = 'Hello world';
        $dom  = Document::fromHtml($html);
        $this->assertEquals('head', $dom->documentElement->firstChild->nodeName);
        $this->assertEquals(1, $dom->head->childNodes->length);
        $this->assertEquals('body', $dom->documentElement->lastChild->nodeName);
        $this->assertEquals('Hello world', $dom->body->lastChild->textContent);
    }

    /**
     * Test that invalid head nodes are moved to body.
     *
     * @covers Document::moveInvalidHeadNodesToBody()
     */
    public function testInvalidHeadNodes()
    {
        // The meta charset tag that is automatically added needs to always be taken into account.

        // Text node.
        $html = '<html><head>text</head><body><span>end</span></body></html>';
        $dom  = Document::fromHtml($html);
        $this->assertEquals('meta', $dom->head->firstChild->tagName);
        $this->assertNull($dom->head->firstChild->nextSibling);
        $body_first_child = $dom->body->firstChild;
        $this->assertInstanceOf('DOMElement', $body_first_child);
        $this->assertEquals('text', $body_first_child->textContent);

        // Valid nodes.
        $html = '<html><head><!--foo--><title>a</title><base href="/"><meta name="foo" content="bar"><link rel="test" href="/"><style></style><noscript><img src="http://example.com/foo.png"></noscript><script></script></head><body></body></html>';
        $dom  = Document::fromHtml($html);
        $this->assertEquals(9, $dom->head->childNodes->length);
        $this->assertNull($dom->body->firstChild);

        // Invalid nodes.
        $html = '<html><head><?pi ?><span></span><div></div><p>hi</p><img src="https://example.com"><iframe src="/"></iframe></head><body></body></html>';
        $dom  = Document::fromHtml($html);
        $this->assertEquals('meta', $dom->head->firstChild->tagName);
        $this->assertNull($dom->head->firstChild->nextSibling);
        $this->assertEquals(6, $dom->body->childNodes->length);
    }

    /**
     * Get head node data.
     *
     * @return array Head node data.
     */
    public function getHeadNodeData()
    {
        $dom = new Document();

        $newNode = static function ($tag, $attributes) use ($dom) {
            $node = $dom->createElement($tag);
            foreach ($attributes as $name => $value) {
                $node->setAttribute($name, $value);
            }
            return $node;
        };

        return [
            [
                $dom,
                $newNode('title', []),
                true,
            ],
            [
                $dom,
                $newNode(
                    'base',
                    ['href' => '/']
                ),
                true,
            ],
            [
                $dom,
                $newNode(
                    'script',
                    ['src' => 'http://example.com/test.js']
                ),
                true,
            ],
            [
                $dom,
                $newNode('style', ['media' => 'print']),
                true,
            ],
            [
                $dom,
                $newNode('noscript', []),
                true,
            ],
            [
                $dom,
                $newNode(
                    'link',
                    [
                        'rel'  => 'stylesheet',
                        'href' => 'https://example.com/foo.css',
                    ]
                ),
                true,
            ],
            [
                $dom,
                $newNode(
                    'meta',
                    [
                        'name'    => 'foo',
                        'content' => 'https://example.com/foo.css',
                    ]
                ),
                true,
            ],
            [
                $dom,
                $dom->createTextNode(" \n\t"),
                true,
            ],
            [
                $dom,
                $dom->createTextNode('no'),
                false,
            ],
            [
                $dom,
                $dom->createComment('hello world'),
                true,
            ],
            [
                $dom,
                $dom->createProcessingInstruction('test'),
                false,
            ],
            [
                $dom,
                $dom->createCDATASection('nope'),
                false,
            ],
            [
                $dom,
                $dom->createEntityReference('bad'),
                false,
            ],
            [
                $dom,
                $dom->createElementNS('http://www.w3.org/2000/svg', 'svg'),
                false,
            ],
            [
                $dom,
                $newNode('span', []),
                false,
            ],
        ];
    }

    /**
     * Test isValidHeadNode().
     *
     * @dataProvider getHeadNodeData
     * @covers       Document::isValidHeadNode()
     *
     * @param Document $dom   DOM document to use.
     * @param DOMNode  $node  Node.
     * @param bool     $valid Expected valid.
     */
    public function testIsValidHeadNode($dom, $node, $valid)
    {
        $this->assertEquals($valid, $dom->isValidHeadNode($node));
    }
}
