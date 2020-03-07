<?php

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Tests\AssertContainsCompatibility;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AmpProject\Dom\Document.
 *
 * @covers Document
 * @package ampproject/common
 */
class DocumentTest extends TestCase
{

    use AssertContainsCompatibility;

    /**
     * Data for AmpProject\Dom\Document test.
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
            'emoji_amp_attribute'                      => [
                'utf-8',
                '<!DOCTYPE html><html ' . Attribute::AMP_EMOJI . '>' . $head . '<body></body></html>',
                '<!DOCTYPE html><html ' . Attribute::AMP_EMOJI . '>' . $head . '<body></body></html>',
            ],
            // The next one is different, see https://github.com/ampproject/amphtml/issues/25990.
            'alt_emoji_amp_attribute'                  => [
                'utf-8',
                '<!DOCTYPE html><html ' . Attribute::AMP_EMOJI_ALT . '>' . $head . '<body></body></html>',
                '<!DOCTYPE html><html ' . Attribute::AMP_EMOJI_ALT . '>' . $head . '<body></body></html>',
            ],
            'emoji_amp4ads_attribute'                      => [
                'utf-8',
                '<!DOCTYPE html><html ' . Attribute::AMP4ADS_EMOJI . '>' . $head . '<body></body></html>',
                '<!DOCTYPE html><html ' . Attribute::AMP4ADS_EMOJI . '>' . $head . '<body></body></html>',
            ],
            // The next one is different, see https://github.com/ampproject/amphtml/issues/25990.
            'alt_emoji_amp4ads_attribute'                  => [
                'utf-8',
                '<!DOCTYPE html><html ' . Attribute::AMP4ADS_EMOJI_ALT . '>' . $head . '<body></body></html>',
                '<!DOCTYPE html><html ' . Attribute::AMP4ADS_EMOJI_ALT . '>' . $head . '<body></body></html>',
            ],
            'emoji_amp4email_attribute'                      => [
                'utf-8',
                '<!DOCTYPE html><html ' . Attribute::AMP4EMAIL_EMOJI . '>' . $head . '<body></body></html>',
                '<!DOCTYPE html><html ' . Attribute::AMP4EMAIL_EMOJI . '>' . $head . '<body></body></html>',
            ],
            // The next one is different, see https://github.com/ampproject/amphtml/issues/25990.
            'alt_emoji_amp4email_attribute'                  => [
                'utf-8',
                '<!DOCTYPE html><html ' . Attribute::AMP4EMAIL_EMOJI_ALT . '>' . $head . '<body></body></html>',
                '<!DOCTYPE html><html ' . Attribute::AMP4EMAIL_EMOJI_ALT . '>' . $head . '<body></body></html>',
            ],
            'html_attributes'                          => [
                'utf-8',
                '<!DOCTYPE html><html lang="en-US" class="no-js">' . $head . '<body></body></html>',
                '<!DOCTYPE html><html lang="en-US" class="no-js">' . $head . '<body></body></html>',
            ],
            'head_attributes'                          => [
                'utf-8',
                '<!DOCTYPE html><html><head itemscope itemtype="http://schema.org/WebSite"></head><body></body></html>',
                '<!DOCTYPE html><html><head itemscope itemtype="http://schema.org/WebSite"><meta charset="utf-8"></head><body></body></html>',
            ],
            'missing_head'                             => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en"><body class="some-class"><p>Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="some-class"><p>Text</p></body></html>',
            ],
            'multiple_heads'                           => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en"><head itemscope itemtype="http://schema.org/WebSite"><meta name="first" content="something"></head><head data-something="else"><meta name="second" content="something-else"></head><body class="some-class"><p>Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en"><head itemscope itemtype="http://schema.org/WebSite" data-something="else"><meta charset="utf-8"><meta name="first" content="something"><meta name="second" content="something-else"></head><body class="some-class"><p>Text</p></body></html>',
            ],
            'missing_body'                             => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<p>Text</p></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body><p>Text</p></body></html>',
            ],
            'multiple_bodies'                          => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="no-js"><p>Text</p></body><body data-some-attribute="to keep"><p>Yet another Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en">' . $head . '<body class="no-js" data-some-attribute="to keep"><p>Text</p><p>Yet another Text</p></body></html>',
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
            'html_with_xmlns_and_xml_lang'             => [
                'utf-8',
                '<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es">' . $head . '<body></body></html>',
                '<!DOCTYPE html><html lang="es">' . $head . '<body></body></html>',
            ],
            'html_with_xmlns_value_that_should_remain' => [
                'utf-8',
                '<!DOCTYPE html><html xmlns="http://www.w3.org/TR/html4/">' . $head . '<body></body></html>',
                '<!DOCTYPE html><html xmlns="http://www.w3.org/TR/html4/">' . $head . '<body></body></html>',
            ],
            'html_with_lang_and_xml_lang'              => [
                'utf-8',
                '<!DOCTYPE html><html lang="es" xml:lang="fr">' . $head . '<body></body></html>',
                '<!DOCTYPE html><html lang="es">' . $head . '<body></body></html>',
            ],
            'html_with_empty_xml_lang'                 => [
                'utf-8',
                '<!DOCTYPE html><html xml:lang="">' . $head . '<body></body></html>',
                '<!DOCTYPE html><html>' . $head . '<body></body></html>',
            ],
            'html_with_empty_lang'                     => [
                'utf-8',
                '<!DOCTYPE html><html lang="" xml:lang="es">' . $head . '<body></body></html>',
                '<!DOCTYPE html><html lang="es">' . $head . '<body></body></html>',
            ],
            'slashes_on_closing_tags'                  => [
                'utf-8',
                '<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8" /></head><body class="some-class"><p>Text</p></body></html>',
                '<!DOCTYPE html><html amp lang="en"><head><meta charset="utf-8"></head><body class="some-class"><p>Text</p></body></html>',
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
            'comments_around_main_elements'            => [
                'utf-8',
                ' <!-- comment 1 --> <!doctype html> <!-- comment 2 --> <html> <!-- comment 3 --> <head></head> <!-- comment 4 --> <body></body> <!-- comment 5 --></html>',
                ' <!-- comment 1 --> <!doctype html> <!-- comment 2 --> <html> <!-- comment 3 --> ' . $head . ' <!-- comment 4 --> <body></body> <!-- comment 5 --></html>',
            ],
            'ie_conditional_comments'                  => [
                'utf-8',
                '<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]--><!--[if IE 7]> <html class="lt-ie9 lt-ie8"> <![endif]--><!--[if IE 8]> <html class="lt-ie9"> <![endif]--><!--[if gt IE 8]><!--> <html class=""> <!--<![endif]--></html>',
                '<!DOCTYPE html><html class="">' . $head . '<body></body></html>',
            ],
			'comments_preserve_position'               => [
				'utf-8',
				"<!DOCTYPE html> \n <!-- before \n <html> --> \n <html> \n <!-- before \n <head> ----> \n <head><meta charset=\"utf-8\"> \n <!-- within \n <head> ----> \n </head> \n <!-- before \n <body> ----> \n <body class=\"something\" data-something=\"something\"> \n <!-- within \n <body> ----> \n </body> \n <!-- after \n </body> ----> \n </html> \n <!-- after \n </html> --> \n",
				"<!DOCTYPE html> \n <!-- before \n <html> --> \n <html> \n <!-- before \n <head> ----> \n <head><meta charset=\"utf-8\"> \n <!-- within \n <head> ----> \n </head> \n <!-- before \n <body> ----> \n <body class=\"something\" data-something=\"something\"> \n <!-- within \n <body> ----> \n </body> \n <!-- after \n </body> ----> \n </html> \n <!-- after \n </html> --> \n",
			],
			'profile_attribute_in_head_moved_to_link'  => [
				'utf-8',
				'<!DOCTYPE html><html><head profile="https://example.com"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><link rel="profile" href="https://example.com"></head><body></body></html>',
			],
			'profile_attribute_in_head_empty_string'   => [
				'utf-8',
				'<!DOCTYPE html><html><head profile=""></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"></head><body></body></html>',
			],
        ];
    }

    /**
     * Tests loading and saving the content via AmpProject\Dom\Document.
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
            array_filter(preg_split('#(<!--.*?-->|<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE)),
            array_filter(preg_split('#(<!--.*?-->|<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE))
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
            $this->assertStringNotContains(Document::AMP_BIND_DATA_ATTR_PREFIX, $converted, "Source: {$html}");
        }
    }

    /**
     * Get Table Row Iterations.
     *
     * @return array An array of arrays holding an integer representation of iterations.
     */
    public function getTableRowIterations()
    {
        return [[1], [10], [100], [1000], [10000], [100000]];
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
            'title'                  => [$dom, $newNode('title', []), true],
            'base'                   => [$dom, $newNode('base', ['href' => '/']), true],
            'script'                 => [$dom, $newNode('script', ['src' => 'http://example.com/test.js']), true],
            'style'                  => [$dom, $newNode('style', ['media' => 'print']), true],
            'noscript'               => [$dom, $newNode('noscript', []), true],
            'link'                   => [
                $dom,
                $newNode('link', ['rel' => 'stylesheet', 'href' => 'https://example.com/foo.css']),
                true,
            ],
            'meta'                   => [
                $dom,
                $newNode('meta', ['name' => 'foo', 'content' => 'https://example.com/foo.css']),
                true,
            ],
            'empty textnode'         => [$dom, $dom->createTextNode(" \n\t"), true],
            'non-empty texnode'      => [$dom, $dom->createTextNode('no'), false],
            'comment'                => [$dom, $dom->createComment('hello world'), true],
            'processing instruction' => [$dom, $dom->createProcessingInstruction('test'), false],
            'cdata'                  => [$dom, $dom->createCDATASection('nope'), false],
            'entity reference'       => [$dom, $dom->createEntityReference('bad'), false],
            'svg'                    => [$dom, $dom->createElementNS('http://www.w3.org/2000/svg', 'svg'), false],
            'span'                   => [$dom, $newNode('span', []), false],
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

    /**
     * Test that the $html property fetches the right element.
     *
     * @covers Document::__get()
     * @covers Document::$html
     */
    public function testHtmlProperty()
    {
        $html     = '<!doctype html><html data-test="correct-element"><head></head><body></body></html>';
        $document = Document::fromHtml($html);
        $this->assertEquals('correct-element', $document->html->getAttribute('data-test'));
    }

    /**
     * Test that the $head property fetches the right element.
     *
     * @covers Document::__get()
     * @covers Document::$head
     */
    public function testHeadProperty()
    {
        $html     = '<!doctype html><html><head data-test="correct-element"></head><body></body></html>';
        $document = Document::fromHtml($html);
        $this->assertEquals('correct-element', $document->head->getAttribute('data-test'));
    }

    /**
     * Test that the $body property fetches the right element.
     *
     * @covers Document::__get()
     * @covers Document::$body
     */
    public function testBodyProperty()
    {
        $html     = '<!doctype html><html><head></head><body data-test="correct-element"></body></html>';
        $document = Document::fromHtml($html);
        $this->assertEquals('correct-element', $document->body->getAttribute('data-test'));
    }

    /**
     * Test that the $ampElements property fetches the right elements.
     *
     * @covers Document::__get()
     * @covers Document::$ampElements
     */
    public function testAmpElementsProperty()
    {
        $html     = '<!doctype html><html>'
                    . '  <head><style amp-custom data-test="wrong-element"></style></head>'
                    . '  <body>'
                    . '    <amp-text data-test="correct-element"></amp-text><amp-img data-test="correct-element"></amp-img>'
                    . '    <template type="amp-mustache" data-test="wrong-element"><amp-img data-test="correct-element" alt="{{foo}}"></amp-img></template>'
                    . '  </body>'
                    . '</html>';
        $document = Document::fromHtml($html);
        $this->assertEquals(3, $document->ampElements->length);
        foreach ($document->ampElements as $element) {
            $this->assertEquals('correct-element', $element->getAttribute('data-test'));
        }
    }
}
