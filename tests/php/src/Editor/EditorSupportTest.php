<?php

namespace AmpProject\AmpWP\Tests\Editor;

use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Editor\EditorSupport;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\WithBlockEditorSupport;
use AmpProject\AmpWP\Tests\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Editor\EditorSupport */
final class EditorSupportTest extends TestCase {

	use WithBlockEditorSupport;

	/** @var EditorSupport */
	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new EditorSupport( new DependencySupport() );

		unset( $GLOBALS['current_screen'], $GLOBALS['wp_scripts'] );
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( EditorSupport::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/** @covers ::register() */
	public function test_register() {
		$this->instance->register();

		$this->assertEquals( 99, has_action( 'admin_enqueue_scripts', [ $this->instance, 'maybe_show_notice' ] ) );
	}

	/**
	 * Test data for test_supports_current_screen().
	 *
	 * @return array
	 */
	public function get_data_for_test_supports_current_screen() {
		return [
			'supports post type and amp'         => [ true, true, true ],
			'supports only post type'            => [ true, false, false ],
			'supports only amp'                  => [ false, true, false ],
			'does not support post type nor amp' => [ false, false, false ],
		];
	}

	/**
	 * @covers ::is_current_screen_supported_block_editor_for_amp_enabled_post_type()
	 * @dataProvider get_data_for_test_supports_current_screen()
	 *
	 * @param bool $post_type_uses_block_editor Whether post type can be edited in the block editor.
	 * @param bool $post_type_supports_amp      Whether post type supports AMP.
	 * @param bool $expected_result             Expected result for test assertions.
	 */
	public function test_supports_current_screen( $post_type_uses_block_editor, $post_type_supports_amp, $expected_result ) {
		$this->setup_environment( $post_type_uses_block_editor, $post_type_supports_amp );

		if (
			defined( 'GUTENBERG_VERSION' )
			&&
			version_compare( GUTENBERG_VERSION, DependencySupport::GB_MIN_VERSION, '>=' )
		) {
			$this->assertSame( $expected_result, $this->instance->is_current_screen_supported_block_editor_for_amp_enabled_post_type() );
		} else {
			if ( version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) ) {
				$this->assertSame( $expected_result, $this->instance->is_current_screen_supported_block_editor_for_amp_enabled_post_type() );
			} else {
				// WP < 5.0 doesn't include the block editor, so it should not be supported.
				$this->assertFalse( $this->instance->is_current_screen_supported_block_editor_for_amp_enabled_post_type() );
			}
		}
	}

	/** @covers ::maybe_show_notice() */
	public function test_dont_show_notice_if_no_screen_defined() {
		$this->instance->maybe_show_notice();
		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
	}

	/** @covers ::maybe_show_notice() */
	public function test_dont_show_notice_for_unsupported_post_type() {
		$this->setup_environment( true, false );

		$this->instance->maybe_show_notice();
		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
	}

	/** @covers ::maybe_show_notice() */
	public function test_show_notice_for_supported_post_type() {
		if ( version_compare( get_bloginfo( 'version' ), DependencySupport::WP_MIN_VERSION, '<' ) ) {
			$this->markTestSkipped();
		}

		$this->setup_environment( true, true );

		$this->instance->maybe_show_notice();
		if ( $this->instance->is_current_screen_supported_block_editor_for_amp_enabled_post_type() ) {
			$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		} else {
			$this->assertStringContainsString(
				'AMP functionality is not available',
				wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false )
			);
		}
	}

	/** @covers ::maybe_show_notice() */
	public function test_maybe_show_notice_for_unsupported_user() {
		$this->setup_environment( true, true );
		wp_set_current_user( self::factory()->user->create() );

		$this->instance->maybe_show_notice();

		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
	}

	/** @covers ::maybe_show_notice() */
	public function test_maybe_show_notice_for_gutenberg_4_9() {
		if ( ! defined( 'GUTENBERG_VERSION' ) || version_compare( GUTENBERG_VERSION, '4.9.0', '>' ) ) {
			$this->markTestSkipped( 'Test only applicable to Gutenberg v4.9.0 and older.' );
		}

		$this->setup_environment( true, true );
		$this->assertFalse( $this->instance->is_current_screen_supported_block_editor_for_amp_enabled_post_type() );

		gutenberg_register_packages_scripts();
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->instance->maybe_show_notice();
		$inline_script = wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false );
		$this->assertStringContainsString( 'AMP functionality is not available', $inline_script );
	}
}
