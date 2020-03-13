<?php

namespace AmpProject\Common;

use AmpProject\Fonts;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AmpProject\Fonts.
 *
 * @covers Amp
 * @package ampproject/common
 */
class FontsTest extends TestCase
{

    /**
     * Test the check for an AMP runtime method.
     *
     * @covers       Fonts::getEmojiFontFamilyValue()
     */
    public function testGetEmojiFontFamilyValue()
    {
        $value = Fonts::getEmojiFontFamilyValue();
        $this->assertContains('Apple Color Emoji', $value);
        $this->assertContains(',', $value);
    }
}
