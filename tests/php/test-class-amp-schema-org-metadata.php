<?php
/**
 * Test Site_Health.
 *
 * @package Amp\AmpWP
 */

use Amp\AmpWP\Transformer\AmpSchemaOrgMetadata;
use Amp\AmpWP\Transformer\AmpSchemaOrgMetadataConfiguration;
use Amp\Dom\Document;
use Amp\Optimizer\ErrorCollection;

/**
 * Test Site_Health.
 */
class AmpSchemaOrgMetadataTest extends WP_UnitTestCase {


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
	 * @dataProvider get_schema_script_data()
	 *
	 * @covers       AmpSchemaOrgMetadata::transform()
	 */
	public function test_transform( $script, $expected ) {
		$html        = '<html><head><script type="application/ld+json">%s</script></head><body>Test</body></html>';
		$dom         = Document::fromHtml( sprintf( $html, $script ) );
		$transformer = new AmpSchemaOrgMetadata( new AmpSchemaOrgMetadataConfiguration() );
		$errors      = new ErrorCollection();
		$transformer->transform( $dom, $errors );
		$this->assertEquals( $expected, substr_count( $dom->saveHTML(), 'schema.org' ) );
	}
}
