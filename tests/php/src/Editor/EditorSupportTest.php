<?php

namespace AmpProject\AmpWP\Tests\Editor;

use AMP_Options_Manager;
use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Editor\EditorSupport;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Editor\EditorSupport */
final class EditorSupportTest extends TestCase {

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
	 * @covers ::supports_current_screen()
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
			$this->assertSame( $expected_result, $this->instance->supports_current_screen() );
		} else {
			if ( version_compare( get_bloginfo( 'version' ), DependencySupport::WP_MIN_VERSION, '>=' ) ) {
				$this->assertSame( $expected_result, $this->instance->supports_current_screen() );
			} else {
				$this->assertFalse( $this->instance->supports_current_screen() );
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
		if ( $this->instance->supports_current_screen() ) {
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
		$this->assertFalse( $this->instance->supports_current_screen() );

		gutenberg_register_packages_scripts();

		$this->instance->maybe_show_notice();
		$inline_script = wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false );
		$this->assertStringContainsString( 'AMP functionality is not available', $inline_script );
	}

	/**
	 * Setup test environment to ensure the correct result for ::supports_current_screen().
	 *
	 * @param bool   $post_type_uses_block_editor Whether the post type uses the block editor.
	 * @param bool   $post_type_supports_amp      Whether the post type supports AMP.
	 * @param string $post_type                   Post type ID.
	 */
	private function setup_environment( $post_type_uses_block_editor, $post_type_supports_amp, $post_type = 'foo' ) {
		if ( $post_type_uses_block_editor ) {
			set_current_screen( 'post.php' );
			get_current_screen()->is_block_editor = $post_type_uses_block_editor;
		}

		if ( $post_type_supports_amp ) {
			register_post_type( $post_type, [ 'public' => true ] );
			$GLOBALS['post'] = self::factory()->post->create( [ 'post_type' => $post_type ] );
			wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

			$supported_post_types = array_merge(
				AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES ),
				[ $post_type ]
			);
			AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );
		}
	}
}
