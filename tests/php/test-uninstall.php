<?php
/**
 * Test cases for uninstall.php
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\TestCase;

/**
 * @runInSeparateProcess
 * @group uninstall
 */
class Test_Uninstall extends TestCase {

	/** @var bool */
	private $was_using_ext_object_cache;

	public function set_up() {
		parent::set_up();
		$this->was_using_ext_object_cache = wp_using_ext_object_cache();
	}

	public function tear_down() {
		wp_using_ext_object_cache( $this->was_using_ext_object_cache );
		parent::tear_down();
	}

	/**
	 * @covers \AmpProject\AmpWP\delete_options()
	 * @covers \AmpProject\AmpWP\delete_user_metadata()
	 * @covers \AmpProject\AmpWP\delete_posts()
	 * @covers \AmpProject\AmpWP\delete_terms()
	 * @covers \AmpProject\AmpWP\delete_transients()
	 * @covers \AmpProject\AmpWP\remove_plugin_data()
	 */
	public function test_uninstall_php() {
		wp_using_ext_object_cache( false );

		// Create dummy data.
		$blog_name = 'Sample Blog Name';
		update_option( 'blogname', $blog_name );
		update_option(
			'amp-options',
			[ 'reader_theme' => 'foo' ]
		);

		$users = $this->factory()->user->create_many( 2, [ 'role' => 'administrator' ] );

		foreach ( $users as $user ) {
			update_user_meta( $user, 'amp_dev_tools_enabled', 'Yes' );
			update_user_meta( $user, 'additional_user_meta', 'Yes' );
		}

		$amp_validated_post = $this->factory()->post->create_and_get(
			[
				'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
			]
		);

		$page_post = $this->factory()->post->create_and_get(
			[
				'post_type' => 'page',
			]
		);

		$amp_error_term = $this->factory()->term->create_and_get(
			[
				'taxonomy' => 'amp_validation_error',
			]
		);

		$post_tag_term = $this->factory()->term->create_and_get(
			[
				'taxonomy' => 'post_tag',
			]
		);

		$theme_mod_name = 'amp_customize_setting_modified_timestamps';
		set_theme_mod( 'color', 'blue' );
		set_theme_mod( $theme_mod_name, [ 'color' => time() ] );
		update_option(
			'theme_mods_foo',
			[
				'color'         => 'red',
				$theme_mod_name => [
					'color' => time(),
				],
			]
		);

		$transient_groups_to_remove = [
			'amp-parsed-stylesheet-v...',
			'amp_img_...',
			'amp_new_validation_error_urls_count',
			'amp_error_index_counts',
			'amp_plugin_activation_validation_errors',
			'amp_themes_wporg',
			'amp_lock_...',
		];

		foreach ( $transient_groups_to_remove as $transient_group ) {
			set_transient( $transient_group, 'Sample value', 10000 );
		}

		set_transient( 'amp_sample_group', 'AMP Sample value', 10000 );

		// Mock uninstall const.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'Yes' );
		}

		require AMP__DIR__ . '/uninstall.php';

		$this->flush_cache();

		$this->assertEmpty( get_option( 'amp-option', false ) );
		$this->assertEmpty( get_post( $amp_validated_post->ID ) );
		$this->assertEmpty( get_term( $amp_error_term->term_id ) );

		$this->assertTrue( is_a( get_post( $page_post->ID ), 'WP_Post' ) );
		$this->assertTrue( is_a( get_term( $post_tag_term->term_id ), 'WP_Term' ) );

		$this->assertEquals( $blog_name, get_option( 'blogname', false ) );

		$this->assertEquals( 'blue', get_theme_mod( 'color' ) );
		$this->assertFalse( get_theme_mod( $theme_mod_name ) );
		$foo_theme_mods = get_option( 'theme_mods_foo' );
		$this->assertEquals( 'red', $foo_theme_mods['color'] );
		$this->assertArrayNotHasKey( $theme_mod_name, $foo_theme_mods );

		foreach ( $transient_groups_to_remove as $transient_group ) {
			$this->assertEmpty( get_transient( $transient_group ) );
		}

		$this->assertEquals( 'AMP Sample value', get_transient( 'amp_sample_group' ) );

		foreach ( $users as $user ) {
			$this->assertEmpty( get_user_meta( $user, 'amp_dev_tools_enabled', true ) );
			$this->assertEquals( 'Yes', get_user_meta( $user, 'additional_user_meta', true ) );
		}
	}
}
