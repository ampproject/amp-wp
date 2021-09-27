<?php
/**
 * Tests for AMP_Validation_Manager class.
 *
 * @package AMP
 */

// phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning

use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Dom\Options;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\HandleValidation;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\WithBlockEditorSupport;
use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\Dom\Document;

/**
 * Tests for AMP_Validation_Manager class.
 *
 * @covers AMP_Validation_Manager
 * @since 0.7
 */
class Test_AMP_Validation_Manager extends DependencyInjectedTestCase {

	use HandleValidation;
	use PrivateAccess;
	use WithBlockEditorSupport;
	use WithoutBlockPreRendering {
		setUp as public prevent_block_pre_render;
	}

	/**
	 * The name of the tested class.
	 *
	 * @var string
	 */
	const TESTED_CLASS = AMP_Validation_Manager::class;

	/**
	 * An instance of DOMElement to test.
	 *
	 * @var DOMElement
	 */
	public $node;

	/**
	 * A tag that the sanitizer should strip.
	 *
	 * @var string
	 */
	public $disallowed_tag = '<script async src="https://example.com/script"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

	/**
	 * The name of a tag that the sanitizer should strip.
	 *
	 * @var string
	 */
	public $disallowed_tag_name = 'script';

	/**
	 * The name of an attribute that the sanitizer should strip.
	 *
	 * @var string
	 */
	public $disallowed_attribute_name = 'onload';

	/**
	 * A mock plugin name that outputs invalid markup.
	 *
	 * @var string
	 */
	public $plugin_name = 'foo-bar';

	/**
	 * A valid image that sanitizers should not alter.
	 *
	 * @var string
	 */
	public $valid_amp_img = '<amp-img id="img-123" media="(min-width: 600x)" src="/assets/example.jpg" width="200" height="500" layout="responsive"></amp-img>';

	/**
	 * The name of the tag to test.
	 *
	 * @var string
	 */
	const TAG_NAME = 'img';

	/**
	 * Backed up $wp_registered_widgets.
	 *
	 * @var array
	 */
	protected $original_wp_registered_widgets;

	/**
	 * Backed up $wp_widget_factory.
	 *
	 * @var WP_Widget_Factory
	 */
	protected $original_wp_widget_factory;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 * @global $wp_registered_widgets
	 */
	public function setUp() {
		unset( $GLOBALS['wp_scripts'], $GLOBALS['wp_styles'] );
		$this->prevent_block_pre_render();

		$dom_document = new Document( '1.0', 'utf-8' );
		$this->node   = $dom_document->createElement( self::TAG_NAME );
		$dom_document->appendChild( $this->node );
		AMP_Validation_Manager::reset_validation_results();
		$this->original_wp_registered_widgets = $GLOBALS['wp_registered_widgets'];
		$this->original_wp_widget_factory     = $GLOBALS['wp_widget_factory'];

		if ( class_exists( 'WP_Block_Type_Registry' ) ) {
			foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $block ) {
				if ( 'amp/' === substr( $block->name, 0, 4 ) ) {
					WP_Block_Type_Registry::get_instance()->unregister( $block->name );
				}
			}
		}
		delete_option( AMP_Options_Manager::OPTION_NAME ); // Make sure default reader mode option does not override theme support being added.
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		$GLOBALS['wp_registered_widgets'] = $this->original_wp_registered_widgets;
		$GLOBALS['wp_widget_factory']     = $this->original_wp_widget_factory;
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		AMP_Validation_Manager::$validation_error_status_overrides = [];
		$_REQUEST = [];
		unset( $GLOBALS['current_screen'] );
		$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', false );
		AMP_Validation_Manager::$hook_source_stack   = [];
		AMP_Validation_Manager::$validation_results  = [];
		AMP_Validation_Manager::reset_validation_results();
		unset( $_REQUEST['post_type'] ); // phpcs:ignore
		parent::tearDown();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Validation_Manager::init()
	 */
	public function test_init() {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		AMP_Validation_Manager::init();

		$this->assertTrue( post_type_exists( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG ) );
		$this->assertTrue( taxonomy_exists( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ) );

		$this->assertEquals( 100, has_filter( 'map_meta_cap', self::TESTED_CLASS . '::map_meta_cap' ) );
		$this->assertEquals( 10, has_action( 'enqueue_block_editor_assets', self::TESTED_CLASS . '::enqueue_block_validation' ) );

		$this->assertEquals( 10, has_action( 'all_admin_notices', self::TESTED_CLASS . '::print_plugin_notice' ) );

		$this->assertEquals( 101, has_action( 'admin_bar_menu', [ self::TESTED_CLASS, 'add_admin_bar_menu_items' ] ) );

		$this->assertEquals( 10, has_action( 'wp', [ self::TESTED_CLASS, 'maybe_fail_validate_request' ] ) );
		$this->assertEquals( 10, has_action( 'wp', [ self::TESTED_CLASS, 'override_validation_error_statuses' ] ) );

		$this->assertEquals(
			10,
			has_filter(
				'option_' . AMP_Options_Manager::OPTION_NAME,
				[ self::TESTED_CLASS, 'filter_options_for_standard_mode_when_amp_first_override' ]
			)
		);
	}

	/** @return array */
	public function get_data_to_test_filter_options_for_standard_mode_when_amp_first_override() {
		$set_query_var = static function () {
			$_GET[ QueryVar::AMP_FIRST ] = '';
		};
		$set_admin_user = static function () {
			wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		};
		$set_validate_request = function () {
			$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', true );
		};

		$set_admin_dashboard = static function () {
			set_current_screen( 'index.php' );
		};

		$set_global_validated_url_post = static function ( $url ) {
			$GLOBALS['post'] = self::factory()->post->create_and_get(
				[
					'post_title' => $url,
					'post_type'  => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				]
			);
			setup_postdata( $GLOBALS['post'] );
		};

		$set_validated_url_post_list_screen = static function () {
			$GLOBALS['hook_suffix'] = 'edit.php';
			$_REQUEST['post_type'] = AMP_Validated_URL_Post_Type::POST_TYPE_SLUG;
			set_current_screen();
		};

		$set_validated_url_post_edit_screen = static function () {
			$GLOBALS['hook_suffix'] = 'post.php';
			$_REQUEST['post_type'] = AMP_Validated_URL_Post_Type::POST_TYPE_SLUG;
			set_current_screen();
		};

		return [
			'frontend_no_query_var'                    => [
				'set_up'          => static function () {},
				'expect_override' => false,
			],
			'frontend_query_var_not_allowed'           => [
				'set_up'          => $set_query_var,
				'expect_override' => false,
			],
			'frontend_query_var_with_admin_user'       => [
				'set_up'          => static function () use ( $set_query_var, $set_admin_user ) {
					$set_query_var();
					$set_admin_user();
				},
				'expect_override' => true,
			],
			'frontend_query_var_with_validate_request' => [
				'set_up'          => static function () use ( $set_query_var, $set_validate_request ) {
					$set_query_var();
					$set_validate_request();
				},
				'expect_override' => true,
			],
			'frontend_query_var_with_admin_user_and_validate_request' => [
				'set_up'          => static function () use ( $set_query_var, $set_admin_user, $set_validate_request ) {
					$set_query_var();
					$set_admin_user();
					$set_validate_request();
				},
				'expect_override' => true,
			],
			'frontend_no_query_var_with_admin_user_and_validate_request' => [
				'set_up'          => static function () use ( $set_query_var, $set_admin_user, $set_validate_request ) {
					$set_admin_user();
					$set_validate_request();
				},
				'expect_override' => false,
			],

			'admin_validation_request_for_new_non_override_url' => [
				'set_up'          => static function () use ( $set_admin_dashboard, $set_admin_user ) {
					$set_admin_user();
					$set_admin_dashboard();
					$_GET['action'] = AMP_Validation_Manager::VALIDATE_QUERY_VAR;
					$_GET['url']    = home_url();
				},
				'expect_override' => false,
			],

			'admin_validation_request_for_new_yes_override_url' => [
				'set_up'          => static function () use ( $set_admin_dashboard, $set_admin_user ) {
					$set_admin_user();
					$set_admin_dashboard();
					$_GET['action'] = AMP_Validation_Manager::VALIDATE_QUERY_VAR;
					$_GET['url']    = add_query_arg( QueryVar::AMP_FIRST, '', home_url( '/' ) );
				},
				'expect_override' => true,
			],

			'admin_validation_request_for_existing_non_override_url' => [
				'set_up'          => static function () use ( $set_admin_dashboard, $set_admin_user ) {
					$set_admin_user();
					$set_admin_dashboard();
					$_GET['action'] = AMP_Validation_Manager::VALIDATE_QUERY_VAR;
					$_GET['post']    = self::factory()->post->create(
						[
							'post_title' => home_url(),
							'post_type'  => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
						]
					);
				},
				'expect_override' => false,
			],

			'admin_validation_request_for_existing_yes_override_url' => [
				'set_up'          => static function () use ( $set_admin_dashboard, $set_admin_user ) {
					$set_admin_user();
					$set_admin_dashboard();
					$_GET['action'] = AMP_Validation_Manager::VALIDATE_QUERY_VAR;
					$_GET['post']    = self::factory()->post->create(
						[
							'post_title' => add_query_arg( QueryVar::AMP_FIRST, '', home_url( '/' ) ),
							'post_type'  => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
						]
					);
				},
				'expect_override' => true,
			],

			'admin_on_dashboard'                       => [
				'set_up'          => static function () use ( $set_admin_user, $set_admin_dashboard ) {
					$set_admin_user();
					$set_admin_dashboard();
				},
				'expect_override' => false,
			],
			'admin_on_post_list_screen_not_amp_override_url' => [
				'set_up'          => static function () use ( $set_admin_user, $set_global_validated_url_post, $set_validated_url_post_list_screen ) {
					$set_admin_user();
					$set_global_validated_url_post( home_url( '/' ) );
					$set_validated_url_post_list_screen();
				},
				'expect_override' => false,
			],
			'admin_on_post_list_screen_yes_amp_override_url' => [
				'set_up'          => static function () use ( $set_admin_user, $set_global_validated_url_post, $set_validated_url_post_list_screen ) {
					$set_admin_user();
					$set_global_validated_url_post( add_query_arg( QueryVar::AMP_FIRST, '', home_url( '/' ) ) );
					$set_validated_url_post_list_screen();
				},
				'expect_override' => true,
			],

			'admin_on_edit_post_screen_not_amp_override_url' => [
				'set_up'          => static function () use ( $set_admin_user, $set_global_validated_url_post, $set_validated_url_post_edit_screen ) {
					$set_admin_user();
					$set_global_validated_url_post( home_url( '/' ) );
					$set_validated_url_post_edit_screen();
				},
				'expect_override' => false,
			],

			'admin_on_edit_post_screen_not_amp_override_url' => [
				'set_up'          => static function () use ( $set_admin_user, $set_global_validated_url_post, $set_validated_url_post_edit_screen ) {
					$set_admin_user();
					$set_global_validated_url_post( add_query_arg( QueryVar::AMP_FIRST, '', home_url( '/' ) ) );
					$set_validated_url_post_edit_screen();
				},
				'expect_override' => true,
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_filter_options_for_standard_mode_when_amp_first_override
	 * @covers AMP_Validation_Manager::is_amp_first_override_request()
	 * @covers AMP_Validation_Manager::filter_options_for_standard_mode_when_amp_first_override()
	 * @covers AMP_Validation_Manager::is_amp_first_override_url()
	 */
	public function test_filter_options_for_standard_mode_when_amp_first_override( $set_up, $expect_override ) {
		$set_up();

		$options_with_reader   = [ Option::THEME_SUPPORT => AMP_Theme_Support::READER_MODE_SLUG ];
		$options_with_standard = [ Option::THEME_SUPPORT => AMP_Theme_Support::STANDARD_MODE_SLUG ];

		$this->assertEquals(
			$expect_override ? $options_with_standard : $options_with_reader,
			AMP_Validation_Manager::filter_options_for_standard_mode_when_amp_first_override( $options_with_reader )
		);
	}

	/**
	 * Test ::maybe_fail_validate_request().
	 *
	 * @covers AMP_Validation_Manager::maybe_fail_validate_request()
	 */
	public function test_maybe_fail_validate_request() {
		$post_id = self::factory()->post->create();

		remove_filter( 'wp', [ AMP_Validation_Manager::class, 'maybe_fail_validate_request' ] );
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			static function () {
				return static function () {};
			}
		);

		$get_output = static function () {
			ob_start();
			AMP_Validation_Manager::maybe_fail_validate_request();
			return ob_get_clean();
		};

		// Verify there is no output if it is not a validation request.
		$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', false );
		$this->assertEmpty( $get_output() );

		// Verify there is no output if it is an AMP request.
		$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', true );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertEmpty( $get_output() );

		// Verify correct response if not an AMP page.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$output = $get_output();
		$this->assertJson( $output );
		$this->assertStringContainsString( 'AMP_NOT_REQUESTED', $output );

		// Verify correct response if AMP not available.
		$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', true );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );

		add_filter(
			'amp_skip_post',
			static function ( $skipped, $_post_id ) use ( $post_id ) {
				return $_post_id === $post_id;
			},
			10,
			2
		);

		$this->go_to( amp_get_permalink( $post_id ) );
		$output = $get_output();
		$this->assertJson( $output );
		$this->assertStringContainsString( 'AMP_NOT_AVAILABLE', $output );
	}

	/** @covers AMP_Validation_Manager::is_validate_request() */
	public function test_is_validate_request() {
		$this->assertFalse( AMP_Validation_Manager::is_validate_request() );
		$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', true );
		$this->assertTrue( AMP_Validation_Manager::is_validate_request() );
		$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', false );
		$this->assertFalse( AMP_Validation_Manager::is_validate_request() );
	}

	/**
	 * Test init_validate_request without error.
	 *
	 * @covers AMP_Validation_Manager::init_validate_request()
	 */
	public function test_init_validate_request_without_error() {
		$this->assertFalse( AMP_Validation_Manager::should_validate_response() );
		AMP_Validation_Manager::init_validate_request();
		$this->assertFalse( AMP_Validation_Manager::is_validate_request() );

		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = wp_slash( AMP_Validation_Manager::get_amp_validate_nonce() ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->assertTrue( AMP_Validation_Manager::should_validate_response() );
		AMP_Validation_Manager::init_validate_request();
		$this->assertTrue( AMP_Validation_Manager::is_validate_request() );
	}

	/**
	 * Test init_validate_request without error.
	 *
	 * @covers AMP_Validation_Manager::init_validate_request()
	 */
	public function test_init_validate_request_with_error() {
		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = 'bad';
		$this->assertInstanceOf( 'WP_Error', AMP_Validation_Manager::should_validate_response() );
		add_filter( 'wp_doing_ajax', '__return_true' );
		ob_start();
		$died = false;
		add_filter(
			'wp_die_ajax_handler',
			function() use ( &$died ) {
				return function() use ( &$died ) {
					$died = true;
				};
			}
		);
		AMP_Validation_Manager::init_validate_request();
		ob_end_clean();
		$this->assertTrue( $died );
	}

	/**
	 * Test \AMP_Validation_Manager::post_supports_validation.
	 *
	 * @covers \AMP_Validation_Manager::post_supports_validation()
	 */
	public function test_post_supports_validation() {

		// Ensure that posts can be validated even when theme support is absent.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertTrue( AMP_Validation_Manager::post_supports_validation( self::factory()->post->create() ) );

		// Ensure normal case of validating published post when theme support present.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertTrue( AMP_Validation_Manager::post_supports_validation( self::factory()->post->create() ) );

		// Trashed posts are not validatable.
		$this->assertFalse( AMP_Validation_Manager::post_supports_validation( self::factory()->post->create( [ 'post_status' => 'trash' ] ) ) );

		// An invalid post is not previewable.
		$this->assertFalse( AMP_Validation_Manager::post_supports_validation( 0 ) );

		// Ensure non-viewable posts do not support validation.
		register_post_type( 'not_viewable', [ 'publicly_queryable' => false ] );
		$post = self::factory()->post->create( [ 'post_type' => 'not_viewable' ] );
		$this->assertFalse( AMP_Validation_Manager::post_supports_validation( $post ) );
	}

	/**
	 * Test add_validation_hooks.
	 *
	 * Excessive CSS validation errors have special case handling.
	 * @see https://github.com/ampproject/amp-wp/issues/2326
	 *
	 * @covers AMP_Validation_Manager::is_sanitization_auto_accepted()
	 */
	public function test_is_sanitization_auto_accepted() {
		$some_error = [
			'node_name'   => 'href',
			'parent_name' => 'a',
			'type'        => 'html_attribute_error',
			'code'        => AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_ATTR,
		];
		$excessive_css_error = [
			'node_name' => 'style',
			'type'      => 'css',
			'code'      => AMP_Style_Sanitizer::STYLESHEET_TOO_LONG,
		];

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->accept_sanitization_by_default( false );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted() );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted( $some_error ) );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted( $excessive_css_error ) );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->accept_sanitization_by_default( true );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_auto_accepted() );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_auto_accepted( $some_error ) );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_auto_accepted( $excessive_css_error ) );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->accept_sanitization_by_default( false );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted() );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted( $some_error ) );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted( $excessive_css_error ) );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->accept_sanitization_by_default( false );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted() );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted( $some_error ) );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_auto_accepted( $excessive_css_error ) );

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->accept_sanitization_by_default( true );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_auto_accepted() );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_auto_accepted( $some_error ) );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_auto_accepted( $excessive_css_error ) );
	}

	/**
	 * Test add_admin_bar_menu_items.
	 *
	 * @covers AMP_Validation_Manager::add_admin_bar_menu_items()
	 */
	public function test_add_admin_bar_menu_items() {
		$this->accept_sanitization_by_default( false );

		// No admin bar item when user lacks capability.
		$this->go_to( home_url( '/' ) );
		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		$this->assertFalse( is_admin() );
		$this->assertFalse( AMP_Validation_Manager::has_cap() );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$this->assertNull( $admin_bar->get_node( 'amp' ) );

		// No admin bar item when no template available.
		$this->go_to( home_url() );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertTrue( AMP_Validation_Manager::has_cap() );
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, false );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$this->assertNull( $admin_bar->get_node( 'amp' ) );
		remove_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( Option::ALL_TEMPLATES_SUPPORTED, true );

		// Admin bar item available in AMP-first mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$node = $admin_bar->get_node( 'amp' );
		$this->assertIsObject( $node );
		$this->assertStringContainsString( 'action=amp_validate', $node->href );
		$view_item = $admin_bar->get_node( 'amp-view' );
		$this->assertIsObject( $view_item );
		$this->assertEqualSets( [ QueryVar::NOAMP ], array_keys( $this->get_url_query_vars( $view_item->href ) ) );
		$this->assertIsObject( $admin_bar->get_node( 'amp-validity' ) );

		// Admin bar item available in paired mode.
		add_filter( 'amp_dev_mode_enabled', '__return_true' );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$node = $admin_bar->get_node( 'amp' );
		$this->assertIsObject( $node );

		/*
		 * Admin bar item available in transitional mode.
		 * Transitional mode is available once template_dir is supplied.
		 */
		add_theme_support( AMP_Theme_Support::SLUG, [ 'template_dir' => 'amp' ] );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$node = $admin_bar->get_node( 'amp' );
		$this->assertIsObject( $node );

		// Admin bar item available in paired mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$root_node = $admin_bar->get_node( 'amp' );
		$this->assertIsObject( $root_node );
		$this->assertEqualSets( [ QueryVar::AMP ], array_keys( $this->get_url_query_vars( $root_node->href ) ) );

		$view_item = $admin_bar->get_node( 'amp-view' );
		$this->assertIsObject( $view_item );
		$this->assertEqualSets( [ QueryVar::AMP ], array_keys( $this->get_url_query_vars( $view_item->href ) ) );
		$this->assertIsObject( $admin_bar->get_node( 'amp-validity' ) );

		// Lastly, confirm that the settings item is added if the user is an admin.
		wp_set_current_user( 0 );
		$admin_bar = new WP_Admin_Bar();
		$this->assertFalse( current_user_can( 'manage_options' ) );
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$this->assertNull( $admin_bar->get_node( 'amp-settings' ) );
		$admin_bar = new WP_Admin_Bar();
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertTrue( current_user_can( 'manage_options' ) );
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$this->assertObjectHasAttribute( 'href', $admin_bar->get_node( 'amp-settings' ) );
	}

	/**
	 * Get URL query vars.
	 *
	 * @param string $url URL.
	 * @return array Query vars.
	 */
	private function get_url_query_vars( $url ) {
		$query_string = wp_parse_url( $url, PHP_URL_QUERY );
		if ( empty( $query_string ) ) {
			return [];
		}
		$query_vars = [];
		parse_str( $query_string, $query_vars );
		return $query_vars;
	}

	/**
	 * Test overrides.
	 *
	 * @covers AMP_Validation_Manager::override_validation_error_statuses()
	 */
	public function test_override_validation_error_statuses_with_good_nonce() {
		$this->assertEmpty( AMP_Validation_Manager::$validation_error_status_overrides );
		$validation_error_term_1 = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( [ 'test' => 1 ] );
		$validation_error_term_2 = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( [ 'test' => 2 ] );
		$_REQUEST['preview']  = '1';
		$_REQUEST['_wpnonce'] = wp_create_nonce( AMP_Validation_Manager::MARKUP_STATUS_PREVIEW_ACTION );
		$_REQUEST[ AMP_Validated_URL_Post_Type::VALIDATION_ERRORS_INPUT_KEY ] = [
			$validation_error_term_1['slug'] => [
				AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
			],
			$validation_error_term_2['slug'] => [
				AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS,
			],
		];
		AMP_Validation_Manager::override_validation_error_statuses();
		$this->assertCount( 2, AMP_Validation_Manager::$validation_error_status_overrides );
	}

	/**
	 * Test validation error overrides for when bad nonce is supplied.
	 *
	 * @covers AMP_Validation_Manager::override_validation_error_statuses()
	 * @expectedException WPDieException
	 */
	public function test_override_validation_error_statuses_with_bad_nonce() {
		$validation_error_term_1 = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( [ 'test' => 1 ] );
		$_REQUEST['preview']  = '1';
		$_REQUEST['_wpnonce'] = 'bad';
		$_REQUEST[ AMP_Validated_URL_Post_Type::VALIDATION_ERRORS_INPUT_KEY ] = [
			$validation_error_term_1['slug'] => [
				AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
			],
		];
		AMP_Validation_Manager::override_validation_error_statuses();
	}

	/**
	 * Test validation error overrides for when no nonce is supplied.
	 *
	 * @covers AMP_Validation_Manager::override_validation_error_statuses()
	 * @expectedException WPDieException
	 */
	public function test_override_validation_error_statuses_with_no_nonce() {
		$validation_error_term_1 = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( [ 'test' => 1 ] );
		$_REQUEST['preview']     = '1';
		$_REQUEST[ AMP_Validated_URL_Post_Type::VALIDATION_ERRORS_INPUT_KEY ] = [
			$validation_error_term_1['slug'] => [
				AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
			],
		];
		AMP_Validation_Manager::override_validation_error_statuses();
	}

	/**
	 * Test add_validation_error_sourcing.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error_sourcing()
	 */
	public function test_add_validation_error_sourcing() {
		AMP_Validation_Manager::add_validation_error_sourcing();
		$this->assertEquals( 10, has_action( 'wp', [ self::TESTED_CLASS, 'wrap_widget_callbacks' ] ) );
		$this->assertEquals(
			defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX, // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound
			has_action( 'register_block_type_args', [ self::TESTED_CLASS, 'wrap_block_callbacks' ] )
		);
		$this->assertEquals( 10, has_action( 'all', [ self::TESTED_CLASS, 'wrap_hook_callbacks' ] ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'the_content', [ self::TESTED_CLASS, 'decorate_filter_source' ] ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'the_excerpt', [ self::TESTED_CLASS, 'decorate_filter_source' ] ) );
		$this->assertEquals( PHP_INT_MAX, has_action( 'do_shortcode_tag', [ self::TESTED_CLASS, 'decorate_shortcode_source' ] ) );
		$this->assertEquals( 8, has_action( 'the_content', [ self::TESTED_CLASS, 'add_block_source_comments' ] ) );
	}

	/**
	 * Test add_validation_error_sourcing with Gutenberg active.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error_sourcing()
	 */
	public function test_add_validation_error_sourcing_gutenberg() {
		if ( ! function_exists( 'do_blocks' ) ) {
			$this->markTestSkipped( 'Gutenberg not active.' );
		}
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Gutenberg for WP < 5.0 is not supported.' );
		}

		$priority = has_filter( 'the_content', 'do_blocks' );
		$this->assertNotFalse( $priority );
		AMP_Validation_Manager::add_validation_error_sourcing();
		$this->assertEquals( $priority - 1, has_filter( 'the_content', [ self::TESTED_CLASS, 'add_block_source_comments' ] ) );
	}

	/**
	 * Test map_meta_cap.
	 *
	 * @covers AMP_Validation_Manager::map_meta_cap()
	 */
	public function test_map_meta_cap() {
		$this->assertEquals(
			[ 'manage_options' ],
			AMP_Validation_Manager::map_meta_cap( [ AMP_Validation_Manager::VALIDATE_CAPABILITY ], AMP_Validation_Manager::VALIDATE_CAPABILITY )
		);

		$this->assertEquals(
			[ 'install_plugins', 'manage_options', 'update_core' ],
			AMP_Validation_Manager::map_meta_cap( [ 'install_plugins', AMP_Validation_Manager::VALIDATE_CAPABILITY, 'update_core' ], AMP_Validation_Manager::VALIDATE_CAPABILITY )
		);
	}

	/**
	 * Test has_cap.
	 *
	 * @covers AMP_Validation_Manager::has_cap()
	 */
	public function test_has_cap() {
		AMP_Validation_Manager::init();

		$subscriber    = self::factory()->user->create_and_get( [ 'role' => 'subscriber' ] );
		$editor        = self::factory()->user->create_and_get( [ 'role' => 'editor' ] );
		$administrator = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );

		wp_set_current_user( $subscriber->ID );
		$this->assertFalse( AMP_Validation_Manager::has_cap() );
		$this->assertFalse( AMP_Validation_Manager::has_cap( $subscriber ) );
		$this->assertFalse( AMP_Validation_Manager::has_cap( $subscriber->ID ) );
		$this->assertTrue( AMP_Validation_Manager::has_cap( $administrator ) );
		$this->assertTrue( AMP_Validation_Manager::has_cap( $administrator->ID ) );

		wp_set_current_user( $administrator->ID );
		$this->assertTrue( AMP_Validation_Manager::has_cap() );
		$this->assertFalse( AMP_Validation_Manager::has_cap( $subscriber ) );
		$this->assertFalse( AMP_Validation_Manager::has_cap( $subscriber->ID ) );
		$this->assertTrue( AMP_Validation_Manager::has_cap( $administrator ) );
		$this->assertTrue( AMP_Validation_Manager::has_cap( $administrator->ID ) );

		$this->assertFalse( AMP_Validation_Manager::has_cap( $editor ) );
		wp_set_current_user( $editor->ID );
		$this->assertFalse( AMP_Validation_Manager::has_cap() );
		add_filter(
			'map_meta_cap',
			static function ( $caps, $cap, $user_id ) {
				if ( AMP_Validation_Manager::VALIDATE_CAPABILITY === $cap && user_can( $user_id, 'edit_others_posts' ) ) {
					$position = array_search( $cap, $caps, true );
					if ( false !== $position ) {
						$caps[ $position ] = 'edit_others_posts';
					}
				}
				return $caps;
			},
			10,
			3
		);
		$this->assertTrue( AMP_Validation_Manager::has_cap() );
		$this->assertTrue( AMP_Validation_Manager::has_cap( $editor ) );
		$this->assertTrue( AMP_Validation_Manager::has_cap( $administrator ) );
		$this->assertFalse( AMP_Validation_Manager::has_cap( $subscriber ) );
	}

	/**
	 * Test add_validation_error.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error()
	 */
	public function test_add_validation_error_track_removed() {
		$this->set_private_property( AMP_Validation_Manager::class, 'is_validate_request', true );
		$this->assertEmpty( AMP_Validation_Manager::$validation_results );

		$that = $this;
		$node = $this->node;
		add_filter(
			'amp_validation_error',
			static function( $error, $context ) use ( $node, $that ) {
				$error['filtered'] = true;
				$that->assertEquals( AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG, $error['code'] );
				$that->assertSame( $node, $context['node'] );
				return $error;
			},
			10,
			2
		);

		AMP_Validation_Manager::add_validation_error(
			[
				'node_name'       => $this->node->nodeName,
				'code'            => AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG,
				'node_attributes' => [],
			],
			[
				'node' => $this->node,
			]
		);

		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals(
			[
				'node_name'       => 'img',
				'sources'         => [],
				'code'            => AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG,
				'node_attributes' => [],
				'filtered'        => true,
			],
			AMP_Validation_Manager::$validation_results[0]['error']
		);
	}

	/**
	 * Test was_node_removed.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error()
	 */
	public function test_add_validation_error_was_node_removed() {
		$this->assertEmpty( AMP_Validation_Manager::$validation_results );
		AMP_Validation_Manager::add_validation_error(
			[
				'node' => $this->node,
			]
		);
		$this->assertNotEmpty( AMP_Validation_Manager::$validation_results );
	}

	/**
	 * Test reset_validation_results.
	 *
	 * @covers AMP_Validation_Manager::reset_validation_results()
	 */
	public function test_reset_validation_results() {
		AMP_Validation_Manager::add_validation_error(
			[
				'code' => 'test',
			]
		);
		AMP_Validation_Manager::reset_validation_results();
		$this->assertEquals( [], AMP_Validation_Manager::$validation_results );
	}

	/**
	 * Test source comments.
	 *
	 * @covers AMP_Validation_Manager::locate_sources()
	 * @covers AMP_Validation_Manager::parse_source_comment()
	 * @covers AMP_Validation_Manager::get_source_comment()
	 */
	public function test_source_comments() {
		$source1 = [
			'type'      => 'plugin',
			'name'      => 'foo',
			'shortcode' => 'test',
			'function'  => __FUNCTION__,
		];
		$source2 = [
			'type'     => 'theme',
			'name'     => 'bar',
			'function' => __FUNCTION__,
			'hook'     => 'something',
		];

		$dom = AMP_DOM_Utils::get_dom_from_content(
			implode(
				'',
				[
					AMP_Validation_Manager::get_source_comment( $source1, true ),
					AMP_Validation_Manager::get_source_comment( $source2, true ),
					'<b id="test">Test</b>',
					AMP_Validation_Manager::get_source_comment( $source2, false ),
					AMP_Validation_Manager::get_source_comment( $source1, false ),
				]
			)
		);

		/**
		 * Comments.
		 *
		 * @var DOMComment[] $comments
		 */
		$comments = [];
		foreach ( $dom->xpath->query( '//comment()' ) as $comment ) {
			$comments[] = $comment;
		}
		$this->assertCount( 4, $comments );

		$sources = AMP_Validation_Manager::locate_sources( $dom->getElementById( 'test' ) );
		$this->assertIsArray( $sources );
		$this->assertCount( 2, $sources );

		$this->assertEquals( $source1, $sources[0] );
		$parsed_comment = AMP_Validation_Manager::parse_source_comment( $comments[0] );
		$this->assertEquals( $source1, $parsed_comment['source'] );
		$this->assertFalse( $parsed_comment['closing'] );
		$parsed_comment = AMP_Validation_Manager::parse_source_comment( $comments[3] );
		$this->assertEquals( $source1, $parsed_comment['source'] );
		$this->assertTrue( $parsed_comment['closing'] );

		$this->assertEquals( $source2, $sources[1] );
		$parsed_comment = AMP_Validation_Manager::parse_source_comment( $comments[1] );
		$this->assertEquals( $source2, $parsed_comment['source'] );
		$this->assertFalse( $parsed_comment['closing'] );
		$parsed_comment = AMP_Validation_Manager::parse_source_comment( $comments[2] );
		$this->assertEquals( $source2, $parsed_comment['source'] );
		$this->assertTrue( $parsed_comment['closing'] );
	}

	/**
	 * Get data for testing locate_sources.
	 *
	 * @return array
	 */
	public function get_locate_sources_data() {
		return [
			'directly_enqueued_link'                     => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_style(
								'foo',
								'https://example.com/foo.css',
								[],
								'0.1'
							);
						}
					);
				},
				'//link[ contains( @href, "foo.css" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'            => 'plugin',
							'name'            => 'amp',
							'function'        => '{closure}',
							'hook'            => 'wp_enqueue_scripts',
							'priority'        => 10,
							'dependency_type' => 'style',
							'handle'          => 'foo',
						],
						$sources[0]
					);
				},
			],

			'stylesheet_added_as_dependency'             => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_style(
								'bar',
								'https://example.com/bar.css',
								[ 'wp-codemirror' ],
								'0.1'
							);
						}
					);
				},
				'//link[ contains( @href, "codemirror" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'              => 'plugin',
							'name'              => 'amp',
							'function'          => '{closure}',
							'hook'              => 'wp_enqueue_scripts',
							'priority'          => 10,
							'dependency_type'   => 'style',
							'handle'            => 'bar',
							'dependency_handle' => 'wp-codemirror',
						],
						$sources[0]
					);
				},
			],

			'inline_style_for_directly_enqueued_stylesheet' => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_style(
								'baz',
								'https://example.com/baz.css',
								[],
								'0.1'
							);
							wp_add_inline_style( 'baz', '/*Hello Baz!*/' );
						}
					);
				},
				'//style[ contains( text(), "Hello Baz" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'            => 'plugin',
							'name'            => 'amp',
							'function'        => '{closure}',
							'hook'            => 'wp_enqueue_scripts',
							'priority'        => 10,
							'dependency_type' => 'style',
							'extra_key'       => 'after',
							'text'            => '/*Hello Baz!*/',
							'handle'          => 'baz',
						],
						$sources[1]
					);
				},
			],

			'external_script_directly_enqueued'          => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_script(
								'foo',
								'https://example.com/foo.js',
								[],
								'0.1',
								true
							);
						}
					);
				},
				'//script[ contains( @src, "foo.js" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'            => 'plugin',
							'name'            => 'amp',
							'function'        => '{closure}',
							'hook'            => 'wp_enqueue_scripts',
							'priority'        => 10,
							'dependency_type' => 'script',
							'handle'          => 'foo',
						],
						$sources[0]
					);
				},
			],

			'external_script_indirectly_enqueued'        => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_script(
								'bar',
								'https://example.com/bar.js',
								[ 'wp-codemirror' ],
								'0.1',
								true
							);
						}
					);
				},
				'//script[ contains( @src, "codemirror" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'              => 'plugin',
							'name'              => 'amp',
							'function'          => '{closure}',
							'hook'              => 'wp_enqueue_scripts',
							'priority'          => 10,
							'dependency_type'   => 'script',
							'handle'            => 'bar',
							'dependency_handle' => 'wp-codemirror',
						],
						$sources[0]
					);
				},
			],

			'inline_script_added_via_wp_localize_script' => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_script(
								'baz',
								'https://example.com/baz.js',
								[],
								'0.1',
								true
							);
							wp_localize_script( 'baz', 'Baz', [ 'greeting' => 'Hello Baz!' ] );
						}
					);
				},
				'//script[ contains( text(), "Hello Baz!" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'            => 'plugin',
							'name'            => 'amp',
							'function'        => '{closure}',
							'hook'            => 'wp_enqueue_scripts',
							'priority'        => 10,
							'dependency_type' => 'script',
							'extra_key'       => 'data',
							'text'            => 'var Baz = {"greeting":"Hello Baz!"};',
							'handle'          => 'baz',
						],
						$sources[2]
					);
				},
			],

			'inline_script_added_via_add_inline_script_before' => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_script(
								'baz',
								'https://example.com/baz.js',
								[],
								'0.1',
								true
							);
							wp_add_inline_script( 'baz', '/*Hello before Baz!*/', 'before' );
						}
					);
				},
				'//script[ contains( text(), "Hello before Baz!" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'            => 'plugin',
							'name'            => 'amp',
							'function'        => '{closure}',
							'hook'            => 'wp_enqueue_scripts',
							'priority'        => 10,
							'dependency_type' => 'script',
							'extra_key'       => 'before',
							'text'            => '/*Hello before Baz!*/',
							'handle'          => 'baz',
						],
						$sources[2]
					);
				},
			],

			'inline_script_added_via_add_inline_script_after' => [
				static function () {
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_script(
								'baz',
								'https://example.com/baz.js',
								[],
								'0.1',
								true
							);
							wp_add_inline_script( 'baz', '/*Hello after Baz!*/', 'after' );
						}
					);
				},
				'//script[ contains( text(), "Hello after Baz!" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'            => 'plugin',
							'name'            => 'amp',
							'function'        => '{closure}',
							'hook'            => 'wp_enqueue_scripts',
							'priority'        => 10,
							'dependency_type' => 'script',
							'extra_key'       => 'after',
							'text'            => '/*Hello after Baz!*/',
							'handle'          => 'baz',
						],
						$sources[2]
					);
				},
			],

			'style_enqueued_at_wp_default_styles'        => [
				static function () {
					add_action(
						'wp_default_styles',
						static function ( WP_Styles $styles ) {
							$styles->add(
								'foo',
								'https://example.com/foo.css',
								[],
								'0.1'
							);
						}
					);
					add_action(
						'wp_enqueue_scripts',
						static function () {
							wp_enqueue_style( 'foo' );
						}
					);
				},
				'//link[ contains( @href, "foo.css" ) ]',
				function ( $sources ) {
					$this->assertArraySubset(
						[
							'type'            => 'plugin',
							'name'            => 'amp',
							'function'        => '{closure}',
							'hook'            => 'wp_default_styles',
							'priority'        => 10,
							'dependency_type' => 'style',
							'handle'          => 'foo',
						],
						$sources[0]
					);
				},
			],
		];
	}

	/**
	 * Test locate sources.
	 *
	 * @dataProvider get_locate_sources_data
	 * @covers AMP_Validation_Manager::locate_sources()
	 * @covers AMP_Validation_Callback_Wrapper
	 *
	 * @param callable $callback Callback set up (add actions).
	 * @param string   $xpath    Expression to find the target element to get sources for.
	 * @param callable $assert   Function to assert the expected sources.
	 */
	public function test_locate_sources_e2e( $callback, $xpath, $assert ) {
		// @todo Remove once https://github.com/WordPress/gutenberg/pull/23104 is in a release.
		// Temporarily fixes an issue with PHP errors being thrown in Gutenberg v8.3.0 on PHP 7.4.
		$theme_features = [
			'editor-color-palette',
			'editor-gradient-presets',
			'editor-font-sizes',
		];
		foreach ( $theme_features as $theme_feature ) {
			if ( ! current_theme_supports( $theme_feature ) ) {
				add_theme_support( $theme_feature, [] );
			}
		}

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		AMP_Validation_Manager::add_validation_error_sourcing();
		$callback();
		$this->set_private_property( AMP_Theme_Support::class, 'is_output_buffering', true );
		$this->go_to( home_url() );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<?php wp_head(); ?>
			</head>
			<body>
				<?php wp_footer(); ?>
			</body>
		</html>
		<?php
		$html = ob_get_clean();

		$dom = Document::fromHtml( $html, Options::DEFAULTS );

		$element = $dom->xpath->query( $xpath )->item( 0 );
		$this->assertInstanceOf( 'DOMElement', $element );
		$sources = AMP_Validation_Manager::locate_sources( $element );
		$this->assertNotEmpty( $sources );
		$assert( $sources );
	}

	/**
	 * Get block data.
	 *
	 * @see Test_AMP_Validation_Utils::test_add_block_source_comments()
	 * @return array
	 */
	public function get_block_data() {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			$this->markTestSkipped( 'Gutenberg not active.' );
		}
		$latest_posts_block = WP_Block_Type_Registry::get_instance()->get_registered( 'core/latest-posts' );

		$reflection_function = new ReflectionFunction( $latest_posts_block->render_callback );
		$is_gutenberg = false !== strpos( $reflection_function->getFileName(), 'gutenberg' );

		return [
			'paragraph'    => [
				"<!-- wp:paragraph -->\n<p>Latest posts:</p>\n<!-- /wp:paragraph -->",
				"<!--amp-source-stack {\"block_name\":\"core\/paragraph\",\"post_id\":{{post_id}},\"block_content_index\":0}-->\n<p>Latest posts:</p>\n<!--/amp-source-stack {\"block_name\":\"core\/paragraph\",\"post_id\":{{post_id}}}-->",
				[
					'element' => 'p',
					'blocks'  => [ 'core/paragraph' ],
				],
			],
			'latest_posts' => [
				'<!-- wp:latest-posts {"postsToShow":1} /-->',
				sprintf(
					'<!--amp-source-stack {"block_name":"core\/latest-posts","post_id":{{post_id}},"block_content_index":0,"block_attrs":{"postsToShow":1},"type":"%1$s","name":"%2$s","file":%4$s,"line":%5$s,"function":"%3$s"}--><ul class="%6$s"><li><a href="{{url}}">{{title}}</a></li></ul><!--/amp-source-stack {"block_name":"core\/latest-posts","post_id":{{post_id}},"block_attrs":{"postsToShow":1},"type":"%1$s","name":"%2$s","file":%4$s,"line":%5$s,"function":"%3$s"}-->',
					$is_gutenberg ? 'plugin' : 'core',
					$is_gutenberg ? 'gutenberg' : 'wp-includes',
					$latest_posts_block->render_callback instanceof Closure ? '{closure}' : $latest_posts_block->render_callback,
					wp_json_encode(
						$is_gutenberg
						? preg_replace( ':.*gutenberg/:', '', $reflection_function->getFileName() )
						: preg_replace( ':.*wp-includes/:', '', $reflection_function->getFileName() )
					),
					$reflection_function->getStartLine(),
					( defined( 'GUTENBERG_DEVELOPMENT_MODE' ) || defined( 'GUTENBERG_VERSION' ) && GUTENBERG_VERSION && version_compare( GUTENBERG_VERSION, '8.8.0', '>=' ) )
						? 'wp-block-latest-posts__list wp-block-latest-posts'
						: 'wp-block-latest-posts wp-block-latest-posts__list'
				),
				[
					'element' => 'ul',
					'blocks'  => [ 'core/latest-posts' ],
				],
			],
			'columns'      => [
				"<!-- wp:columns -->\n<div class=\"wp-block-columns has-2-columns\">\n    <!-- wp:quote {\"layout\":\"column-1\",\"foo\":{\"bar\":1}} -->\n    <blockquote class=\"wp-block-quote layout-column-1\">\n        <p>A quotation!</p><cite>Famous</cite></blockquote>\n    <!-- /wp:quote -->\n\n    <!-- wp:html {\"layout\":\"column-2\"} -->\n    <div class=\"layout-column-2\">\n        <script>\n            document.write('Not allowed!');\n        </script>\n    </div>\n    <!-- /wp:html -->\n</div>\n<!-- /wp:columns -->",
				"<!--amp-source-stack {\"block_name\":\"core\/columns\",\"post_id\":{{post_id}},\"block_content_index\":0}-->\n<div class=\"wp-block-columns has-2-columns\">\n\n\n\n<!--amp-source-stack {\"block_name\":\"core\/quote\",\"post_id\":{{post_id}},\"block_content_index\":1,\"block_attrs\":{\"layout\":\"column-1\",\"foo\":{\"bar\":1}}}-->\n    <blockquote class=\"wp-block-quote layout-column-1\">\n        <p>A quotation!</p><cite>Famous</cite></blockquote>\n    <!--/amp-source-stack {\"block_name\":\"core\/quote\",\"post_id\":{{post_id}}}--><!--amp-source-stack {\"block_name\":\"core\/html\",\"post_id\":{{post_id}},\"block_content_index\":2,\"block_attrs\":{\"layout\":\"column-2\"}}-->\n    <div class=\"layout-column-2\">\n        <script>\n            document.write('Not allowed!');\n        </script>\n    </div>\n    <!--/amp-source-stack {\"block_name\":\"core\/html\",\"post_id\":{{post_id}}}--></div>\n<!--/amp-source-stack {\"block_name\":\"core\/columns\",\"post_id\":{{post_id}}}-->",
				[
					'element' => 'blockquote',
					'blocks'  => [
						'core/columns',
						'core/quote',
					],
				],
			],
		];
	}

	/**
	 * Test add_block_source_comments.
	 *
	 * @param string $content  Content.
	 * @param string $expected Expected content.
	 * @param array  $query    Query.
	 *
	 * @dataProvider get_block_data
	 * @covers       AMP_Validation_Manager::add_block_source_comments()
	 * @covers       AMP_Validation_Manager::handle_block_source_comment_replacement()
	 */
	public function test_add_block_source_comments( $content, $expected, $query ) {
		if ( ! function_exists( 'do_blocks' ) ) {
			$this->markTestSkipped( 'Gutenberg not active.' );
		}
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Gutenberg for WP < 5.0 is not supported.' );
		}

		global $post;
		$post = self::factory()->post->create_and_get();
		$this->assertInstanceOf( 'WP_Post', get_post() );

		$rendered_block = do_blocks( AMP_Validation_Manager::add_block_source_comments( $content ) );

		$expected = str_replace(
			[
				'{{post_id}}',
				'{{title}}',
				'{{url}}',
			],
			[
				$post->ID,
				get_the_title( $post ),
				get_permalink( $post ),
			],
			$expected
		);

		$this->assertEquals(
			preg_replace( '/(?<=>)\s+(?=<)/', '', str_replace( '%d', $post->ID, $expected ) ),
			preg_replace( '/(?<=>)\s+(?=<)/', '', $rendered_block )
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( $rendered_block );
		$el  = $dom->getElementsByTagName( $query['element'] )->item( 0 );

		$this->assertEquals(
			$query['blocks'],
			wp_list_pluck( AMP_Validation_Manager::locate_sources( $el ), 'block_name' )
		);
	}

	/**
	 * Test wrap_widget_callbacks.
	 *
	 * @group widgets
	 * @covers AMP_Validation_Manager::wrap_widget_callbacks()
	 */
	public function test_wrap_widget_callbacks() {
		global $wp_registered_widgets, $_wp_sidebars_widgets, $wp_widget_factory;
		$this->go_to( amp_get_permalink( self::factory()->post->create() ) );

		update_option(
			'widget_search',
			[
				2               => [
					'title' => '',
				],
				'_multi_widget' => 1,
			]
		);
		update_option(
			'widget_archives',
			[
				2               => [
					'title' => '',
				],
				'_multi_widget' => 1,
			]
		);

		$wp_registered_widgets = [];
		$wp_widget_factory     = new WP_Widget_Factory();
		wp_widgets_init();

		$search_widget_id = 'search-2';
		$this->assertArrayHasKey( $search_widget_id, $wp_registered_widgets );
		$this->assertIsArray( $wp_registered_widgets[ $search_widget_id ]['callback'] );
		$this->assertInstanceOf( 'WP_Widget_Search', $wp_registered_widgets[ $search_widget_id ]['callback'][0] );
		$this->assertSame( 'display_callback', $wp_registered_widgets[ $search_widget_id ]['callback'][1] );
		$archives_widget_id = 'archives-2';
		$this->assertArrayHasKey( $archives_widget_id, $wp_registered_widgets );
		$this->assertIsArray( $wp_registered_widgets[ $archives_widget_id ]['callback'] );

		AMP_Validation_Manager::wrap_widget_callbacks();
		$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $wp_registered_widgets[ $search_widget_id ]['callback'] );
		$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $wp_registered_widgets[ $archives_widget_id ]['callback'] );
		$this->assertInstanceOf( 'WP_Widget', $wp_registered_widgets[ $search_widget_id ]['callback'][0] );
		$this->assertInstanceOf( 'WP_Widget', $wp_registered_widgets[ $archives_widget_id ]['callback'][0] );
		$this->assertSame( 'display_callback', $wp_registered_widgets[ $search_widget_id ]['callback'][1] );
		$this->assertSame( 'display_callback', $wp_registered_widgets[ $archives_widget_id ]['callback'][1] );

		$sidebar_id = 'amp-sidebar';
		register_sidebar(
			[
				'id'           => $sidebar_id,
				'after_widget' => '</li>',
			]
		);

		// Test core search widget.
		$_wp_sidebars_widgets[ $sidebar_id ] = [ $search_widget_id ];
		AMP_Theme_Support::start_output_buffering();
		dynamic_sidebar( $sidebar_id );
		$output     = ob_get_clean();
		$reflection = new ReflectionMethod( 'WP_Widget_Search', 'widget' );
		$this->assertStringStartsWith(
			sprintf(
				'<!--amp-source-stack {"type":"core","name":"wp-includes","file":%1$s,"line":%2$d,"function":%3$s,"widget_id":%4$s}--><li id=%4$s',
				wp_json_encode( preg_replace( ':^.*wp-includes/:', '', $reflection->getFileName() ) ),
				$reflection->getStartLine(),
				wp_json_encode( $reflection->getDeclaringClass()->getName() . '::' . $reflection->getName() ),
				wp_json_encode( $search_widget_id )
			),
			$output
		);
		$this->assertMatchesRegularExpression(
			'#</li><!--/amp-source-stack {.*$#s',
			$output
		);

		// Test plugin-extended archives widget.
		$_wp_sidebars_widgets[ $sidebar_id ] = [ $archives_widget_id ];
		AMP_Theme_Support::start_output_buffering();
		dynamic_sidebar( $sidebar_id );
		$output     = ob_get_clean();
		$reflection = new ReflectionMethod( 'WP_Widget_Archives', 'widget' );
		$this->assertStringStartsWith(
			sprintf(
				'<!--amp-source-stack {"type":"core","name":"wp-includes","file":%1$s,"line":%2$d,"function":%3$s,"widget_id":%4$s}--><li id=%4$s',
				wp_json_encode( 'widgets/' . basename( $reflection->getFileName() ) ),
				$reflection->getStartLine(),
				wp_json_encode( $reflection->getDeclaringClass()->getName() . '::' . $reflection->getName() ),
				wp_json_encode( $archives_widget_id )
			),
			$output
		);
	}

	/**
	 * Test wrap_hook_callbacks.
	 *
	 * @covers AMP_Validation_Manager::wrap_hook_callbacks()
	 */
	public function test_callback_wrappers() {
		global $post;
		$that = $this;
		$post = self::factory()->post->create_and_get();
		$this->set_capability();
		$action_no_tag_output     = 'foo_action';
		$action_core_output       = 'core_action';
		$action_no_output         = 'bar_action_no_output';
		$action_function_callback = 'baz_action_function';
		$action_no_argument       = 'test_action_no_argument';
		$action_one_argument      = 'baz_action_one_argument';
		$action_two_arguments     = 'example_action_two_arguments';
		$notice                   = 'Example notice';

		AMP_Validation_Manager::add_validation_error_sourcing();

		add_action( $action_function_callback, '_amp_show_load_errors_admin_notice' );
		add_action( $action_no_argument, [ $this, 'output_div' ] );
		add_action( $action_one_argument, [ $this, 'output_notice' ] );
		add_action( $action_two_arguments, [ $this, 'output_message' ], 10, 2 );
		add_action( $action_no_output, [ $this, 'get_string' ], 10, 2 );
		add_action( $action_no_tag_output, 'the_ID' );
		add_action( $action_core_output, 'edit_post_link' );
		add_action( $action_no_output, '__return_false' );

		// All of the callback functions remain as-is. They will only change for a given hook at the 'all' action.
		$this->assertEquals( 10, has_action( $action_no_tag_output, 'the_ID' ) );
		$this->assertEquals( 10, has_action( $action_no_output, [ $this, 'get_string' ] ) );
		$this->assertEquals( 10, has_action( $action_no_argument, [ $this, 'output_div' ] ) );
		$this->assertEquals( 10, has_action( $action_one_argument, [ $this, 'output_notice' ] ) );
		$this->assertEquals( 10, has_action( $action_two_arguments, [ $this, 'output_message' ] ) );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_function_callback );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div class="notice notice-error">', $output );
		$this->assertStringContainsString( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertStringContainsString( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_argument );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<div></div>', $output );
		$this->assertStringContainsString( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertStringContainsString( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_one_argument, $notice );
		$output = ob_get_clean();
		$this->assertStringContainsString( $notice, $output );
		$this->assertStringContainsString( sprintf( '<div class="notice notice-warning"><p>%s</p></div>', $notice ), $output );
		$this->assertStringContainsString( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertStringContainsString( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_two_arguments, $notice, get_the_ID() );
		$output = ob_get_clean();
		AMP_Theme_Support::start_output_buffering();
		self::output_message( $notice, get_the_ID() );
		$expected_output = ob_get_clean();
		$this->assertStringContainsString( $expected_output, $output );
		$this->assertStringContainsString( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertStringContainsString( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		// This action's callback doesn't output any HTML tags, so no HTML should be present.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_tag_output );
		$output = ob_get_clean();
		$this->assertStringNotContainsString( '<!--amp-source-stack ', $output );
		$this->assertStringNotContainsString( '<!--/amp-source-stack ', $output );

		// This action's callback comes from core.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_core_output );
		$output = ob_get_clean();
		$this->assertStringContainsString( '<!--amp-source-stack {"type":"core","name":"wp-includes"', $output );
		$this->assertStringContainsString( '<!--/amp-source-stack {"type":"core","name":"wp-includes"', $output );

		// This action's callback doesn't echo any markup, so it shouldn't be wrapped in comments.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_output );
		$output = ob_get_clean();
		$this->assertStringNotContainsString( '<!--amp-source-stack ', $output );
		$this->assertStringNotContainsString( '<!--/amp-source-stack ', $output );

		$handle_inner_action = null;
		$handle_outer_action = null;

		// Ensure that nested actions output the expected stack, and that has_action() works as expected in spite of the function wrapping.
		$handle_outer_action = static function() use ( $that, &$handle_outer_action, &$handle_inner_action ) {
			$that->assertEquals( 10, has_action( 'outer_action', $handle_outer_action ) );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
			do_action( 'inner_action' );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
		};
		$outer_reflection    = new ReflectionFunction( $handle_outer_action );
		$handle_inner_action = static function() use ( $that, &$handle_outer_action, &$handle_inner_action ) {
			$that->assertEquals( 10, has_action( 'outer_action', $handle_outer_action ) );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
			echo '<b>Hello</b>';
		};
		$inner_reflection    = new ReflectionFunction( $handle_inner_action );
		add_action( 'outer_action', $handle_outer_action );
		add_action( 'inner_action', $handle_inner_action );
		AMP_Theme_Support::start_output_buffering();
		do_action( 'outer_action' );
		$output = ob_get_clean();
		$this->assertEquals(
			implode(
				'',
				[
					sprintf( '<!--amp-source-stack {"type":"plugin","name":"amp","file":"tests\/php\/validation\/test-class-amp-validation-manager.php","line":%d,"function":"{closure}","hook":"outer_action","priority":10}-->', $outer_reflection->getStartLine() ),
					sprintf( '<!--amp-source-stack {"type":"plugin","name":"amp","file":"tests\/php\/validation\/test-class-amp-validation-manager.php","line":%d,"function":"{closure}","hook":"inner_action","priority":10}-->', $inner_reflection->getStartLine() ),
					'<b>Hello</b>',
					sprintf( '<!--/amp-source-stack {"type":"plugin","name":"amp","file":"tests\/php\/validation\/test-class-amp-validation-manager.php","line":%d,"function":"{closure}","hook":"inner_action","priority":10}-->', $inner_reflection->getStartLine() ),
					sprintf( '<!--/amp-source-stack {"type":"plugin","name":"amp","file":"tests\/php\/validation\/test-class-amp-validation-manager.php","line":%d,"function":"{closure}","hook":"outer_action","priority":10}-->', $outer_reflection->getStartLine() ),
				]
			),
			$output
		);
	}

	/**
	 * @covers ::wrap_block_callbacks()
	 * @covers AMP_Validation_Callback_Wrapper::get_callback_function()
	 */
	public function test_wrap_block_callbacks() {

		$with_no_render_callback = [
			'name' => 'test/no_render_callback',
		];
		$this->assertSame(
			$with_no_render_callback,
			AMP_Validation_Manager::wrap_block_callbacks( $with_no_render_callback )
		);

		$with_non_existent_render_callback = [
			'name'            => 'test/non_existent_render_callback',
			'render_callback' => 'this_does_not_exist',
		];
		$this->assertSame(
			$with_non_existent_render_callback,
			AMP_Validation_Manager::wrap_block_callbacks( $with_non_existent_render_callback )
		);

		$original_render_callback = static function () {
			return '<span>Render callback</span>';
		};
		$with_unwrapped_render_callback = [
			'name'            => 'test/with_unwrapped_render_callback',
			'render_callback' => $original_render_callback,
		];
		$output = AMP_Validation_Manager::wrap_block_callbacks( $with_unwrapped_render_callback );
		$render_callback = $output['render_callback'];
		$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $render_callback );
		$this->assertSame(
			$original_render_callback,
			$render_callback->get_callback_function()
		);
	}

	/**
	 * Test has_parameters_passed_by_reference.
	 *
	 * @covers AMP_Validation_Manager::has_parameters_passed_by_reference()
	 */
	public function test_has_parameters_passed_by_reference() {
		$tested_method = new ReflectionMethod( AMP_Validation_Manager::class, 'has_parameters_passed_by_reference' );
		$tested_method->setAccessible( true );
		$reflection_by_value          = new ReflectionFunction( 'get_bloginfo' );
		$reflection_by_ref_first_arg  = new ReflectionFunction( 'wp_handle_upload' );
		$reflection_by_ref_second_arg = new ReflectionFunction( 'wp_parse_str' );

		$this->assertEquals( 0, $tested_method->invoke( null, $reflection_by_value ) );
		$this->assertEquals( 1, $tested_method->invoke( null, $reflection_by_ref_first_arg ) );
		$this->assertEquals( 2, $tested_method->invoke( null, $reflection_by_ref_second_arg ) );
	}

	/**
	 * Test decorate_shortcode_source.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error_sourcing()
	 * @covers AMP_Validation_Manager::decorate_shortcode_source()
	 * @covers AMP_Validation_Manager::decorate_filter_source()
	 * @throws Exception If assertion fails.
	 */
	public function test_decorate_shortcode_and_filter_source() {
		global $post;
		if ( empty( $post ) ) {
			$post = self::factory()->post->create_and_get();
		}

		AMP_Validation_Manager::add_validation_error_sourcing();
		$shortcode_fallback = static function() {
			return '<b>test</b>';
		};
		add_shortcode(
			'test',
			$shortcode_fallback
		);

		$filtered_content = apply_filters( 'the_content', 'before[test]after' );

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '>=' ) && has_filter( 'the_content', 'do_blocks' ) ) {
			$sources = [
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'WP_Embed::run_shortcode',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'WP_Embed::autoembed',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'do_blocks',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wptexturize',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wpautop',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'shortcode_unautop',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'prepend_attachment',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => version_compare( get_bloginfo( 'version' ), '5.5-alpha', '>' ) ? 'wp_filter_content_tags' : 'wp_make_content_images_responsive',
				],
			];
		} elseif ( has_filter( 'the_content', 'do_blocks' ) ) {
			$sources = [
				[
					'type'     => 'plugin',
					'name'     => 'gutenberg',
					'function' => 'do_blocks',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'WP_Embed::run_shortcode',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'WP_Embed::autoembed',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wptexturize',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wpautop',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'shortcode_unautop',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'prepend_attachment',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wp_make_content_images_responsive',
				],
			];
		} else {
			$sources = [
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'WP_Embed::run_shortcode',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'WP_Embed::autoembed',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wptexturize',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wpautop',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'shortcode_unautop',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'prepend_attachment',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'wp_make_content_images_responsive',
				],
			];
		}

		if ( function_exists( 'wp_replace_insecure_home_url' ) ) {
			$sources[] = [
				'type'     => 'core',
				'name'     => 'wp-includes',
				'function' => 'wp_replace_insecure_home_url',
			];
		}

		$sources = array_merge(
			$sources,
			[
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'capital_P_dangit',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'do_shortcode',
				],
				[
					'type'     => 'core',
					'name'     => 'wp-includes',
					'function' => 'convert_smilies',
				],
			]
		);

		foreach ( $sources as &$source ) {
			$function = $source['function'];
			unset( $source['function'] );
			if ( strpos( $function, '::' ) ) {
				$method     = explode( '::', $function, 2 );
				$reflection = new ReflectionMethod( $method[0], $method[1] );
			} else {
				$reflection = new ReflectionFunction( $function );
			}

			if ( 'core' === $source['type'] ) {
				$source['file'] = preg_replace( ':.*/' . preg_quote( $source['name'], ':' ) . '/:', '', $reflection->getFileName() );
			} elseif ( 'plugin' === $source['type'] ) {
				$source['file'] = preg_replace( ':.*/' . preg_quote( basename( WP_PLUGIN_DIR ), ':' ) . '/[^/]+?/:', '', $reflection->getFileName() );
			} else {
				throw new Exception( 'Unexpected type: ' . $source['type'] );
			}
			$source['line']     = $reflection->getStartLine();
			$source['function'] = $function;
		}

		$source_json = wp_json_encode(
			[
				'hook'      => 'the_content',
				'filter'    => true,
				'post_id'   => get_the_ID(),
				'post_type' => get_post_type(),
				'sources'   => $sources,
			]
		);

		$shortcode_fallback_reflection = new ReflectionFunction( $shortcode_fallback );

		$expected_content = implode(
			'',
			[
				"<!--amp-source-stack $source_json-->",
				sprintf(
					'<p>before<!--amp-source-stack {"type":"plugin","name":"amp","file":%1$s,"line":%2$s,"function":"{closure}","shortcode":"test"}--><b>test</b><!--/amp-source-stack {"type":"plugin","name":"amp","file":%1$s,"line":%2$s,"function":"{closure}","shortcode":"test"}-->after</p>' . "\n",
					wp_json_encode( substr( $shortcode_fallback_reflection->getFileName(), strlen( AMP__DIR__ ) + 1 ) ),
					$shortcode_fallback_reflection->getStartLine()
				),
				"<!--/amp-source-stack $source_json-->",
			]
		);

		$this->assertEquals(
			preg_split( '/(?=<)/', $expected_content ),
			preg_split( '/(?=<)/', $filtered_content )
		);
	}

	/**
	 * Test can_output_buffer.
	 *
	 * Note that this method cannot currently be fully tested because
	 * it relies on `AMP_Theme_Support::start_output_buffering()` having
	 * been called, and this method starts an output buffer with a callback
	 *
	 * @covers AMP_Validation_Manager::can_output_buffer()
	 */
	public function test_can_output_buffer() {
		$this->assertFalse( AMP_Validation_Manager::can_output_buffer() );
	}

	/**
	 * Test wrapped_callback for filters.
	 *
	 * @covers AMP_Validation_Manager::wrapped_callback()
	 */
	public function test_filter_wrapped_callback() {
		$test_string     = 'Filter-amended Value';
		$filter_callback = [
			'function'      => static function ( $value ) use ( $test_string ) {
				return $value . $test_string;
			},
			'accepted_args' => 1,
			'source'        => [
				'type' => 'plugin',
				'name' => 'amp',
				'hook' => 'foo',
			],
		];

		$value = 'Some Value';
		apply_filters( 'foo', $value );
		$wrapped_callback = AMP_Validation_Manager::wrapped_callback( $filter_callback );
			$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $wrapped_callback );
		AMP_Theme_Support::start_output_buffering();
		$filtered_value = $wrapped_callback( $value );
		$output = ob_get_clean();
		$this->assertEquals( $value . $test_string, $filtered_value );
		$this->assertEmpty( $output, 'Expected no output since no action triggered.' );
	}

	/**
	 * Test wrapped_callback for actions.
	 *
	 * @covers AMP_Validation_Manager::wrapped_callback()
	 */
	public function test_action_wrapped_callback() {
		$test_string     = "<b class='\nfoo\nbar\n'>Cool!</b>";
		$action_callback = [
			'function'      => static function() use ( $test_string ) {
				echo $test_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			},
			'accepted_args' => 1,
			'source'        => [
				'type' => 'plugin',
				'name' => 'amp',
				'hook' => 'bar',
			],
		];

		do_action( 'bar' ); // So that output buffering will be done.
		$wrapped_callback = AMP_Validation_Manager::wrapped_callback( $action_callback );
		$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $wrapped_callback );
		AMP_Theme_Support::start_output_buffering();
		$wrapped_callback();
		$output = ob_get_clean();

		$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $wrapped_callback );
		$this->assertStringContainsString( $test_string, $output );
		$this->assertStringContainsString( '<!--amp-source-stack {"type":"plugin","name":"amp","hook":"bar"}', $output );
		$this->assertStringContainsString( '<!--/amp-source-stack {"type":"plugin","name":"amp","hook":"bar"}', $output );

		$action_callback = [
			'function'      => [ $this, 'get_string' ],
			'accepted_args' => 0,
			'source'        => [
				'type' => 'plugin',
				'name' => 'amp',
				'hook' => 'bar',
			],
		];

		$wrapped_callback = AMP_Validation_Manager::wrapped_callback( $action_callback );
		$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $wrapped_callback );
		AMP_Theme_Support::start_output_buffering();
		$result = $wrapped_callback();
		$output = ob_get_clean();
		$this->assertInstanceOf( AMP_Validation_Callback_Wrapper::class, $wrapped_callback );
		$this->assertEquals( '', $output );
		$this->assertEquals( $this->get_string(), $result );
		unset( $GLOBALS['post'] );
	}

	/**
	 * Test wrap_buffer_with_source_comments.
	 *
	 * @covers \AMP_Validation_Manager::wrap_buffer_with_source_comments()
	 */
	public function test_wrap_buffer_with_source_comments() {
		$initial_content = '<html><body></body></html>';
		$this->assertEquals( $initial_content, AMP_Validation_Manager::wrap_buffer_with_source_comments( $initial_content ) );

		$earliest_source = [ 'plugin' => 'foo' ];
		$latest_source   = [ 'theme' => 'bar' ];

		// Doesn't use array_merge, as wrap_buffer_with_source_comments() accesses this array with indices like 1 or 2.
		AMP_Validation_Manager::$hook_source_stack[] = $earliest_source;
		AMP_Validation_Manager::$hook_source_stack[] = $latest_source;

		$wrapped_content = AMP_Validation_Manager::wrap_buffer_with_source_comments( $initial_content );
		$this->assertStringContainsString( $initial_content, $wrapped_content );
		$this->assertStringContainsString( '<!--amp-source-stack', $wrapped_content );
		$this->assertStringContainsString( '<!--/amp-source-stack', $wrapped_content );
		$this->assertStringContainsString( wp_json_encode( $latest_source ), $wrapped_content );
		$this->assertStringNotContainsString( wp_json_encode( $earliest_source ), $wrapped_content );
	}

	/**
	 * Test get_amp_validate_nonce.
	 *
	 * @covers \AMP_Validation_Manager::get_amp_validate_nonce()
	 */
	public function test_get_amp_validate_nonce() {
		$nonce = AMP_Validation_Manager::get_amp_validate_nonce();
		$this->assertIsString( $nonce );
		$this->assertEquals( 32, strlen( $nonce ) );
	}

	/**
	 * Test should_validate_response.
	 *
	 * @covers AMP_Validation_Manager::should_validate_response()
	 */
	public function test_should_validate_response() {
		$this->assertFalse( AMP_Validation_Manager::should_validate_response() );

		// Making a request with an invalid nonce.
		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = 'invalid';
		$result = AMP_Validation_Manager::should_validate_response();
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'http_request_failed', $result->get_error_code() );

		// Making a request with a valid nonce.
		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = AMP_Validation_Manager::get_amp_validate_nonce();
		$this->assertTrue( AMP_Validation_Manager::should_validate_response() );
	}

	/**
	 * Test get_validate_response_data.
	 *
	 * @covers AMP_Validation_Manager::get_validate_response_data()
	 */
	public function test_get_validate_response_data() {
		$post = self::factory()->post->create_and_get();

		$this->go_to( get_permalink( $post ) );
		$source_html = '<style>amp-fit-text { color:red }</style><script>document.write("bad")</script><amp-fit-text width="300" height="200" layout="responsive">Lorem ipsum</amp-fit-text>';

		$sanitizer_classes = amp_get_content_sanitizers();
		$sanitizer_classes = AMP_Validation_Manager::filter_sanitizer_args( $sanitizer_classes );
		$sanitize_results  = AMP_Content_Sanitizer::sanitize_document(
			AMP_DOM_Utils::get_dom_from_content( $source_html ),
			$sanitizer_classes,
			[]
		);

		$data = AMP_Validation_Manager::get_validate_response_data( $sanitize_results );
		$this->assertArrayHasKey( 'url', $data );
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'queried_object', $data );

		$this->assertEquals( amp_get_current_url(), $data['url'] );

		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals( AMP_Validation_Manager::$validation_results, $data['results'] );

		$this->assertEquals(
			[
				'id'   => $post->ID,
				'type' => $post->post_type,
			],
			$data['queried_object']
		);
	}

	/**
	 * Test remove_illegal_source_stack_comments.
	 *
	 * @covers \AMP_Validation_Manager::remove_illegal_source_stack_comments()
	 */
	public function test_remove_illegal_source_stack_comments() {
		$html = '
			<html>
				<head>
					<script id="first" type="application/ld+json"><!--amp-source-stack {"type":"plugin","name":"add-scripts-and-styles-with-source-stacks.php","file":"add-scripts-and-styles-with-source-stacks.php","line":18,"function":"{closure}","hook":"my_print_json","priority":10}-->{"foo":"bar <b>foo<\/b>"}<!--/amp-source-stack {"type":"plugin","name":"add-scripts-and-styles-with-source-stacks.php","file":"add-scripts-and-styles-with-source-stacks.php","line":18,"function":"{closure}","hook":"my_print_json","priority":10}--></script>
					<style id="second">body { color: blue; }<!--amp-source-stack {"type":"plugin","name":"add-scripts-and-styles-with-source-stacks.php","file":"add-scripts-and-styles-with-source-stacks.php","line":18,"function":"{closure}","hook":"my_print_style","priority":10}-->body { color: red; }<!--/amp-source-stack {"type":"plugin","name":"add-scripts-and-styles-with-source-stacks.php","file":"add-scripts-and-styles-with-source-stacks.php","line":18,"function":"{closure}","hook":"my_print_style","priority":10}-->body { color: white; }</style>
				</head>
				<body>
					<script id="third" type="text/javascript">/* start custom scripts! */<!--amp-source-stack {"type":"plugin","name":"add-scripts-and-styles-with-source-stacks.php","file":"add-scripts-and-styles-with-source-stacks.php","line":18,"function":"{closure}","hook":"my_print_script","priority":10}-->document.write("hello!")<!--/amp-source-stack {"type":"plugin","name":"add-scripts-and-styles-with-source-stacks.php","file":"add-scripts-and-styles-with-source-stacks.php","line":18,"function":"{closure}","hook":"my_print_script","priority":10}-->/* end custom scripts! */</script>
				</body>
			</html>
		';

		$dom = Document::fromHtml( $html, Options::DEFAULTS );

		AMP_Validation_Manager::remove_illegal_source_stack_comments( $dom );

		$this->assertStringNotContainsString( 'amp-source-stack', $dom->saveHTML() );
		$this->assertEquals( '{"foo":"bar <b>foo<\/b>"}', $dom->getElementById( 'first' )->textContent );
		$this->assertEquals( 'body { color: blue; }body { color: red; }body { color: white; }', $dom->getElementById( 'second' )->textContent );
		$this->assertEquals( '/* start custom scripts! */document.write("hello!")/* end custom scripts! */', $dom->getElementById( 'third' )->textContent );
	}

	public function get_data_to_test_send_validate_response() {
		return [
			'ok_no_error_no_store' => [
				'status_code' => 200,
				'last_error'  => null,
				'store'       => false,
				'save_error'  => false,
			],
			'fatal_error_store'    => [
				'status_code' => 500,
				'last_error'  => [
					'type'    => E_ERROR,
					'message' => 'Something bad happened.',
					'file'    => __FILE__,
					'line'    => __LINE__,
				],
				'store'       => true,
				'save_error'  => false,
			],
			'warning_store'        => [
				'status_code' => 200,
				'last_error'  => [
					'type'    => E_WARNING,
					'message' => 'Something kinda bad happened.',
					'file'    => __FILE__,
					'line'    => __LINE__,
				],
				'store'       => true,
				'save_error'  => false,
			],
			'store_failure'        => [
				'status_code' => 200,
				'last_error'  => null,
				'store'       => true,
				'save_error'  => true,
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_send_validate_response
	 * @covers \AMP_Validation_Manager::send_validate_response()
	 */
	public function test_send_validate_response( $status_code, $last_error, $store, $save_error ) {
		$source_html          = '<html amp><head><style>body{color:red}</style></head><body><amp-layout layout="bad"></amp-layout></body></html>';
		$sanitizer_classes    = amp_get_content_sanitizers();
		$sanitizer_classes    = AMP_Validation_Manager::filter_sanitizer_args( $sanitizer_classes );
		$sanitization_results = AMP_Content_Sanitizer::sanitize_document(
			AMP_DOM_Utils::get_dom_from_content( $source_html ),
			$sanitizer_classes,
			[]
		);

		if ( $save_error ) {
			add_filter( 'wp_insert_post_empty_content', '__return_true' );
		}

		$response = AMP_Validation_Manager::send_validate_response( $sanitization_results, $status_code, $last_error, $store );
		$this->assertJson( $response );
		$data = json_decode( $response, true );

		if ( $save_error ) {
			$this->assertSame(
				[
					'code'    => 'empty_content',
					'message' => 'Content, title, and excerpt are empty.',
				],
				$data
			);
			return;
		}

		$this->assertArrayHasKey( 'http_status_code', $data );
		$this->assertArrayHasKey( 'php_fatal_error', $data );
		$this->assertArrayHasKey( 'queried_object', $data );
		$this->assertArrayHasKey( 'url', $data );
		$this->assertArrayHasKey( 'stylesheets', $data );
		$this->assertArrayHasKey( 'results', $data );

		$this->assertEquals( $status_code, $data['http_status_code'] );
		if ( $last_error && in_array( $last_error['type'], [ E_ERROR, E_RECOVERABLE_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE ], true ) ) {
			$this->assertIsArray( $data['php_fatal_error'] );
			$this->assertEquals( $last_error, $data['php_fatal_error'] );
		} else {
			$this->assertFalse( $data['php_fatal_error'] );
		}

		$this->assertCount( 1, $data['results'] );
		$this->assertEquals( 'SPECIFIED_LAYOUT_INVALID', $data['results'][0]['error']['code'] );

		$this->assertCount( 1, $data['stylesheets'] );

		if ( $store ) {
			$this->assertArrayHasKey( 'validated_url_post', $data );
			$this->assertArrayHasKey( 'id', $data['validated_url_post'] );
			$this->assertArrayHasKey( 'edit_link', $data['validated_url_post'] );
			$this->assertEquals( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG, get_post_type( $data['validated_url_post']['id'] ) );
		} else {
			$this->assertArrayNotHasKey( 'validated_url_post', $data );
		}
	}

	/**
	 * Test finalize_validation.
	 *
	 * @covers \AMP_Validation_Manager::finalize_validation()
	 * @covers \AMP_Validation_Manager::add_admin_bar_menu_items()
	 */
	public function test_finalize_validation() {
		self::set_capability();
		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		show_admin_bar( true );
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->go_to( home_url( '/' ) );

		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		ob_start();

		?>
		<html>
			<head>
				<meta charset="utf-8">
			</head>
			<body>
				<?php $admin_bar->render(); ?>
			</body>
		</html>
		<?php
		$html = ob_get_clean();

		$dom = new Document();
		$dom->loadHTML( $html );

		$this->assertInstanceOf( 'DOMElement', $dom->getElementById( 'wp-admin-bar-amp' ) );
		$status_icon_element = $dom->getElementById( 'amp-admin-bar-item-status-icon' );
		$this->assertInstanceOf( 'DOMElement', $status_icon_element );
		$valid_class_name = 'ab-icon amp-icon amp-valid';
		$this->assertEquals( $valid_class_name, $status_icon_element->getAttribute( 'class' ) );
		$validity_link_element = $dom->getElementById( 'wp-admin-bar-amp-validity' );
		$this->assertInstanceOf( 'DOMElement', $validity_link_element );
		$this->assertEquals( 'Validate URL', trim( $validity_link_element->textContent ) );

		$error1 = [ 'code' => AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_ATTR ];
		$error2 = [ 'code' => AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_TAG ];

		$term1_data = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( $error1 );
		$term2_data = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( $error2 );

		$term1_id = wp_insert_term( $term1_data['slug'], AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, $term1_data )['term_id'];
		$term2_id = wp_insert_term( $term1_data['slug'], AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, $term2_data )['term_id'];

		$get_small_text_content = function ( $validity_link_element ) {
			$small = $validity_link_element->getElementsByTagName( 'small' )->item( 0 );
			$this->assertInstanceOf( DOMElement::class, $small );
			$text = $small->textContent;
			$small->parentNode->removeChild( $small );
			return $text;
		};

		// Test 1 unreviewed removed term.
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => true,
			],
		];
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS ] );
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(1 issue: unreviewed)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-warning', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );

		// Test 1 reviewed removed term.
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS ] );
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => true,
			],
		];
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(1 issue: reviewed)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-valid', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );

		// Test 1 unreviewed kept term in Standard Mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertTrue( amp_is_canonical() );
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS ] );
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => false,
			],
		];
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(1 issue: unreviewed, kept)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-warning', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );

		// Test 1 reviewed kept term in Standard Mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->assertTrue( amp_is_canonical() );
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS ] );
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => false,
			],
		];
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(1 issue: kept)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-warning', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );

		// Test 1 unreviewed kept term in Transitional Mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_theme_support( 'amp', [ 'paired' => true ] );
		$this->assertTrue( ! amp_is_canonical() );
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS ] );
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => false,
			],
		];
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(1 issue: unreviewed, kept)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-invalid', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );

		// Test 1 reviewed kept term in Transitional Mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		add_theme_support( 'amp', [ 'paired' => true ] );
		$this->assertTrue( ! amp_is_canonical() );
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS ] );
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => false,
			],
		];
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(1 issue: kept)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-invalid', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );

		// Test 2 issues: one reviewed removed and one unreviewed removed.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		add_theme_support( 'amp', [ 'paired' => false ] );
		$this->assertTrue( amp_is_canonical() );
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS ] );
		wp_update_term( $term2_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS ] );
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => true,
			],
			[
				'error'     => $error2,
				'sanitized' => true,
			],
		];
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(2 issues: 1 unreviewed)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-warning', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );

		// Test 2 issues: two reviewed removed.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		add_theme_support( 'amp', [ 'paired' => false ] );
		$this->assertTrue( amp_is_canonical() );
		wp_update_term( $term1_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS ] );
		wp_update_term( $term2_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, [ 'term_group' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS ] );
		AMP_Validation_Manager::$validation_results = [
			[
				'error'     => $error1,
				'sanitized' => true,
			],
			[
				'error'     => $error2,
				'sanitized' => true,
			],
		];
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertEquals( '(2 issues: all reviewed)', $get_small_text_content( $validity_link_element ) );
		$this->assertStringContainsString( 'amp-icon amp-valid', $status_icon_element->getAttribute( 'class' ) );
		$status_icon_element->setAttribute( 'class', $valid_class_name );
	}

	/**
	 * Test filter_sanitizer_args
	 *
	 * @covers AMP_Validation_Manager::filter_sanitizer_args()
	 */
	public function test_filter_sanitizer_args() {
		global $post;
		$post       = self::factory()->post->create_and_get();
		$sanitizers = [
			AMP_Img_Sanitizer::class      => [],
			AMP_Form_Sanitizer::class     => [],
			AMP_Comments_Sanitizer::class => [],
		];

		$expected_callback   = self::TESTED_CLASS . '::add_validation_error';
		$filtered_sanitizers = AMP_Validation_Manager::filter_sanitizer_args( $sanitizers );
		foreach ( $filtered_sanitizers as $args ) {
			$this->assertEquals( $expected_callback, $args['validation_error_callback'] );
		}
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
	}

	/**
	 * Test for validate_after_plugin_activation().
	 *
	 * @covers AMP_Validation_Manager::validate_after_plugin_activation()
	 */
	public function test_validate_after_plugin_activation() {
		add_filter( 'amp_pre_get_permalink', '__return_empty_string' );
		$r = AMP_Validation_Manager::validate_after_plugin_activation();
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'no_published_post_url_available', $r->get_error_code() );
		remove_filter( 'amp_pre_get_permalink', '__return_empty_string' );

		$validation_error = [
			'code' => 'example',
		];

		$validation = [
			'results' => [
				[
					'error'     => $validation_error,
					'sanitized' => false,
				],
			],
		];

		self::factory()->post->create();
		$filter = static function() use ( $validation ) {
			return [
				'body' => wp_json_encode( $validation ),
			];
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$r = AMP_Validation_Manager::validate_after_plugin_activation();
		remove_filter( 'pre_http_request', $filter );
		$this->assertEquals( [ $validation_error ], $r );
		$this->assertEquals( [ $validation_error ], get_transient( AMP_Validation_Manager::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY ) );
	}

	/**
	 * Get validation errors.
	 *
	 * @return array
	 */
	public function get_validation_errors() {
		return [
			'simple error'                         => [
				[
					[
						'code' => 'example',
					],
				],
				'',
			],
			'error containing a huge HTML comment' => [
				[
					[
						'code' => 'example',
						'text' => '<!-- ' . str_repeat( 'a', 1000000 ) . ' -->',
					],
				],
				"\n<!-- Generated by greatness! -->\n",
			],
			'error with multiple comments at end'  => [
				[
					[
						'code' => 'example',
					],
				],
				'<!-- generated 2 seconds ago -->
				<!-- generated in 1.134 seconds -->
				<!-- served from batcache in 0.003 seconds -->
				<!-- expires in 298 seconds -->',
			],
			'error with multi-line comment at end' => [
				[
					[
						'code' => 'example',
					],
				],
				'<!--
					generated 2 seconds ago
					generated in 1.134 seconds
					served from batcache in 0.003 seconds
					expires in 298 seconds
				-->',
			],
		];
	}

	/**
	 * Test for validate_url() and validate_url_and_store().
	 *
	 * @dataProvider get_validation_errors()
	 *
	 * @covers AMP_Validation_Manager::validate_url()
	 * @covers AMP_Validation_Manager::validate_url_and_store()
	 *
	 * @param array  $validation_errors Validation errors.
	 * @param string $after_matter      After matter that is appended to response body.
	 */
	public function test_validate_url( $validation_errors, $after_matter ) {
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );

		// Test headers absent.
		self::factory()->post->create();
		$filter = static function() {
			return [
				'body'    => '',
				'headers' => [],
			];
		};
		add_filter( 'pre_http_request', $filter );
		$r = AMP_Validation_Manager::validate_url( home_url( '/' ) );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'white_screen_of_death', $r->get_error_code() );

		$r2 = AMP_Validation_Manager::validate_url_and_store( home_url( '/' ) );
		$this->assertInstanceOf( 'WP_Error', $r2 );
		$this->assertEquals( $r->get_error_code(), $r2->get_error_code() );
		remove_filter( 'pre_http_request', $filter );

		// Test success.
		$validated_url  = home_url( '/foo/' );
		$php_error      = [
			'type'    => E_ERROR,
			'message' => 'Bad thing happened!',
		];
		$queried_object = [
			'type' => 'post',
			'id'   => 123,
		];
		$stylesheets = [ [ 'CSS!' ] ];
		$filter        = function( $pre, $r, $url ) use ( $validation_errors, $php_error, $queried_object, $stylesheets, $after_matter ) {
			$this->assertStringContainsString( AMP_Validation_Manager::VALIDATE_QUERY_VAR . '=', $url );

			$validation = [
				'results'         => [],
				'stylesheets'     => $stylesheets,
				'php_fatal_error' => $php_error,
				'queried_object'  => $queried_object,
			];
			foreach ( $validation_errors as $error ) {
				$sanitized            = false;
				$validation['results'][] = compact( 'error', 'sanitized' );
			}

			return [
				// Prepend JSON with byte order mark, whitespace, and append with HTML comment to ensure stripped.
				'body'    => "\xEF\xBB\xBF" . ' ' . wp_json_encode( $validation ) . $after_matter,
				'headers' => [
					'content-type' => 'application/json',
				],
			];
		};

		add_filter( 'pre_http_request', $filter, 10, 3 );
		$r = AMP_Validation_Manager::validate_url( $validated_url );
		$this->assertIsArray( $r );
		$this->assertEquals( $validation_errors, wp_list_pluck( $r['results'], 'error' ) );
		$this->assertEquals( $validated_url, $r['url'] );
		$this->assertEquals( $stylesheets, $r['stylesheets'] );
		$this->assertEquals( $php_error, $r['php_fatal_error'] );
		$this->assertEquals( $queried_object, $r['queried_object'] );

		// Now try the same, but store the results.
		$r = AMP_Validation_Manager::validate_url_and_store( $validated_url );
		$this->assertIsArray( $r );
		$this->assertEquals( $validation_errors, wp_list_pluck( $r['results'], 'error' ) );
		$this->assertEquals( $validated_url, $r['url'] );
		$this->assertEquals( $stylesheets, $r['stylesheets'] );
		$this->assertEquals( $php_error, $r['php_fatal_error'] );
		$this->assertEquals( $queried_object, $r['queried_object'] );
		$this->assertArrayHasKey( 'post_id', $r );
		$this->assertEquals( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG, get_post_type( $r['post_id'] ) );
		$this->assertEquals( $r['url'], AMP_Validated_URL_Post_Type::get_url_from_post( $r['post_id'] ) );
		$this->assertEquals( $php_error, json_decode( get_post_meta( $r['post_id'], AMP_Validated_URL_Post_Type::PHP_FATAL_ERROR_POST_META_KEY, true ), true ) );
		$this->assertEquals( $queried_object, get_post_meta( $r['post_id'], AMP_Validated_URL_Post_Type::QUERIED_OBJECT_POST_META_KEY, true ) );
		$this->assertEquals( $stylesheets, json_decode( get_post_meta( $r['post_id'], AMP_Validated_URL_Post_Type::STYLESHEETS_POST_META_KEY, true ), true ) );
		$this->assertEquals( AMP_Validated_URL_Post_Type::get_validated_environment(), get_post_meta( $r['post_id'], AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY, true ) );

		$updated_validated_url = home_url( '/bar/' );
		$previous_post_id      = $r['post_id'];
		$r = AMP_Validation_Manager::validate_url_and_store( $updated_validated_url, $previous_post_id );
		$this->assertEquals( $previous_post_id, $r['post_id'] );
		$this->assertEquals( $updated_validated_url, AMP_Validated_URL_Post_Type::get_url_from_post( $r['post_id'] ) );
		remove_filter( 'pre_http_request', $filter );
	}

	/**
	 * Test for validate_url() for a URL on another site.
	 *
	 * @covers AMP_Validation_Manager::validate_url()
	 */
	public function test_validate_url_on_another_site() {
		$r = AMP_Validation_Manager::validate_url( 'https://another-site.example.com/' );
		$this->assertInstanceOf( WP_Error::class, $r );
		$this->assertEquals( 'http_request_failed', $r->get_error_code() );
		$this->assertStringStartsWith( 'Unable to validate a URL on another site. Attempted to validate: https://another-site.example.com/', $r->get_error_message() );
	}

	/**
	 * Test for validate_url() for a URL that redirects to another site.
	 *
	 * @covers AMP_Validation_Manager::validate_url()
	 */
	public function test_validate_url_for_redirect_to_another_site() {
		$filter = static function() {
			return [
				'response' => [
					'code' => 301,
				],
				'headers'  => [
					'Location' => 'https://redirected-site.example.com/',
				],
			];
		};
		add_filter( 'pre_http_request', $filter );
		$r = AMP_Validation_Manager::validate_url( home_url() );
		$this->assertInstanceOf( WP_Error::class, $r );
		$this->assertEquals( 'http_request_failed', $r->get_error_code() );
		$this->assertStringStartsWith( 'Unable to validate a URL on another site. Attempted to validate: https://redirected-site.example.com/', $r->get_error_message() );
	}

	/**
	 * @covers AMP_Validation_Manager::serialize_validation_error_messages()
	 * @covers AMP_Validation_Manager::unserialize_validation_error_messages()
	 */
	public function test_serialize_unserialize_validation_error_messages() {
		$messages = [
			'foo',
			'bar',
			'bar',
			'baz',
		];

		$encoded_messages = AMP_Validation_Manager::serialize_validation_error_messages( $messages );
		$this->assertTrue( is_string( $encoded_messages ) );
		$this->assertStringContainsString( ':', $encoded_messages );

		$decoded_messages = AMP_Validation_Manager::unserialize_validation_error_messages( $encoded_messages );
		$this->assertTrue( is_array( $decoded_messages ) );
		$this->assertCount( 3, $decoded_messages );
		$this->assertEqualSets( array_unique( $messages ), $decoded_messages );

		$decoded_messages = AMP_Validation_Manager::unserialize_validation_error_messages( 'badhash:badencoding' );
		$this->assertNull( $decoded_messages );
	}

	/**
	 * Test for print_plugin_notice()
	 *
	 * @covers AMP_Validation_Manager::print_plugin_notice()
	 */
	public function test_print_plugin_notice() {
		global $pagenow;
		$output = get_echo( [ AMP_Validation_Manager::class, 'print_plugin_notice' ] );
		$this->assertEmpty( $output );
		$pagenow          = 'plugins.php';
		$_GET['activate'] = 'true';

		$cache_plugins_backup = wp_cache_get( 'plugins', 'plugins' );

		$plugins = [
			'' => [
				$this->plugin_name => [
					'Name' => 'Foo Bar',
				],
			],
		];

		wp_cache_set( 'plugins', $plugins, 'plugins' );

		set_transient(
			AMP_Validation_Manager::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY,
			[
				[
					'code'    => 'example',
					'sources' => [
						[
							'type' => 'plugin',
							'name' => 'foo-bar',
						],
					],
				],
			]
		);
		$output = get_echo( [ AMP_Validation_Manager::class, 'print_plugin_notice' ] );
		$this->assertStringContainsString( 'Warning: The following plugin may be incompatible with AMP', $output );
		$this->assertStringContainsString( 'Foo Bar', $output );
		$this->assertStringContainsString( 'More details', $output );
		$this->assertStringContainsString( admin_url( 'edit.php' ), $output );

		if ( $cache_plugins_backup ) {
			wp_cache_set( 'plugins', $cache_plugins_backup, 'plugins' );
		}
	}

	/**
	 * Test enqueue_block_validation.
	 *
	 * @covers AMP_Validation_Manager::enqueue_block_validation()
	 */
	public function test_enqueue_block_validation() {
		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestSkipped( 'The block editor is not available.' );
		} elseif ( version_compare( get_bloginfo( 'version' ), '5.3', '<' ) ) {
			$this->markTestSkipped( 'Block editor is too old.' );
		}

		AMP_Validation_Manager::enqueue_block_validation();

		$slug = 'amp-block-validation';
		$this->assertFalse( wp_script_is( $slug, 'enqueued' ) );

		// Ensure not displayed when dev tools is disabled.
		$service = $this->injector->make( UserAccess::class );
		$this->set_capability();
		$service->set_user_enabled( wp_get_current_user()->ID, false );
		AMP_Validation_Manager::enqueue_block_validation();
		$this->assertFalse( wp_script_is( $slug, 'enqueued' ) );

		$this->setup_environment( true, true );
		$service->set_user_enabled( wp_get_current_user()->ID, true );
		AMP_Validation_Manager::enqueue_block_validation();
		$this->assertTrue( wp_script_is( $slug, 'enqueued' ) );

		$script                = wp_scripts()->registered[ $slug ];
		$expected_dependencies = [
			'lodash',
			'react',
			'wp-api-fetch',
			'wp-block-editor',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-edit-post',
			'wp-element',
			'wp-hooks',
			'wp-i18n',
			'wp-plugins',
			'wp-polyfill',
			'wp-url',
		];

		$this->assertStringContainsString( 'js/amp-block-validation.js', $script->src );
		$this->assertEqualSets( $expected_dependencies, $script->deps );
		$this->assertStringContainsString( $slug, wp_scripts()->queue );

		$style = wp_styles()->registered[ $slug ];
		$this->assertStringContainsString( 'css/amp-block-validation.css', $style->src );
		$this->assertEquals( AMP__VERSION, $style->ver );
		$this->assertStringContainsString( $slug, wp_styles()->queue );
	}

	/**
	 * Processes markup, to determine AMP validity.
	 *
	 * Passes $markup through the AMP sanitizers.
	 * Also passes a 'validation_error_callback' to keep track of stripped attributes and nodes.
	 *
	 * @param string $markup The markup to process.
	 * @return string Sanitized markup.
	 */
	protected function process_markup( $markup ) {
		global $content_width;

		AMP_Theme_Support::register_content_embed_handlers();

		/** This filter is documented in wp-includes/post-template.php */
		$markup = apply_filters( 'the_content', $markup );
		$args   = [
			'content_max_width'         => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH,
			'validation_error_callback' => 'AMP_Validation_Manager::add_validation_error',
		];

		$results = AMP_Content_Sanitizer::sanitize( $markup, amp_get_content_sanitizers(), $args );
		return $results[0];
	}

	/**
	 * Test process_markup.
	 */
	public function test_process_markup() {
		$this->set_capability();
		$this->process_markup( $this->valid_amp_img );
		$this->assertEquals( [], AMP_Validation_Manager::$validation_results );

		AMP_Validation_Manager::reset_validation_results();
		$video = '<video src="https://example.com/video">';
		$this->process_markup( $video );
		// This isn't valid AMP, but the sanitizer should convert it to an <amp-video>, without stripping anything.
		$this->assertEquals( [], AMP_Validation_Manager::$validation_results );

		AMP_Validation_Manager::reset_validation_results();

		$this->process_markup( $this->disallowed_tag );
		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals( 'script', AMP_Validation_Manager::$validation_results[0]['error']['node_name'] );

		AMP_Validation_Manager::reset_validation_results();
		$disallowed_style = '<div style="display:none"></div>';
		$this->process_markup( $disallowed_style );
		$this->assertEquals( [], AMP_Validation_Manager::$validation_results );

		AMP_Validation_Manager::reset_validation_results();
		$invalid_video = '<video width="200" height="100"></video>';
		$this->process_markup( $invalid_video );
		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals( 'video', AMP_Validation_Manager::$validation_results[0]['error']['node_name'] );
		AMP_Validation_Manager::reset_validation_results();

		$this->process_markup( '<button onclick="evil()">Do it</button>' );
		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals( 'onclick', AMP_Validation_Manager::$validation_results[0]['error']['node_name'] );
		AMP_Validation_Manager::reset_validation_results();
	}

	/**
	 * Add a nonce to the $_REQUEST, so that is_authorized() returns true.
	 *
	 * @return void
	 */
	public function set_capability() {
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);
	}

	/**
	 * Outputs a div.
	 *
	 * @return void
	 */
	public function output_div() {
		echo '<div></div>';
	}

	/**
	 * Outputs a notice.
	 *
	 * @param string $message The message to output.
	 *
	 * @return void
	 */
	public function output_notice( $message ) {
		printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_attr( $message ) );
	}

	/**
	 * Outputs a message with an excerpt.
	 *
	 * @param string $message The message to output.
	 * @param string $id The ID of the post.
	 *
	 * @return void
	 */
	public function output_message( $message, $id ) {
		printf( '<p>%s</p><p>%s</p>', esc_attr( $message ), esc_html( $id ) );
	}

	/**
	 * Gets a string.
	 *
	 * @return string
	 */
	public function get_string() {
		return 'Example string';
	}

	/**
	 * Creates and inserts a custom post.
	 *
	 * @param array  $errors Validation errors to populate.
	 * @param string $url    URL that the errors occur on. Defaults to the home page.
	 * @return int|WP_Error $error_post The ID of new custom post, or an error.
	 */
	public function create_custom_post( $errors = [], $url = null ) {
		if ( ! $url ) {
			$url = home_url( '/' );
		}

		return AMP_Validated_URL_Post_Type::store_validation_errors( $errors, $url );
	}
}
