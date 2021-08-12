<?php
/**
 * Test Site_Health.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\Dom\Options;
use AmpProject\AmpWP\Optimizer\Transformer\AmpSchemaOrgMetadata;
use AmpProject\AmpWP\Optimizer\Transformer\AmpSchemaOrgMetadataConfiguration;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Test Site_Health.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Optimizer\Transformer\AmpSchemaOrgMetadata
 */
class AmpSchemaOrgMetadataTest extends TestCase {

	/**
	 * Data provider for test_transform.
	 *
	 * @return array
	 */
	public function get_schema_script_data() {
		return [
			'schema_org_not_present'        => [
				'',
				1,
			],
			'schema_org_present'            => [
				wp_json_encode( [ '@context' => 'http://schema.org' ] ),
				1,
			],
			'schema_org_output_not_escaped' => [
				'{"@context":"http://schema.org"',
				1,
			],
			'schema_org_another_key'        => [
				wp_json_encode( [ '@anothercontext' => 'https://schema.org' ] ),
				1,
			],
		];
	}

	/**
	 * Test the transform method.
	 *
	 * @dataProvider get_schema_script_data
	 *
	 * @covers ::transform
	 *
	 * @param string $json     JSON data.
	 * @param int    $expected Expected count of valid JSON+LD schema.
	 */
	public function test_transform( $json, $expected ) {
		$html          = '<html><head><script type="application/ld+json">%s</script></head><body>Test</body></html>';
		$dom           = Document::fromHtml( sprintf( $html, $json ), Options::DEFAULTS );
		$configuration = new AmpSchemaOrgMetadataConfiguration(
			[
				AmpSchemaOrgMetadataConfiguration::METADATA => [
					'@context'  => 'http://schema.org',
					'publisher' => [
						'@type' => 'Organization',
						'name'  => 'Acme',
					],
				],
			]
		);
		$transformer   = new AmpSchemaOrgMetadata( $configuration );
		$errors        = new ErrorCollection();
		$transformer->transform( $dom, $errors );
		$this->assertEquals( $expected, substr_count( $dom->saveHTML(), 'schema.org' ) );
	}

	/**
	 * Test that an empty metadata array configuration does not produce the schema.org meta script.
	 *
	 * @covers ::transform
	 */
	public function test_empty_metadata_configuration() {
		$dom         = new Document();
		$transformer = new AmpSchemaOrgMetadata( new AmpSchemaOrgMetadataConfiguration() );
		$transformer->transform( $dom, new ErrorCollection() );

		$xpath_query = '//script[ @type = "application/ld+json" ]';
		$this->assertEquals( 0, $dom->xpath->query( $xpath_query )->length );
	}
}
