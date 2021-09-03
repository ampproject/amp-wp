<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Dom\Document;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/** @coversDefaultClass \AmpProject\AmpWP\ValidationExemption */
final class ValidationExemptionTest extends TestCase {

	use MarkupComparison;

	/** @return array */
	public function get_data_to_test_px_verified_node() {
		return [
			'element'   => [
				'//script',
				'<script type="module">doIt();</script>',
				'<script data-px-verified-tag type="module">doIt();</script>',
			],
			'attribute' => [
				'//img/@onload',
				'<img src="https://example.com/" width="100" height="200" onload="doIt();">',
				'<img data-px-verified-attrs="onload" height="200" onload="doIt();" src="https://example.com/" width="100">',
			],
			'both'      => [
				'//script | //script/@onload | //script/@src',
				'<script onload="doIt" src="https://example.com//"></script>',
				'<script data-px-verified-attrs="onload src" data-px-verified-tag onload="doIt" src="https://example.com//"></script>',
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_px_verified_node
	 *
	 * @covers ::is_px_verified_for_node()
	 * @covers ::mark_node_as_px_verified()
	 * @covers ::is_document_with_px_verified_nodes()
	 */
	public function test_px_verified_node( $xpath, $input, $expected ) {
		$dom   = Document::fromHtml( "<html><body>$input</body></html>" );
		$nodes = $dom->xpath->query( $xpath );
		$this->assertFalse( ValidationExemption::is_document_with_px_verified_nodes( $dom ) );
		foreach ( $nodes as $node ) {
			$this->assertFalse( ValidationExemption::is_px_verified_for_node( $node ) );
		}
		foreach ( $nodes as $node ) {
			$this->assertTrue( ValidationExemption::mark_node_as_px_verified( $node ) );
		}
		foreach ( $nodes as $node ) {
			$this->assertTrue( ValidationExemption::is_px_verified_for_node( $node ) );
		}
		$this->assertFalse( ValidationExemption::is_px_verified_for_node( $dom->body ) );
		$this->assertTrue( ValidationExemption::is_document_with_px_verified_nodes( $dom ) );

		$this->assertSimilarMarkup(
			$expected,
			preg_replace( ':</?body[^>]*>:', '', $dom->saveHTML( $dom->body ) )
		);
	}

	/** @covers ::mark_node_as_px_verified() */
	public function test_mark_node_as_px_verified_for_bad_nodes() {
		$dom          = Document::fromHtml( '' );
		$comment_node = $dom->createComment( 'test' );
		$this->assertFalse( ValidationExemption::mark_node_as_px_verified( $comment_node ) );
	}

	/** @return array */
	public function get_data_to_test_amp_unvalidated_node() {
		return [
			'element'   => [
				'//script',
				'<script type="module">doIt();</script>',
				'<script data-amp-unvalidated-tag type="module">doIt();</script>',
			],
			'attribute' => [
				'//img/@onload',
				'<img src="https://example.com/" width="100" height="200" onload="doIt();">',
				'<img data-amp-unvalidated-attrs="onload" height="200" onload="doIt();" src="https://example.com/" width="100">',
			],
			'both'      => [
				'//script | //script/@onload | //script/@src',
				'<script onload="doIt" src="https://example.com//"></script>',
				'<script data-amp-unvalidated-attrs="onload src" data-amp-unvalidated-tag onload="doIt" src="https://example.com//"></script>',
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_amp_unvalidated_node
	 *
	 * @covers ::is_amp_unvalidated_for_node()
	 * @covers ::mark_node_as_amp_unvalidated()
	 * @covers ::is_document_with_amp_unvalidated_nodes()
	 */
	public function test_amp_unvalidated_node( $xpath, $input, $expected ) {
		$dom   = Document::fromHtml( "<html><body>$input</body></html>" );
		$nodes = $dom->xpath->query( $xpath );
		$this->assertFalse( ValidationExemption::is_document_with_amp_unvalidated_nodes( $dom ) );
		foreach ( $nodes as $node ) {
			$this->assertFalse( ValidationExemption::is_amp_unvalidated_for_node( $node ) );
		}
		foreach ( $nodes as $node ) {
			$this->assertTrue( ValidationExemption::mark_node_as_amp_unvalidated( $node ) );
		}
		foreach ( $nodes as $node ) {
			$this->assertTrue( ValidationExemption::is_amp_unvalidated_for_node( $node ) );
		}
		$this->assertFalse( ValidationExemption::is_amp_unvalidated_for_node( $dom->body ) );
		$this->assertTrue( ValidationExemption::is_document_with_amp_unvalidated_nodes( $dom ) );

		$this->assertSimilarMarkup(
			$expected,
			preg_replace( ':</?body[^>]*>:', '', $dom->saveHTML( $dom->body ) )
		);
	}

	/** @covers ::mark_node_as_amp_unvalidated() */
	public function test_mark_node_as_amp_unvalidated_for_bad_nodes() {
		$dom          = Document::fromHtml( '' );
		$comment_node = $dom->createComment( 'test' );
		$this->assertFalse( ValidationExemption::mark_node_as_amp_unvalidated( $comment_node ) );
	}
}
