<?php
/**
 * Class AMP_GTag_Script_Sanitizer_Test.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Dom\Document;
use AmpProject\Html\Tag;
use AmpProject\Dom\Element;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests the GTag Script Sanitizer class.
 *
 * @coversDefaultClass AMP_GTag_Script_Sanitizer
 */
class AMP_GTag_Script_Sanitizer_Test extends TestCase {

	/**
	 * XPath query to find the scripts element.
	 *
	 * @var string
	 */
	const SCRIPT_XPATH = '//script[ ( @async and starts-with( @src, "https://www.googletagmanager.com/gtag/js" ) ) or contains( text(), "function gtag(" ) ]';

	/**
	 * XPath query to find inline gtag events.
	 *
	 * @var string
	 */
	const INLINE_GTAG_EVENT_XPATH = '//@*[ starts-with(name(), "on") and name() != "on" and contains(., "gtag(") ]';

	/**
	 * HTML markup with gtag script.
	 *
	 * @var string
	 */
	const GTAG_HTML_MARKUP = '<html><head></head><body><script async src="https://www.googletagmanager.com/gtag/js?id=xxxxxx"></script><script>function gtag(){dataLayer.push(arguments)}window.dataLayer=window.dataLayer||[],gtag("js",new Date),gtag("config","xxxxxx")</script><div><a href="/">Home</a><a href="/contact" onclick=\'gtag("event","click",{event_category:"click",event_label:"contactPage"})\'>Contact</a><form><input type="text" name="name" placeholder="Name"><button onsubmit=\'gtag("event","click",{event_category:"click",event_label:"contactPage"})\' onclick="submit()"></button><button type="reset" onclick="clear()">Reset</button></form></div><script src="https://example.com/pixel/js"></script><script>document.write("Hello world")</script></body></html>';

	/**
	 * Get data
	 *
	 * @return array Test data.
	 */
	public function get_data() {
		return [
			'do_not_px_verify_script_tag_if_no_sandboxing' => [
				'enable_sandboxing'  => false,
				'sandboxing_level'   => null,
				'expect_px_verified' => false,
			],
			'do_not_px_verify_script_tag_if_sandboxing_level_is_strict' => [
				'enable_sandboxing'  => true,
				'sandboxing_level'   => 3,
				'expect_px_verified' => false,
			],
			'px_verify_script_tag_if_sandboxing_level_is_moderate' => [
				'enable_sandboxing'  => true,
				'sandboxing_level'   => 2,
				'expect_px_verified' => true,
			],
			'px_verify_script_tag_if_sandboxing_level_is_loose' => [
				'enable_sandboxing'  => true,
				'sandboxing_level'   => 1,
				'expect_px_verified' => true,
			],
		];
	}

	/**
	 * @dataProvider get_data
	 *
	 * @param bool $enable_sandboxing Whether sandboxing is enabled.
	 * @param int $sandboxing_level The sandboxing level to set.
	 * @param bool $expect_px_verified Whether the script tag is expected to be px verified.
	 *
	 * @covers ::sanitize
	 */
	public function test_sanitize( $enable_sandboxing, $sandboxing_level, $expect_px_verified ) {
		AMP_Options_Manager::update_option( Option::SANDBOXING_ENABLED, $enable_sandboxing );
		if ( $enable_sandboxing ) {
			AMP_Options_Manager::update_option( Option::SANDBOXING_LEVEL, $sandboxing_level );
		}

		$dom = new Document();
		$dom->loadHTML( self::GTAG_HTML_MARKUP );

		$sanitizer = new AMP_GTag_Script_Sanitizer( $dom );
		$sanitizer->sanitize();
		$gtag_scripts  = $dom->xpath->query( self::SCRIPT_XPATH );
		$other_scripts = $dom->xpath->query( '//script[ not( @async and starts-with( @src, "https://www.googletagmanager.com/gtag/js" ) ) and not( contains( text(), "function gtag(" ) ) ]' );

		// Gtag scripts.
		$this->assertCount( 2, $gtag_scripts );
		$this->assertInstanceof( DOMNodeList::class, $gtag_scripts );
		foreach ( $gtag_scripts as $script ) {
			$this->assertInstanceof( Element::class, $script );
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertEquals( $expect_px_verified, ValidationExemption::is_px_verified_for_node( $script ) );
		}

		$this->assertCount( 2, $other_scripts );
		$this->assertInstanceof( DOMNodeList::class, $other_scripts );
		foreach ( $other_scripts as $script ) {
			$this->assertInstanceof( Element::class, $script );
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );
		}

		// Inline Gtag events.
		$inline_gtag_events  = $dom->xpath->query( self::INLINE_GTAG_EVENT_XPATH );
		$other_inline_events = $dom->xpath->query( '//@*[ starts-with(name(), "on") and name() != "on" and not( contains(., "gtag(") ) ]' );

		$this->assertCount( 2, $inline_gtag_events );
		$this->assertInstanceof( DOMNodeList::class, $inline_gtag_events );
		foreach ( $inline_gtag_events as $inline_gtag_event ) {
			$this->assertInstanceof( DOMAttr::class, $inline_gtag_event );
			$this->assertStringStartsWith( 'on', $inline_gtag_event->nodeName );
			$this->assertEquals( $expect_px_verified, ValidationExemption::is_px_verified_for_node( $inline_gtag_event ) );
		}

		$this->assertCount( 2, $other_inline_events );
		$this->assertInstanceof( DOMNodeList::class, $other_inline_events );
		foreach ( $other_inline_events as $other_inline_event ) {
			$this->assertInstanceof( DOMAttr::class, $other_inline_event );
			$this->assertStringStartsWith( 'on', $inline_gtag_event->nodeName );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $other_inline_event ) );
		}
	}
}
