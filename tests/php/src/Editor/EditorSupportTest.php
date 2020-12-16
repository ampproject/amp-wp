<?php

namespace AmpProject\AmpWP\Tests\Editor;

use AmpProject\AmpWP\Editor\EditorSupport;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Tests\Editor\EditorSupport */
final class EditorSupportTest extends WP_UnitTestCase {

	/** @var EditorSupport */
	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new EditorSupport();
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

	/** @covers ::has_support_from_gutenberg_plugin */
	public function test_has_support_from_gutenberg_plugin() {
		if ( defined( 'GUTENBERG_VERSION' ) ) {
			$this->assertTrue( $this->instance->has_support_from_gutenberg_plugin() );
		} else {
			if ( version_compare( get_bloginfo( 'version' ), EditorSupport::WP_MIN_VERSION, '>=' ) ) {
				$this->assertTrue( $this->instance->has_support_from_core() );
			} else {
				$this->assertFalse( $this->instance->has_support_from_core() );
			}
		}
	}

	public function test_has_support_from_core() {
		if ( version_compare( get_bloginfo( 'version' ), EditorSupport::WP_MIN_VERSION, '>=' ) ) {
			$this->assertTrue( $this->instance->has_support_from_core() );
		} else {
			$this->assertFalse( $this->instance->has_support_from_core() );
		}
	}

	public function test_maybe_show_notice_for_supported_post_type_and_supported_user() {
		global $post;

		set_current_screen( 'edit.php' );
		$post = $this->factory()->post->create();
		setup_postdata( get_post( $post ) );

		get_current_screen()->is_block_editor( true );
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->instance->maybe_show_notice();

		if ( version_compare( get_bloginfo( 'version' ), EditorSupport::WP_MIN_VERSION, '<' ) ) {
			$this->assertContains(
				'AMP functionality',
				wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false )
			);
		} else {
			$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		}
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}

	public function test_dont_show_notice_for_unsupported_post_type() {
		global $post;

		set_current_screen( 'edit.php' );
		register_post_type( 'my-post-type' );
		$post = $this->factory()->post->create( [ 'post_type' => 'my-post-type' ] );
		setup_postdata( get_post( $post ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->instance->maybe_show_notice();
		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}

	public function test_maybe_show_notice_for_unsupported_user() {
		global $post;

		set_current_screen( 'edit.php' );
		$post = $this->factory()->post->create();
		setup_postdata( get_post( $post ) );

		$this->instance->maybe_show_notice();

		$this->assertFalse( wp_scripts()->print_inline_script( 'wp-edit-post', 'after', false ) );
		unset( $GLOBALS['current_screen'] );
		unset( $GLOBALS['wp_scripts'] );
	}
}
