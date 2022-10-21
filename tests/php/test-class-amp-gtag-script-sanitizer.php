<?php
/**
 * Class AMP_GTag_Script_Sanitizer_Test.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Dom\Document;
use AmpProject\Html\Tag;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests the GTag Script Sanitizer class.
 *
 * @coversDefaultClass AMP_GTag_Script_Sanitizer
 */
class AMP_GTag_Script_Sanitizer_Test extends TestCase {

	/**
	 * Sandboxing enabled.
	 *
	 * @var bool
	 */
	private $sandboxing_enabled;

	/**
	 * Sandboxing level.
	 *
	 * @var int
	 */
	private $sandboxing_level;

	/**
	 * XPath query to find the scripts element.
	 *
	 * @var string
	 */
	const SCRIPT_XPATH = '//script[ contains( @src, "https://www.googletagmanager.com/gtag/js" ) or contains( text(), "gtag(" ) ]';

	/**
	 * HTML markup with gtag script.
	 *
	 * @var string
	 */
	const GTAG_HTML_MARKUP = '<html><head></head><body><script async src="https://www.googletagmanager.com/gtag/js?id=xxxxxx"></script><script>function gtag(){dataLayer.push(arguments)}window.dataLayer=window.dataLayer||[],gtag("js",new Date),gtag("config","xxxxxx")</script></body></html>';

	/**
	 * Enable sandboxing for the test.
	 *
	 * @param bool $enabled Whether sandboxing is enabled.
	 * @param int $sandboxing_level The sandboxing level to set.
	 */
	public function enable_sandboxing( $enable = true, $level = 3 ) {
		AMP_Options_Manager::update_option( Option::SANDBOXING_ENABLED, $enable );
		if ( $enable ) {
			AMP_Options_Manager::update_option( Option::SANDBOXING_LEVEL, $level );
		}
	}

	/**
	 * Do not PX verify script tag if no sandboxing.
	 *
	 * @covers ::sanitize
	 */
	public function test_do_not_px_verify_script_tag_if_no_sandboxing() {
		$dom = new Document();
		$dom->loadHTML( self::GTAG_HTML_MARKUP );

		$this->enable_sandboxing( false );

		$sanitizer = new AMP_GTag_Script_Sanitizer( $dom );
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( self::SCRIPT_XPATH );

		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}

	/**
	 * Do not PX verify script tag if sandboxing level is strict.
	 *
	 * @covers ::sanitize
	 */
	public function test_do_not_px_verify_script_tag_if_sandboxing_level_is_strict() {
		$dom = new Document();
		$dom->loadHTML( self::GTAG_HTML_MARKUP );

		$this->enable_sandboxing( true );

		$sanitizer = new AMP_GTag_Script_Sanitizer( $dom );
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( self::SCRIPT_XPATH );

		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}

	/**
	 * PX verify script tag if sandboxing level is Moderate.
	 *
	 * @covers ::sanitize
	 */
	public function test_px_verify_script_tag_if_sandboxing_level_is_moderate() {
		$dom = new Document();
		$dom->loadHTML( self::GTAG_HTML_MARKUP );

		$this->enable_sandboxing( true, 2 );

		$sanitizer = new AMP_GTag_Script_Sanitizer( $dom );
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( self::SCRIPT_XPATH );

		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}

	/**
	 * PX verify script tag if sandboxing level is Loose.
	 *
	 * @covers ::sanitize
	 */
	public function test_px_verify_script_tag_if_sandboxing_level_is_loose() {
		$dom = new Document();
		$dom->loadHTML( self::GTAG_HTML_MARKUP );

		$this->enable_sandboxing( true, 1 );

		$sanitizer = new AMP_GTag_Script_Sanitizer( $dom );
		$sanitizer->sanitize();
		$scripts = $dom->xpath->query( self::SCRIPT_XPATH );

		$this->assertCount( 2, $scripts );
		foreach ( $scripts as $script ) {
			$this->assertSame( Tag::SCRIPT, $script->tagName );
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $script ) );
		}
	}
}
