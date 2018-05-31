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
	 */
	public function test_get_and_set_options() {
		global $wp_settings_errors;

		AMP_Options_Manager::register_settings(); // Adds validate_options as filter.
		delete_option( AMP_Options_Manager::OPTION_NAME );
		$this->assertSame( array(), AMP_Options_Manager::get_options() );
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
	}

	/**
	 * Test check_supported_post_type_update_errors.
	 *
	 * @covers AMP_Options_Manager::check_supported_post_type_update_errors()
	 */
	public function test_check_supported_post_type_update_errors() {
		global $wp_settings_errors;

		register_post_type( 'foo', array(
			'public' => true,
			'label'  => 'Foo',
		) );
		AMP_Options_Manager::update_option( 'supported_post_types', array( 'foo' ) );
		AMP_Post_Type_Support::add_post_type_support();
		AMP_Options_Manager::check_supported_post_type_update_errors();
		$this->assertEmpty( get_settings_errors() );

		// Activation error.
		remove_post_type_support( 'foo', amp_get_slug() );
		AMP_Options_Manager::check_supported_post_type_update_errors();
		$errors = get_settings_errors();
		$this->assertCount( 1, $errors );
		$error = current( $errors );
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
}
