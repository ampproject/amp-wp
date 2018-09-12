<?php
/**
 * Tests for AMP_Options_Manager.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Options_Manager.
 *
 * @covers AMP_Options_Manager
 */
class Test_AMP_Options_Manager extends WP_UnitTestCase {

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		parent::tearDown();
		unregister_post_type( 'foo' );
	}

	/**
	 * Test constants.
	 */
	public function test_constants() {
		$this->assertEquals( 'amp-options', AMP_Options_Manager::OPTION_NAME );
	}

	/**
	 * Test register_settings.
	 *
	 * @covers AMP_Options_Manager::register_settings()
	 */
	public function test_register_settings() {
		AMP_Options_Manager::register_settings();
		$registered_settings = get_registered_settings();
		$this->assertArrayHasKey( AMP_Options_Manager::OPTION_NAME, $registered_settings );
		$this->assertEquals( 'array', $registered_settings[ AMP_Options_Manager::OPTION_NAME ]['type'] );

		$this->assertEquals( 10, has_action( 'update_option_' . AMP_Options_Manager::OPTION_NAME, array( 'AMP_Options_Manager', 'maybe_flush_rewrite_rules' ) ) );
	}

	/**
	 * Test maybe_flush_rewrite_rules.
	 *
	 * @covers AMP_Options_Manager::maybe_flush_rewrite_rules()
	 */
	public function test_maybe_flush_rewrite_rules() {
		global $wp_rewrite;
		$wp_rewrite->init();
		AMP_Options_Manager::register_settings();
		$dummy_rewrite_rules = array( 'previous' => true );

		// Check change to supported_post_types.
		update_option( 'rewrite_rules', $dummy_rewrite_rules );
		AMP_Options_Manager::maybe_flush_rewrite_rules(
			array( 'supported_post_types' => array( 'page' ) ),
			array()
		);
		$this->assertEmpty( get_option( 'rewrite_rules' ) );

		// Check update of supported_post_types but no change.
		update_option( 'rewrite_rules', $dummy_rewrite_rules );
		update_option( AMP_Options_Manager::OPTION_NAME, array(
			array( 'supported_post_types' => array( 'page' ) ),
			array( 'supported_post_types' => array( 'page' ) ),
		) );
		$this->assertEquals( $dummy_rewrite_rules, get_option( 'rewrite_rules' ) );

		// Check changing a different property.
		update_option( 'rewrite_rules', array( 'previous' => true ) );
		update_option( AMP_Options_Manager::OPTION_NAME, array(
			array( 'foo' => 'new' ),
			array( 'foo' => 'old' ),
		) );
		$this->assertEquals( $dummy_rewrite_rules, get_option( 'rewrite_rules' ) );
	}

	/**
	 * Test get_options.
	 *
	 * @covers AMP_Options_Manager::get_options()
	 * @covers AMP_Options_Manager::get_option()
	 * @covers AMP_Options_Manager::update_option()
	 * @covers AMP_Options_Manager::validate_options()
	 * @covers AMP_Theme_Support::reset_cache_miss_url_option()
	 */
	public function test_get_and_set_options() {
		global $wp_settings_errors;
		wp_using_ext_object_cache( true ); // turn on external object cache flag.
		AMP_Options_Manager::register_settings(); // Adds validate_options as filter.
		delete_option( AMP_Options_Manager::OPTION_NAME );
		$this->assertEquals(
			array(
				'theme_support'           => 'disabled',
				'supported_post_types'    => array( 'post' ),
				'analytics'               => array(),
				'force_sanitization'      => true,
				'accept_tree_shaking'     => true,
				'disable_admin_bar'       => false,
				'all_templates_supported' => true,
				'supported_templates'     => array( 'is_singular' ),
				'enable_response_caching' => true,
			),
			AMP_Options_Manager::get_options()
		);
		$this->assertSame( false, AMP_Options_Manager::get_option( 'foo' ) );
		$this->assertSame( 'default', AMP_Options_Manager::get_option( 'foo', 'default' ) );
		// Test supported_post_types validation.
		AMP_Options_Manager::update_option( 'supported_post_types', array( 'post', 'page', 'attachment' ) );
		$this->assertSame(
			array(
				'post',
				'page',
				'attachment',
			),
			AMP_Options_Manager::get_option( 'supported_post_types' )
		);

		// Test analytics validation with missing fields.
		AMP_Options_Manager::update_option( 'analytics', array(
			'bad' => array(),
		) );
		$errors = get_settings_errors( AMP_Options_Manager::OPTION_NAME );
		$this->assertEquals( 'missing_analytics_vendor_or_config', $errors[0]['code'] );
		$wp_settings_errors = array();

		// Test analytics validation with bad JSON.
		AMP_Options_Manager::update_option( 'analytics', array(
			'__new__' => array(
				'type'   => 'foo',
				'config' => 'BAD',
			),
		) );
		$errors = get_settings_errors( AMP_Options_Manager::OPTION_NAME );
		$this->assertEquals( 'invalid_analytics_config_json', $errors[0]['code'] );
		$wp_settings_errors = array();

		// Test analytics validation with good fields.
		AMP_Options_Manager::update_option( 'analytics', array(
			'__new__' => array(
				'type'   => 'foo',
				'config' => '{"good":true}',
			),
		) );
		$this->assertEmpty( get_settings_errors( AMP_Options_Manager::OPTION_NAME ) );

		// Test analytics validation with duplicate check.
		AMP_Options_Manager::update_option( 'analytics', array(
			'__new__' => array(
				'type'   => 'foo',
				'config' => '{"good":true}',
			),
		) );
		$errors = get_settings_errors( AMP_Options_Manager::OPTION_NAME );
		$this->assertEquals( 'duplicate_analytics_entry', $errors[0]['code'] );
		$wp_settings_errors = array();

		// Confirm format of entry ID.
		$entries = AMP_Options_Manager::get_option( 'analytics' );
		$entry   = current( $entries );
		$id      = substr( md5( $entry['type'] . $entry['config'] ), 0, 12 );
		$this->assertArrayHasKey( $id, $entries );
		$this->assertEquals( 'foo', $entries[ $id ]['type'] );
		$this->assertEquals( '{"good":true}', $entries[ $id ]['config'] );

		// Confirm adding another entry works.
		AMP_Options_Manager::update_option( 'analytics', array(
			'__new__' => array(
				'type'   => 'bar',
				'config' => '{"good":true}',
			),
		) );
		$entries = AMP_Options_Manager::get_option( 'analytics' );
		$this->assertCount( 2, AMP_Options_Manager::get_option( 'analytics' ) );
		$this->assertArrayHasKey( $id, $entries );

		// Confirm updating an entry works.
		AMP_Options_Manager::update_option( 'analytics', array(
			$id => array(
				'id'     => $id,
				'type'   => 'foo',
				'config' => '{"very_good":true}',
			),
		) );
		$entries = AMP_Options_Manager::get_option( 'analytics' );
		$this->assertEquals( 'foo', $entries[ $id ]['type'] );
		$this->assertEquals( '{"very_good":true}', $entries[ $id ]['config'] );

		// Confirm deleting an entry works.
		AMP_Options_Manager::update_option( 'analytics', array(
			$id => array(
				'id'     => $id,
				'type'   => 'foo',
				'config' => '{"very_good":true}',
				'delete' => true,
			),
		) );
		$entries = AMP_Options_Manager::get_option( 'analytics' );
		$this->assertCount( 1, $entries );
		$this->assertArrayNotHasKey( $id, $entries );

		// Test re-enabling response cache works.
		add_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, 'http://example.org/test-post' );
		AMP_Options_Manager::update_option( 'enable_response_caching', true );
		$this->assertTrue( AMP_Options_Manager::get_option( 'enable_response_caching' ) );
		$this->assertNull( get_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, null ) );
		wp_using_ext_object_cache( false ); // turn off external object cache.
		add_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, 'http://example.org/test-post' );
		AMP_Options_Manager::update_option( 'enable_response_caching', true );
		$this->assertFalse( AMP_Options_Manager::get_option( 'enable_response_caching' ) );
		$this->assertEquals( 'http://example.org/test-post', get_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, null ) );
	}

	/**
	 * Test check_supported_post_type_update_errors.
	 *
	 * @covers AMP_Options_Manager::check_supported_post_type_update_errors()
	 */
	public function test_check_supported_post_type_update_errors() {
		global $wp_settings_errors;
		$wp_settings_errors = array(); // clear any errors before starting.
		add_theme_support( 'amp' );
		register_post_type( 'foo', array(
			'public' => true,
			'label'  => 'Foo',
		) );
		AMP_Post_Type_Support::add_post_type_support();

		// Test when 'all_templates_supported' is selected.
		AMP_Options_Manager::update_option( 'theme_support', 'native' );
		AMP_Options_Manager::update_option( 'all_templates_supported', true );
		AMP_Options_Manager::update_option( 'supported_post_types', array( 'post' ) );
		AMP_Options_Manager::check_supported_post_type_update_errors();
		$this->assertEmpty( get_settings_errors() );

		// Test when 'all_templates_supported' is not selected.
		AMP_Options_Manager::update_option( 'theme_support', 'native' );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		foreach ( get_post_types() as $post_type ) {
			if ( 'foo' !== $post_type ) {
				remove_post_type_support( $post_type, amp_get_slug() );
			}
		}
		AMP_Options_Manager::update_option( 'supported_post_types', array( 'foo' ) );
		AMP_Options_Manager::check_supported_post_type_update_errors();
		$this->assertEmpty( get_settings_errors() );

		// Test when 'all_templates_supported' is not selected, and theme support is also disabled.
		add_post_type_support( 'post', amp_get_slug() );
		AMP_Options_Manager::update_option( 'theme_support', 'disabled' );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		AMP_Options_Manager::update_option( 'supported_post_types', array( 'post' ) );
		AMP_Options_Manager::check_supported_post_type_update_errors();
		$settings_errors    = get_settings_errors();
		$wp_settings_errors = array();
		$this->assertCount( 1, $settings_errors );
		$this->assertEquals( 'foo_deactivation_error', $settings_errors[0]['code'] );

		// Activation error.
		remove_post_type_support( 'post', amp_get_slug() );
		remove_post_type_support( 'foo', amp_get_slug() );
		AMP_Options_Manager::update_option( 'supported_post_types', array( 'foo' ) );
		AMP_Options_Manager::update_option( 'theme_support', 'disabled' );
		AMP_Options_Manager::check_supported_post_type_update_errors();
		$settings_errors = get_settings_errors();
		$this->assertCount( 1, $settings_errors );
		$error = current( $settings_errors );
		$this->assertEquals( 'foo_activation_error', $error['code'] );
		$wp_settings_errors = array();

		// Deactivation error.
		AMP_Options_Manager::update_option( 'supported_post_types', array() );
		add_post_type_support( 'foo', amp_get_slug() );
		AMP_Options_Manager::check_supported_post_type_update_errors();
		$errors = get_settings_errors();
		$this->assertCount( 1, $errors );
		$error = current( $errors );
		$this->assertEquals( 'foo_deactivation_error', $error['code'] );
		$wp_settings_errors = array();
	}

	/**
	 * Test for persistent_object_caching_notice()
	 *
	 * @covers AMP_Options_Manager::persistent_object_caching_notice()
	 */
	public function test_persistent_object_caching_notice() {
		set_current_screen( 'toplevel_page_amp-options' );
		$text = 'The AMP plugin performs at its best when persistent object cache is enabled.';

		wp_using_ext_object_cache( null );
		ob_start();
		AMP_Options_Manager::persistent_object_caching_notice();
		$this->assertContains( $text, ob_get_clean() );

		wp_using_ext_object_cache( true );
		ob_start();
		AMP_Options_Manager::persistent_object_caching_notice();
		$this->assertNotContains( $text, ob_get_clean() );

		set_current_screen( 'edit.php' );

		wp_using_ext_object_cache( null );
		ob_start();
		AMP_Options_Manager::persistent_object_caching_notice();
		$this->assertNotContains( $text, ob_get_clean() );

		wp_using_ext_object_cache( true );
		ob_start();
		AMP_Options_Manager::persistent_object_caching_notice();
		$this->assertNotContains( $text, ob_get_clean() );

		wp_using_ext_object_cache( false );
	}

	/**
	 * Test for render_cache_miss_notice()
	 *
	 * @covers AMP_Options_Manager::show_response_cache_disabled_notice()
	 */
	public function test_show_response_cache_disabled_notice() {
		$this->assertFalse( AMP_Options_Manager::show_response_cache_disabled_notice() );

		wp_using_ext_object_cache( true ); // turn on external object cache flag.
		$this->assertFalse( AMP_Options_Manager::show_response_cache_disabled_notice() );

		AMP_Options_Manager::update_option( 'enable_response_caching', false );
		$this->assertFalse( AMP_Options_Manager::show_response_cache_disabled_notice() );

		add_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, site_url() );
		$this->assertTrue( AMP_Options_Manager::show_response_cache_disabled_notice() );

		// Test if external object cache is now disabled.
		wp_using_ext_object_cache( false );
		$this->assertFalse( AMP_Options_Manager::show_response_cache_disabled_notice() );
	}

	/**
	 * Test for render_cache_miss_notice()
	 *
	 * @covers AMP_Options_Manager::render_cache_miss_notice()
	 */
	public function test_render_cache_miss_notice() {
		set_current_screen( 'toplevel_page_amp-options' );
		wp_using_ext_object_cache( true ); // turn on external object cache flag.

		// Test default state.
		ob_start();
		AMP_Options_Manager::render_cache_miss_notice();
		$this->assertEmpty( ob_get_clean() );

		// Test when disabled but not exceeded.
		AMP_Options_Manager::update_option( 'enable_response_caching', false );
		ob_start();
		AMP_Options_Manager::render_cache_miss_notice();
		$this->assertEmpty( ob_get_clean() );

		// Test when disabled and exceeded, but external object cache is disabled.
		add_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, site_url() );
		wp_using_ext_object_cache( false ); // turn off external object cache flag.
		ob_start();
		AMP_Options_Manager::render_cache_miss_notice();
		$this->assertEmpty( ob_get_clean() );

		// Test when disabled, exceeded, and external object cache is enabled.
		wp_using_ext_object_cache( true ); // turn off external object cache flag.
		ob_start();
		AMP_Options_Manager::render_cache_miss_notice();
		$notice = ob_get_clean();
		$this->assertContains( '<div class="notice notice-warning is-dismissible">', $notice );

		// Test when enabled but not exceeded.
		delete_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION );
		ob_start();
		AMP_Options_Manager::render_cache_miss_notice();
		$this->assertEmpty( ob_get_clean() );

		// Test when on a different screen.
		set_current_screen( 'edit.php' );
		ob_start();
		AMP_Options_Manager::render_cache_miss_notice();
		$this->assertEmpty( ob_get_clean() );

		wp_using_ext_object_cache( false ); // turn off external object cache flag.
	}
}
