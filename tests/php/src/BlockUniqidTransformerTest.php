<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Block_Uniqid_Sanitizer;
use AmpProject\AmpWP\BlockUniqidTransformer;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/** @coversDefaultClass \AmpProject\AmpWP\BlockUniqidTransformer */
final class BlockUniqidTransformerTest extends DependencyInjectedTestCase {

	use MarkupComparison;
	use PrivateAccess;

	/** @var string */
	private $original_wp_version;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		$this->original_wp_version = $GLOBALS['wp_version'];
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		$GLOBALS['wp_version'] = $this->original_wp_version;
		parent::tearDown();
	}

	public function test_it_can_be_initialized() {
		$instance = $this->injector->make( BlockUniqidTransformer::class );
		$this->assertSame(
			defined( 'GUTENBERG_VERSION' ) ? GUTENBERG_VERSION : null,
			$this->get_private_property( $instance, 'gutenberg_version' )
		);
		$this->assertInstanceOf( Registerable::class, $instance );
		$this->assertInstanceOf( Service::class, $instance );
	}

	/**
	 * @covers ::is_necessary()
	 * @covers ::is_affected_gutenberg_version()
	 * @covers ::is_affected_wordpress_version()
	 */
	public function test_is_necessary() {
		$instance = $this->injector->make( BlockUniqidTransformer::class );

		$gutenberg_data = $this->get_data_to_test_is_affected_gutenberg_version();
		$wp_data        = $this->get_data_to_test_is_affected_wordpress_version();

		foreach ( $wp_data as list( $wp_version, $wp_expected ) ) {
			$GLOBALS['wp_version'] = $wp_version;
			foreach ( $gutenberg_data as list( $gb_version, $gb_expected ) ) {
				$this->set_private_property( $instance, 'gutenberg_version', $gb_version );

				$this->assertSame(
					$wp_expected || $gb_expected,
					$instance->is_necessary(),
					"Unexpected for WP $wp_version and Gutenberg $gb_version"
				);
			}
		}
	}

	/** @return array */
	public function get_data_to_test_is_affected_gutenberg_version() {
		return [
			'none' => [ null, false ],
			'10.6' => [ '10.6', false ],
			'10.7' => [ '10.7', true ],
			'11.0' => [ '11.0', true ],
			'12.0' => [ '12.0', true ],
			'12.6' => [ '12.6', true ],
			'12.7' => [ '12.7', false ],
			'13.0' => [ '13.0', false ],
		];
	}

	/**
	 * @covers ::is_affected_gutenberg_version()
	 * @dataProvider get_data_to_test_is_affected_gutenberg_version
	 * @param string $gutenberg_version Gutenberg version.
	 * @param bool   $is_affected       Is affected.
	 */
	public function test_is_affected_gutenberg_version( $gutenberg_version, $is_affected ) {
		$instance = $this->injector->make( BlockUniqidTransformer::class );

		// If Gutenberg is active for the tests, ignore the GUTENBERG_VERSION constant.
		$this->set_private_property( $instance, 'gutenberg_version', null );

		$this->assertSame( $is_affected, $instance->is_affected_gutenberg_version( $gutenberg_version ) );
	}

	/** @return array */
	public function get_data_to_test_is_affected_wordpress_version() {
		return [
			'5.7.0' => [ '5.7.0', false ],
			'5.8.0' => [ '5.8.0', true ],
			'5.9.0' => [ '5.9.0', true ],
			'5.9.1' => [ '5.9.1', true ],
			'5.9.2' => [ '5.9.2', true ],
			'5.9.3' => [ '5.9.3', false ],
			'6.0.0' => [ '6.0.0', false ],
		];
	}

	/**
	 * @covers ::is_affected_wordpress_version()
	 * @dataProvider get_data_to_test_is_affected_wordpress_version
	 * @param string $wp_version  WP version.
	 * @param bool   $is_affected Is affected.
	 */
	public function test_is_affected_wordpress_version( $wp_version, $is_affected ) {
		$instance = $this->injector->make( BlockUniqidTransformer::class );
		$this->assertSame( $is_affected, $instance->is_affected_wordpress_version( $wp_version ) );
	}

	/** @return array */
	public function get_data_to_test_register() {
		return [
			'necessary'   => [
				'5.9.0',
				true,
			],
			'unnecessary' => [
				'6.0.0',
				false,
			],
		];
	}

	/**
	 * @covers ::register()
	 * @dataProvider get_data_to_test_register
	 * @param string $wp_version WP version.
	 * @param bool   $expected   Expected.
	 */
	public function test_register( $wp_version, $expected ) {
		remove_all_filters( 'amp_content_sanitizers' );

		$this->assertArrayNotHasKey(
			AMP_Block_Uniqid_Sanitizer::class,
			amp_get_content_sanitizers()
		);

		$instance = $this->injector->make( BlockUniqidTransformer::class );
		$this->set_private_property( $instance, 'gutenberg_version', null );
		$GLOBALS['wp_version'] = $wp_version;

		$instance->register();
		$this->assertSame( $expected, $instance->is_necessary() );
		if ( $expected ) {
			$this->assertArrayHasKey(
				AMP_Block_Uniqid_Sanitizer::class,
				amp_get_content_sanitizers()
			);
		} else {
			$this->assertArrayNotHasKey(
				AMP_Block_Uniqid_Sanitizer::class,
				amp_get_content_sanitizers()
			);
		}
	}
}
