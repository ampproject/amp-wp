<?php
/**
 * Tests for AMP_Meta_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Tests for AMP_Meta_Sanitizer.
 */
class Test_AMP_Meta_Sanitizer extends WP_UnitTestCase {

	/**
	 * Provide data to the test_sanitize method.
	 *
	 * @return array[] Array of arrays with test data.
	 */
	public function get_data_for_sanitize() {
		$script1 = 'document.body.textContent += "First!";';
		$script2 = 'document.body.textContent += "Second!";';
		$script3 = 'document.body.textContent += "Third!";';
		$script4 = 'document.body.textContent += "Fourth! (And forbidden because no amp-script-src meta in head.)";';

		$script1_hash = amp_generate_script_hash( $script1 );
		$script2_hash = amp_generate_script_hash( $script2 );
		$script3_hash = amp_generate_script_hash( $script3 );
		$script4_hash = amp_generate_script_hash( $script4 );

		$amp_boilerplate = amp_get_boilerplate_code();

		$html5_microdata = '
			<span itemprop="author" itemscope itemtype="https://schema.org/Person">
				<meta itemprop="name" content="Siva">
			</span>
			<meta itemprop="datePublished" content="2020-03-24T18:05:15+05:30">
			<meta itemprop="dateModified" content="2020-03-24T18:05:15+05:30">
			<meta itemscope itemprop="mainEntityOfPage" itemtype="https://schema.org/WebPage" itemid="https://example.com/">
			<span itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
				<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
					<meta itemprop="url" content="https://example/logo.png">
				</span>
				<meta itemprop="name" content="Example">
				<meta itemprop="url" content="https://example.com">
			</span>
			<meta itemprop="headline " content="This is a test">
			<span itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
				<meta itemprop="url" content="https://example.com/foo.jpg">
				<meta itemprop="width" content="1280"><meta itemprop="height" content="720">
			</span>
			<div itemscope id="amanda" itemref="a b"></div>
			<p id="a">Name: <span itemprop="name">Amanda</span> </p>
				<div id="b" itemprop="band" itemscope itemref="c"></div>
			<div id="c">
				<p>Band: <span itemprop="name">Jazz Band</span> </p>
				<p>Size: <span itemprop="size">12</span> players</p>
			</div>
			<meta id="foo">
			<meta name="greeting" content="Hello!">
			<meta name="keywords" content="Meta Tags, Metadata" scheme="ISBN">
			<meta content="This is a basic text" property="og:title">
		';

		return [
			'Do not break the correct charset tag'        => [
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Do not break the correct viewport tag'       => [
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Move charset and viewport tags from body to head' => [
				'<!DOCTYPE html><html><head>' . $amp_boilerplate . '</head><body><meta charset="utf-8"><meta name="viewport" content="width=device-width"></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Add default charset tag if none is present'  => [
				'<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Add default viewport tag if none is present' => [
				'<!DOCTYPE html><html><head><meta charset="utf-8">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Make sure charset is the first meta tag'     => [
				'<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width"><meta charset="utf-8">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Concatenate and reposition script hashes'    => [
				'<!DOCTYPE html><html><head><meta name="amp-script-src" content="' . esc_attr( $script1_hash ) . '"><meta charset="utf-8"><meta name="amp-script-src" content="' . esc_attr( $script2_hash ) . '"><meta name="viewport" content="width=device-width"><meta name="amp-script-src" content="' . esc_attr( $script3_hash ) . '">' . $amp_boilerplate . '</head><body><meta name="amp-script-src" content="' . esc_attr( $script4_hash ) . '"></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><meta name="amp-script-src" content="' . esc_attr( $script1_hash ) . ' ' . esc_attr( $script2_hash ) . ' ' . esc_attr( $script3_hash ) . ' ' . esc_attr( $script4_hash ) . '">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Make sure http-equiv meta tags are moved'    => [
				'<!DOCTYPE html><html><head><meta charset="utf-8">' . $amp_boilerplate . '</head><body><meta http-equiv="imagetoolbar" content="false"></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta http-equiv="imagetoolbar" content="false"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Ignore generic meta tags'                    => [
				'<!DOCTYPE html><html><head>' . $amp_boilerplate . '</head><body>' . $html5_microdata . '</body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body>' . $html5_microdata . '</body></html>',
			],
		];
	}

	/**
	 * Test that the expected tag specs exist for the body.
	 */
	public function test_expected_meta_tags() {
		$named_specs = array_filter(
			AMP_Allowed_Tags_Generated::get_allowed_tag( 'meta' ),
			static function ( $spec ) {
				return isset( $spec['tag_spec']['spec_name'] ) && AMP_Meta_Sanitizer::BODY_ANCESTOR_META_TAG_SPEC_NAME === $spec['tag_spec']['spec_name'];
			}
		);
		$this->assertCount( 1, $named_specs );

		$body_ok_specs = array_filter(
			AMP_Allowed_Tags_Generated::get_allowed_tag( 'meta' ),
			static function ( $spec ) {
				$head_required = (
					( isset( $spec['tag_spec']['mandatory_parent'] ) && 'head' === $spec['tag_spec']['mandatory_parent'] )
					||
					( isset( $spec['tag_spec']['mandatory_ancestor'] ) && 'head' === $spec['tag_spec']['mandatory_ancestor'] )
				);
				return ! $head_required;
			}
		);

		$this->assertEquals( $named_specs, $body_ok_specs );

		$spec = current( $named_specs );
		$this->assertArrayHasKey( 'name', $spec['attr_spec_list'] );
		$this->assertEquals( [ 'blacklisted_value_regex' ], array_keys( $spec['attr_spec_list']['name'] ) );
	}

	/**
	 * Tests the sanitize method.
	 *
	 * @dataProvider get_data_for_sanitize
	 * @covers \AMP_Meta_Sanitizer::sanitize()
	 *
	 * @param string  $source_content   Source DOM content.
	 * @param string  $expected_content Expected content after sanitization.
	 */
	public function test_sanitize( $source_content, $expected_content ) {
		$dom       = Document::fromHtml( $source_content );
		$sanitizer = new AMP_Meta_Sanitizer( $dom );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();

		$this->assertEqualMarkup( $expected_content, $dom->saveHTML() );
	}

	/**
	 * Assert markup is equal.
	 *
	 * @param string $expected Expected markup.
	 * @param string $actual   Actual markup.
	 */
	public function assertEqualMarkup( $expected, $actual ) {
		$actual   = preg_replace( '/\s+/', ' ', $actual );
		$expected = preg_replace( '/\s+/', ' ', $expected );
		$actual   = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $actual ) );
		$expected = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $expected ) );

		$this->assertEquals(
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE ) ),
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE ) )
		);
	}
}
