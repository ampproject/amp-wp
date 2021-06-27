<?php

namespace AmpProject\AmpWP\Tests\Editor;

use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\Editor\EditorSupport;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Editor\EditorSupport */
final class EditorSupportTest extends WP_UnitTestCase {

	use AssertContainsCompatibility;

	/** @var EditorSupport */
	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new EditorSupport( new DependencySupport() );
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

	public function test_editor_supports_amp_block_editor_features() {
		if (
			defined( 'GUTENBERG_VERSION' )
			&&
			version_compare( GUTENBERG_VERSION, DependencySupport::GB_MIN_VERSION, '>=' )
		) {
			$this->assertTrue( $this->instance->editor_supports_amp_block_editor_features() );
		} else {
			if ( version_compare( get_bloginfo( 'version' ), DependencySupport::WP_MIN_VERSION, '>=' ) ) {
				$this->assertTrue( $this->instance->editor_supports_amp_block_editor_features() );
			} else {
				$this->assertFalse( $this->instance->editor_supports_amp_block_editor_features() );
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
		global $post;

		set_current_screen( 'post.php' );
		register_post_type( 'my-post-type' );
		$post = $this->factory()->post->create( [ 'post_type' => 'my-post-type' ] );
		setup_postdata( get_post( $post ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->instance->maybe_show_notice();
		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}

	/** @covers ::maybe_show_notice() */
	public function test_show_notice_for_supported_post_type() {
		global $post;

		if ( version_compare( get_bloginfo( 'version' ), DependencySupport::WP_MIN_VERSION, '<' ) ) {
			$this->markTestSkipped();
		}

		set_current_screen( 'post.php' );
		$post = $this->factory()->post->create();
		setup_postdata( get_post( $post ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->instance->maybe_show_notice();
		if ( $this->instance->editor_supports_amp_block_editor_features() ) {
			$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		} else {
			$this->assertContains(
				'AMP functionality is not available',
				wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false )
			);
		}
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}

	/** @covers ::maybe_show_notice() */
	public function test_maybe_show_notice_for_unsupported_user() {
		global $post;

		set_current_screen( 'post.php' );
		$post = $this->factory()->post->create();
		setup_postdata( get_post( $post ) );

		$this->instance->maybe_show_notice();

		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}

	/** @covers ::maybe_show_notice() */
	public function test_maybe_show_notice_with_cpt_supporting_gutenberg_but_not_amp() {
		global $post;

		if ( ! $this->instance->editor_supports_amp_block_editor_features() ) {
			$this->markTestSkipped();
		}

		register_post_type(
			'my-gb-post-type',
			[
				'public'       => true,
				'show_in_rest' => true,
				'supports'     => [ 'editor' ],
			]
		);

		set_current_screen( 'post.php' );
		$post = $this->factory()->post->create( [ 'post_type' => 'my-gb-post-type' ] );
		setup_postdata( get_post( $post ) );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->instance->maybe_show_notice();
		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}

	/** @covers ::maybe_show_notice() */
	public function test_maybe_show_notice_for_gutenberg_4_9() {
		global $post;
		if ( ! defined( 'GUTENBERG_VERSION' ) || version_compare( GUTENBERG_VERSION, '4.9.0', '>' ) ) {
			$this->markTestSkipped( 'Test only applicable to Gutenberg v4.9.0 and older.' );
		}

		$this->assertFalse( $this->instance->editor_supports_amp_block_editor_features() );

		gutenberg_register_packages_scripts();
		set_current_screen( 'post.php' );
		$post = $this->factory()->post->create();
		setup_postdata( get_post( $post ) );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->instance->maybe_show_notice();
		$inline_script = wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false );
		$this->assertStringContains( 'AMP functionality is not available', $inline_script );
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}
}
