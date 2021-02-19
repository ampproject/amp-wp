<?php
/**
 * Tests for AMP_Meta_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\Dom\Document;

/**
 * Tests for AMP_Meta_Sanitizer.
 */
class Test_AMP_Meta_Sanitizer extends WP_UnitTestCase {

	use MarkupComparison;

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
		$this->assertEquals( [ 'disallowed_value_regex' ], array_keys( $spec['attr_spec_list']['name'] ) );
	}

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

		$meta_charset  = '<meta charset="utf-8">';
		$meta_viewport = '<meta name="viewport" content="width=device-width">';

		$meta_tags_allowed_in_body = '
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

		$data = [
			'Do not break the correct charset tag'        => [
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Do not break the correct viewport tag'       => [
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
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

			'Remove legacy meta http-equiv=Content-Type'  => [
				'<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $amp_boilerplate . '</head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Process invalid meta http-equiv value'       => [
				// Note the AMP_Tag_And_Attribute_Sanitizer removes the http-equiv attribute because the content is invalid.
				'<!DOCTYPE html><html><head>' . $amp_boilerplate . '</head><body><meta http-equiv="Content-Type" content="text/vbscript"></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta content="text/vbscript"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Disallowed meta=content-deposition'          => [
				'<!DOCTYPE html><html><head>' . $amp_boilerplate . '<meta name="content-disposition" content="inline; filename=data.csv"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><meta content="inline; filename=data.csv">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Disallowed meta=revisit-after'               => [
				'<!DOCTYPE html><html><head>' . $amp_boilerplate . '<meta name="revisit-after" content="7 days"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><meta content="7 days">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Disallowed meta=amp-bogus'                   => [
				'<!DOCTYPE html><html><head>' . $amp_boilerplate . '<meta name="amp-bogus" content="bad"></head><body></body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><meta content="bad">' . $amp_boilerplate . '</head><body></body></html>',
			],

			'Ignore generic meta tags'                    => [
				'<!DOCTYPE html><html><head><meta charset="utf-8">' . $amp_boilerplate . '</head><body>' . $meta_tags_allowed_in_body . '</body></html>',
				'<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width">' . $amp_boilerplate . '</head><body>' . $meta_tags_allowed_in_body . '</body></html>',
			],
		];

		$http_equiv_specs = [
			'meta http-equiv=X-UA-Compatible'        => '<meta http-equiv="X-UA-Compatible" content="IE=edge">',
			'meta http-equiv=content-language'       => '<meta http-equiv="content-language" content="labellist">',
			'meta http-equiv=pics-label'             => '<meta http-equiv="PICS-Label" content="en-US">',
			'meta http-equiv=imagetoolbar'           => '<meta http-equiv="imagetoolbar" content="false">',
			'meta http-equiv=Content-Style-Type'     => '<meta http-equiv="Content-Style-Type" content="text/css">',
			'meta http-equiv=Content-Script-Type'    => '<meta http-equiv="Content-Script-Type" content="text/javascript">',
			'meta http-equiv=origin-trial'           => '<meta http-equiv="origin-trial" content="...">',
			'meta http-equiv=resource-type'          => '<meta http-equiv="resource-type" content="document">',
			'meta http-equiv=x-dns-prefetch-control' => '<meta http-equiv="x-dns-prefetch-control" content="on">',
		];
		foreach ( $http_equiv_specs as $equiv_spec => $tag ) {
			$data[ "Verify http-equiv moved: $equiv_spec" ] = [
				"<!DOCTYPE html><html><head>{$meta_charset}{$meta_viewport}{$amp_boilerplate}</head><body>{$tag}</body></html>",
				"<!DOCTYPE html><html><head>{$meta_charset}{$tag}{$meta_viewport}{$amp_boilerplate}</head><body></body></html>",
			];
		}

		$named_specs = [
			'meta name=apple-itunes-app'                 => '<meta name="apple-itunes-app" content="app-id=myAppStoreID, affiliate-data=myAffiliateData, app-argument=myURL">',
			'meta name=amp-experiments-opt-in'           => '<meta name="amp-experiments-opt-in" content="experiment-a,experiment-b">',
			'meta name=amp-3p-iframe-src'                => '<meta name="amp-3p-iframe-src" content="https://storage.googleapis.com/amp-testing.appspot.com/public/remote.html">',
			'meta name=amp-consent-blocking'             => '<meta name="amp-consent-blocking" content="">',
			'meta name=amp-experiment-token'             => '<meta name="amp-experiment-token" content="{copy your token here}">',
			'meta name=amp-link-variable-allowed-origin' => '<meta name="amp-link-variable-allowed-origin" content="https://example.com https://example.org">',
			'meta name=amp-google-clientid-id-api'       => '<meta name="amp-google-client-id-api" content="googleanalytics">',
			'meta name=amp-ad-doubleclick-sra'           => '<meta name="amp-ad-doubleclick-sra">',
			'meta name=amp-list-load-more'               => '<meta name="amp-list-load-more" content="">',
			'meta name=amp-recaptcha-input'              => '<meta name="amp-recaptcha-input" content="">',
			'meta name=amp-ad-enable-refresh'            => '<meta name="amp-ad-enable-refresh" content="network1=refresh_interval1,network2=refresh_interval2,...">',
			'meta name=amp-to-amp-navigation'            => '<meta name="amp-to-amp-navigation" content="AMP-Redirect-To; AMP.navigateTo">',
		];
		foreach ( $named_specs as $named_spec => $tag ) {
			$data[ "Verify meta[name] moved: $named_spec" ] = [
				"<!DOCTYPE html><html><head>{$meta_charset}{$meta_viewport}{$amp_boilerplate}</head><body>{$tag}</body></html>",
				"<!DOCTYPE html><html><head>{$meta_charset}{$meta_viewport}{$tag}{$amp_boilerplate}</head><body></body></html>",
			];
		}

		return $data;
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

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer(
			$dom,
			[ 'use_document_element' => true ]
		);
		$sanitizer->sanitize();

		$this->assertEqualMarkup( $expected_content, $dom->saveHTML() );
	}

	/** @covers \AMP_Meta_Sanitizer::sanitize() */
	public function test_initial_scale_removal() {
		$html = '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head></html>';

		$dom       = Document::fromHtml( $html );
		$sanitizer = new AMP_Meta_Sanitizer( $dom, [] );
		$sanitizer->sanitize();
		$this->assertEquals( 'width=device-width', $dom->viewport->getAttribute( 'content' ) );

		$dom       = Document::fromHtml( $html );
		$sanitizer = new AMP_Meta_Sanitizer( $dom, [ 'remove_initial_scale_viewport_property' => true ] );
		$sanitizer->sanitize();
		$this->assertEquals( 'width=device-width', $dom->viewport->getAttribute( 'content' ) );

		$dom       = Document::fromHtml( $html );
		$sanitizer = new AMP_Meta_Sanitizer( $dom, [ 'remove_initial_scale_viewport_property' => false ] );
		$sanitizer->sanitize();
		$this->assertEquals( 'width=device-width,initial-scale=1', $dom->viewport->getAttribute( 'content' ) );

		$html      = '<html><head><meta name="viewport" content="width=device-width, initial-scale=2"></head></html>';
		$dom       = Document::fromHtml( $html );
		$sanitizer = new AMP_Meta_Sanitizer( $dom, [ 'remove_initial_scale_viewport_property' => true ] );
		$sanitizer->sanitize();
		$this->assertEquals( 'width=device-width,initial-scale=2', $dom->viewport->getAttribute( 'content' ) );
	}
}
