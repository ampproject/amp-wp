<?php

namespace AmpProject\Optimizer;

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\Layout;
use AmpProject\Tag;
use DOMElement;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AmpProject\Optimizer\ImageDimensions.
 *
 * @covers  ImageDimensions
 * @package ampproject/optimizer
 */
class ImageDimensionsTest extends TestCase
{

    /**
     * Test instantiating the ImageDimensions object.
     *
     * @covers \AmpProject\Optimizer\ImageDimensions::__construct()
     */
    public function testItCanBeInstantiated()
    {
        $image           = new DOMElement(Tag::IMG);
        $imageDimensions = new ImageDimensions($image);

        $this->assertInstanceOf(ImageDimensions::class, $imageDimensions);
    }

    /**
     * Provide an associative array of test data for checking and getting image dimensions.
     *
     * @return array[] Associative array of test data.
     */
    public function dataItCanGetDimensions()
    {
        return [
            'no dimensions'           => [null, null, false, false, null, null],
            'width only'              => [500, null, true, false, 500, null],
            'height only'             => [null, 500, false, true, null, 500],
            'width & height'          => [640, 480, true, true, 640, 480],
            'no dimensions (string)'  => ['', '', false, false, null, null],
            'width only (string)'     => ['500', '', true, false, 500, null],
            'height only (string)'    => ['', '500', false, true, null, 500],
            'width & height (string)' => ['640', '480', true, true, 640, 480],
            'width only (float)'      => [500.3, null, true, false, 500.3, null],
            'height only (float)'     => [null, 500.7, false, true, null, 500.7],
            'width & height (float)'  => [640.0, 480.0, true, true, 640.0, 480.0],
            'auto width'              => ['auto', null, true, false, 'auto', null],
            'auto height'             => [null, 'auto', false, true, null, 'auto'],
            'auto width & height'     => ['auto', 'auto', true, true, 'auto', 'auto'],
            'fluid width'             => ['fluid', null, true, false, 'fluid', null],
            'fluid height'            => [null, 'fluid', false, true, null, 'fluid'],
            'fluid width & height'    => ['fluid', 'fluid', true, true, 'fluid', 'fluid'],
        ];
    }

    /**
     * Test checking and getting the dimensions.
     *
     * @param int|string|null $width             Width of the image.
     * @param int|string|null $height            Height of the image.
     * @param bool            $expectedHasWidth  Expected presence check for width.
     * @param bool            $expectedHasHeight Expected presence check for height.
     * @param int|null        $expectedWidth     Expected value of width.
     * @param int|null        $expectedHeight    Expected value of height.
     *
     * @dataProvider dataItCanGetDimensions()
     *
     * @covers       \AmpProject\Optimizer\ImageDimensions::hasWidth()
     * @covers       \AmpProject\Optimizer\ImageDimensions::hasHeight()
     * @covers       \AmpProject\Optimizer\ImageDimensions::getWidth()
     * @covers       \AmpProject\Optimizer\ImageDimensions::getHeight()
     */
    public function testItCanGetDimensions(
        $width,
        $height,
        $expectedHasWidth,
        $expectedHasHeight,
        $expectedWidth,
        $expectedHeight
    ) {
        $dom   = new Document();
        $image = $dom->createElement(Tag::IMG);

        if ($width !== null) {
            $image->setAttribute(Attribute::WIDTH, $width);
        }

        if ($height !== null) {
            $image->setAttribute(Attribute::HEIGHT, $height);
        }

        $imageDimensions = new ImageDimensions($image);

        $this->assertEquals($expectedHasWidth, $imageDimensions->hasWidth());
        $this->assertEquals($expectedHasHeight, $imageDimensions->hasHeight());
        $this->assertEquals($expectedWidth, $imageDimensions->getWidth());
        $this->assertEquals($expectedHeight, $imageDimensions->getHeight());
    }

    /**
     * Provide an associative array of test data for checking and getting the image layout.
     *
     * @return array[] Associative array of test data.
     */
    public function dataItCanGetTheLayout()
    {
        return [
            'no layout'          => [null, false, ''],
            'fixed layout'       => [Layout::FIXED, true, Layout::FIXED],
            'no layout (string)' => ['', false, ''],
        ];
    }

    /**
     * Test checking and getting the layout.
     *
     * @param string|null $layout            Layout of the image.
     * @param bool        $expectedHasLayout Expected presence check for layout.
     * @param int|null    $expectedLayout    Expected value of layout.
     *
     * @dataProvider dataItCanGetTheLayout()
     *
     * @covers       \AmpProject\Optimizer\ImageDimensions::hasLayout()
     * @covers       \AmpProject\Optimizer\ImageDimensions::getLayout()
     */
    public function testItCanGetTheLayout($layout, $expectedHasLayout, $expectedLayout)
    {
        $dom   = new Document();
        $image = $dom->createElement(Tag::IMG);

        if ($layout !== null) {
            $image->setAttribute(Attribute::LAYOUT, $layout);
        }

        $imageDimensions = new ImageDimensions($image);

        $this->assertEquals($expectedHasLayout, $imageDimensions->hasLayout());
        $this->assertEquals($expectedLayout, $imageDimensions->getLayout());
    }

    /**
     * Test whether the dimensions from a parent can be returned.
     *
     * @covers \AmpProject\Optimizer\ImageDimensions::getDimensionsFromParent()
     */
    public function testItCanGetDimensionsFromParent()
    {
        $dom   = new Document();
        $image = $dom->createElement(Tag::IMG);

        $parent = $dom->createElement(Tag::FIGURE);
        $parent->setAttribute(Attribute::WIDTH, 400);
        $parent->setAttribute(Attribute::HEIGHT, 200);

        $parent->appendChild($image);

        $imageDimensions = new ImageDimensions($image);

        $this->assertEquals([400, 200], $imageDimensions->getDimensionsFromParent());
    }

    /**
     * Test whether the dimensions from a parent's parent can be returned.
     *
     * @covers \AmpProject\Optimizer\ImageDimensions::getDimensionsFromParent()
     */
    public function testItCanGetDimensionsFromParentsParent()
    {
        $dom   = new Document();
        $image = $dom->createElement(Tag::IMG);

        $parentA = $dom->createElement(Tag::DIV);

        $parentB = $dom->createElement(Tag::DIV);
        $parentB->setAttribute(Attribute::WIDTH, 400);
        $parentB->setAttribute(Attribute::HEIGHT, 200);

        $parentB->appendChild($parentA)
                ->appendChild($image);

        $imageDimensions = new ImageDimensions($image);

        $this->assertEquals([400, 200], $imageDimensions->getDimensionsFromParent());
    }

    /**
     * Test whether iterating over parents for dimensions stop at a certain depth.
     *
     * @covers \AmpProject\Optimizer\ImageDimensions::getDimensionsFromParent()
     */
    public function testItStopsReturningParentDimensionsAtDepth3()
    {
        $dom   = new Document();
        $image = $dom->createElement(Tag::IMG);

        $parentA = $dom->createElement(Tag::DIV);

        $parentB = $dom->createElement(Tag::DIV);

        $parentC = $dom->createElement(Tag::DIV);
        $parentC->setAttribute(Attribute::WIDTH, 400);
        $parentC->setAttribute(Attribute::HEIGHT, 200);

        $parentC->appendChild($parentB)
                ->appendChild($parentA)
                ->appendChild($image);

        $imageDimensions = new ImageDimensions($image);

        $this->assertEquals([-1, -1], $imageDimensions->getDimensionsFromParent());
    }

    /**
     * Provide test data for checking if an image is tiny.
     *
     * @return array[] Associative array of test data.
     */
    public function dataItCanCheckIfAnImageIsTiny()
    {
        return [
            'large with no layout'                    => [500, 500, null, null, false],
            'small width with no layout'              => [50, 500, null, null, true],
            'small height with no layout'             => [500, 50, null, null, true],
            'small with no layout'                    => [50, 50, null, null, true],
            'zero width with no layout'               => [0, 500, null, null, true],
            'zero height with no layout'              => [500, 0, null, null, true],
            'zero width & height with no layout'      => [0, 0, null, null, true],
            'large intrinsic'                         => [500, 500, Layout::INTRINSIC, null, false],
            'small intrinsic'                         => [50, 50, Layout::INTRINSIC, null, false],
            'large responsive'                        => [500, 500, Layout::RESPONSIVE, null, false],
            'small responsive'                        => [50, 50, Layout::RESPONSIVE, null, false],
            'large fixed height'                      => ['auto', 500, Layout::FIXED_HEIGHT, null, true],
            'small fixed height'                      => ['auto', 50, Layout::FIXED_HEIGHT, null, true],
            'no dimensions no layout'                 => [null, null, null, null, true],
            'no dimensions fill (checks parent size)' => [null, null, Layout::FILL, null, false],
        ];
    }

    /**
     * Test if an image is tiny.
     *
     * @param int|null    $width     Width of the image. Null if not defined.
     * @param int|null    $height    Height of the image. Null if not defined.
     * @param string|null $layout    Layout of the image. Null if not defined.
     * @param int|null    $threshold Threshold in pixels. Null to fall back to default.
     * @param bool        $expected  Expected check result.
     *
     * @dataProvider dataItCanCheckIfAnImageIsTiny()
     *
     * @covers       \AmpProject\Optimizer\ImageDimensions::IsTiny()
     */
    public function testItCanCheckIfAnImageIsTiny($width, $height, $layout, $threshold, $expected)
    {
        $dom   = new Document();
        $image = $dom->createElement(Tag::IMG);

        if ($width !== null) {
            $image->setAttribute(Attribute::WIDTH, $width);
        }

        if ($height !== null) {
            $image->setAttribute(Attribute::HEIGHT, $height);
        }

        if ($layout !== null) {
            $image->setAttribute(Attribute::LAYOUT, $layout);
        }

        $parent = $dom->createElement(Tag::FIGURE);
        $parent->setAttribute(Attribute::WIDTH, 400);
        $parent->setAttribute(Attribute::HEIGHT, 200);

        $parent->appendChild($image);

        $imageDimensions = new ImageDimensions($image);

        $this->assertEquals($expected, $imageDimensions->isTiny($threshold));
    }
}
