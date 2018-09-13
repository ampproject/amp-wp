<?php
/**
 * Tests for AMP_Validation_Manager class.
 *
 * @package AMP
 */

// phpcs:disable Generic.Formatting.MultipleStatementAlignment.NotSameWarning

/**
 * Tests for AMP_Validation_Manager class.
 *
 * @covers AMP_Validation_Manager
 * @since 0.7
 */
class Test_AMP_Validation_Manager extends \WP_UnitTestCase {

	/**
	 * The name of the tested class.
	 *
	 * @var string
	 */
	const TESTED_CLASS = 'AMP_Validation_Manager';

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
	 * Setup.
	 *
	 * @inheritdoc
	 * @global $wp_registered_widgets
	 */
	public function setUp() {
		parent::setUp();
		$dom_document = new DOMDocument( '1.0', 'utf-8' );
		$this->node   = $dom_document->createElement( self::TAG_NAME );
		AMP_Validation_Manager::reset_validation_results();
		$this->original_wp_registered_widgets = $GLOBALS['wp_registered_widgets'];
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		$GLOBALS['wp_registered_widgets'] = $this->original_wp_registered_widgets; // WPCS: override ok.
		remove_theme_support( 'amp' );
		$_REQUEST = array();
		unset( $GLOBALS['current_screen'] );
		AMP_Validation_Manager::$should_locate_sources = false;
		AMP_Validation_Manager::$hook_source_stack     = array();
		AMP_Validation_Manager::$validation_results    = array();
		AMP_Validation_Manager::reset_validation_results();
		parent::tearDown();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Validation_Manager::init()
	 */
	public function test_init() {
		add_theme_support( 'amp' );
		AMP_Validation_Manager::init();

		$this->assertTrue( post_type_exists( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ) );
		$this->assertTrue( taxonomy_exists( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ) );

		$this->assertEquals( 10, has_action( 'save_post', self::TESTED_CLASS . '::handle_save_post_prompting_validation' ) );
		$this->assertEquals( 10, has_action( 'enqueue_block_editor_assets', self::TESTED_CLASS . '::enqueue_block_validation' ) );

		$this->assertEquals( 10, has_action( 'edit_form_top', self::TESTED_CLASS . '::print_edit_form_validation_status' ) );
		$this->assertEquals( 10, has_action( 'all_admin_notices', self::TESTED_CLASS . '::print_plugin_notice' ) );

		$this->assertEquals( 10, has_action( 'rest_api_init', self::TESTED_CLASS . '::add_rest_api_fields' ) );

		$this->assertContains( AMP_Validation_Manager::VALIDATION_ERRORS_QUERY_VAR, wp_removable_query_args() );
		$this->assertEquals( 100, has_action( 'admin_bar_menu', array( self::TESTED_CLASS, 'add_admin_bar_menu_items' ) ) );

		$this->assertFalse( has_action( 'wp', array( self::TESTED_CLASS, 'wrap_widget_callbacks' ) ) );
		$this->assertEquals( 10, has_filter( 'amp_validation_error_sanitized', array( self::TESTED_CLASS, 'filter_tree_shaking_validation_error_as_accepted' ) ) );

		// Make sure should_locate_sources arg is recognized, as is disabling of tree-shaking.
		remove_all_filters( 'amp_validation_error_sanitized' );
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		AMP_Options_Manager::update_option( 'accept_tree_shaking', false );
		AMP_Validation_Manager::init( array(
			'should_locate_sources' => true,
		) );
		$this->assertEquals( 10, has_action( 'wp', array( self::TESTED_CLASS, 'wrap_widget_callbacks' ) ) );
		$this->assertFalse( has_filter( 'amp_validation_error_sanitized', array( self::TESTED_CLASS, 'filter_tree_shaking_validation_error_as_accepted' ) ) );
	}

	/**
	 * Test filter_tree_shaking_validation_error_as_accepted.
	 *
	 * @covers AMP_Validation_Manager::filter_tree_shaking_validation_error_as_accepted()
	 */
	public function test_filter_tree_shaking_validation_error_as_accepted() {
		$this->assertNull( AMP_Validation_Manager::filter_tree_shaking_validation_error_as_accepted( null, array( 'code' => 'foo' ) ) );
		$this->assertTrue( AMP_Validation_Manager::filter_tree_shaking_validation_error_as_accepted( null, array( 'code' => AMP_Style_Sanitizer::TREE_SHAKING_ERROR_CODE ) ) );
	}

	/**
	 * Test add_validation_hooks.
	 *
	 * @covers AMP_Validation_Manager::is_sanitization_forcibly_accepted()
	 */
	public function test_is_sanitization_forcibly_accepted() {
		remove_theme_support( 'amp' );
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_forcibly_accepted() );

		remove_theme_support( 'amp' );
		AMP_Options_Manager::update_option( 'force_sanitization', true );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_forcibly_accepted() );

		add_theme_support( 'amp' );
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_forcibly_accepted() );

		add_theme_support( 'amp', array( 'paired' => true ) );
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		$this->assertFalse( AMP_Validation_Manager::is_sanitization_forcibly_accepted() );

		add_theme_support( 'amp', array( 'paired' => true ) );
		AMP_Options_Manager::update_option( 'force_sanitization', true );
		$this->assertTrue( AMP_Validation_Manager::is_sanitization_forcibly_accepted() );
	}

	/**
	 * Test add_admin_bar_menu_items.
	 *
	 * @covers AMP_Validation_Manager::add_admin_bar_menu_items()
	 */
	public function test_add_admin_bar_menu_items() {
		AMP_Options_Manager::update_option( 'force_sanitization', false );

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
		add_theme_support( 'amp' );
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( AMP_Validation_Manager::has_cap() );
		add_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$this->assertNull( $admin_bar->get_node( 'amp' ) );
		remove_filter( 'amp_supportable_templates', '__return_empty_array' );
		AMP_Options_Manager::update_option( 'all_templates_supported', true );

		// Admin bar item available in native mode.
		add_theme_support( 'amp', array( 'paired' => false ) );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$node = $admin_bar->get_node( 'amp' );
		$this->assertInternalType( 'object', $node );
		$this->assertContains( 'action=amp_validate', $node->href );
		$this->assertNull( $admin_bar->get_node( 'amp-view' ) );
		$this->assertInternalType( 'object', $admin_bar->get_node( 'amp-validity' ) );

		// Admin bar item available in paired mode.
		add_theme_support( 'amp', array( 'paired' => true ) );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$node = $admin_bar->get_node( 'amp' );
		$this->assertInternalType( 'object', $node );
		$this->assertStringEndsWith( '?amp', $node->href );
		$this->assertInternalType( 'object', $admin_bar->get_node( 'amp-view' ) );
		$this->assertInternalType( 'object', $admin_bar->get_node( 'amp-validity' ) );

		// Admin bar item available in paired mode with validation errors.
		$_GET[ AMP_Validation_Manager::VALIDATION_ERRORS_QUERY_VAR ] = 3;
		add_theme_support( 'amp', array( 'paired' => true ) );
		$admin_bar = new WP_Admin_Bar();
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$node = $admin_bar->get_node( 'amp' );
		$this->assertInternalType( 'object', $node );
		$this->assertContains( 'action=amp_validate', $node->href );
		$this->assertNull( $admin_bar->get_node( 'amp-view' ) );
		$this->assertInternalType( 'object', $admin_bar->get_node( 'amp-validity' ) );
	}

	/**
	 * Test add_validation_error_sourcing.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error_sourcing()
	 */
	public function test_add_validation_error_sourcing() {
		AMP_Validation_Manager::add_validation_error_sourcing();
		$this->assertEmpty( AMP_Validation_Manager::$validation_error_status_overrides );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'the_content', array( self::TESTED_CLASS, 'decorate_filter_source' ) ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'the_excerpt', array( self::TESTED_CLASS, 'decorate_filter_source' ) ) );
		$this->assertEquals( -1, has_action( 'do_shortcode_tag', array( self::TESTED_CLASS, 'decorate_shortcode_source' ) ) );

		// Test overrides.
		$validation_error_term_1 = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( array( 'test' => 1 ) );
		$validation_error_term_2 = AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( array( 'test' => 2 ) );
		$_REQUEST[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = AMP_Validation_Manager::get_amp_validate_nonce();
		$_REQUEST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] = array(
			$validation_error_term_1['slug'] => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS,
			$validation_error_term_2['slug'] => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS,
		);
		AMP_Validation_Manager::add_validation_error_sourcing();
		$this->assertCount( 2, AMP_Validation_Manager::$validation_error_status_overrides );
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
		if ( ( version_compare( get_bloginfo( 'version' ), '4.9', '<' ) ) ) {
			$this->markTestSkipped( 'The WP version is less than 4.9, so Gutenberg did not init.' );
		}

		$priority = has_filter( 'the_content', 'do_blocks' );
		$this->assertNotFalse( $priority );
		AMP_Validation_Manager::add_validation_error_sourcing();
		$this->assertEquals( $priority - 1, has_filter( 'the_content', array( self::TESTED_CLASS, 'add_block_source_comments' ) ) );
	}

	/**
	 * Tests handle_save_post_prompting_validation.
	 *
	 * @covers AMP_Validation_Manager::handle_save_post_prompting_validation()
	 * @covers AMP_Validation_Manager::validate_queued_posts_on_frontend()
	 */
	public function test_handle_save_post_prompting_validation_and_validate_queued_posts_on_frontend() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$GLOBALS['pagenow']        = 'post.php'; // WPCS: override ok.

		register_post_type( 'secret', array( 'public' => false ) );
		$secret           = $this->factory()->post->create_and_get( array( 'post_type' => 'secret' ) );
		$_POST['post_ID'] = $secret->ID;
		AMP_Validation_Manager::handle_save_post_prompting_validation( $secret->ID );
		$this->assertFalse( has_action( 'shutdown', array( 'AMP_Validation_Manager', 'validate_queued_posts_on_frontend' ) ) );
		$this->assertEmpty( AMP_Validation_Manager::validate_queued_posts_on_frontend() );

		$auto_draft       = $this->factory()->post->create_and_get( array( 'post_status' => 'auto-draft' ) );
		$_POST['post_ID'] = $auto_draft->ID;
		AMP_Validation_Manager::handle_save_post_prompting_validation( $auto_draft->ID );
		$this->assertFalse( has_action( 'shutdown', array( 'AMP_Validation_Manager', 'validate_queued_posts_on_frontend' ) ) );
		$this->assertEmpty( AMP_Validation_Manager::validate_queued_posts_on_frontend() );

		// Testing without $_POST context.
		$post = $this->factory()->post->create_and_get( array( 'post_type' => 'post' ) );
		AMP_Validation_Manager::handle_save_post_prompting_validation( $post->ID );
		$this->assertFalse( has_action( 'shutdown', array( 'AMP_Validation_Manager', 'validate_queued_posts_on_frontend' ) ) );

		// Test success.
		$post = $this->factory()->post->create_and_get( array( 'post_type' => 'post' ) );
		$_POST['post_ID'] = $post->ID;
		AMP_Validation_Manager::handle_save_post_prompting_validation( $post->ID );
		$this->assertEquals( 10, has_action( 'shutdown', array( 'AMP_Validation_Manager', 'validate_queued_posts_on_frontend' ) ) );

		add_filter( 'pre_http_request', function() {
			return new WP_Error( 'http_request_made' );
		} );
		$results = AMP_Validation_Manager::validate_queued_posts_on_frontend();
		$this->assertArrayHasKey( $post->ID, $results );
		$this->assertInstanceOf( 'WP_Error', $results[ $post->ID ] );

		unset( $GLOBALS['pagenow'] );
	}

	/**
	 * Test add_rest_api_fields.
	 *
	 * @covers AMP_Validation_Manager::add_rest_api_fields()
	 */
	public function test_add_rest_api_fields() {
		// Test in a non Native-AMP (canonical) context.
		AMP_Validation_Manager::add_rest_api_fields();
		$post_types_non_canonical = array_intersect(
			get_post_types_by_support( 'amp' ),
			get_post_types( array(
				'show_in_rest' => true,
			) )
		);
		$this->assert_rest_api_field_present( $post_types_non_canonical );

		// Test in a Native AMP (canonical) context.
		add_theme_support( 'amp' );
		AMP_Validation_Manager::add_rest_api_fields();
		$post_types_canonical = get_post_types_by_support( 'editor' );
		$this->assert_rest_api_field_present( $post_types_canonical );
	}

	/**
	 * Asserts that the post types have the additional REST field.
	 *
	 * @param array $post_types The post types that should have the REST field.
	 */
	protected function assert_rest_api_field_present( $post_types ) {
		foreach ( $post_types as $post_type ) {
			$field = $GLOBALS['wp_rest_additional_fields'][ $post_type ][ AMP_Validation_Manager::VALIDITY_REST_FIELD_NAME ];
			$this->assertEquals(
				$field['schema'],
				array(
					'description' => 'AMP validity status',
					'type'        => 'object',
				)
			);
			$this->assertEquals( $field['get_callback'], array( self::TESTED_CLASS, 'get_amp_validity_rest_field' ) );
		}
	}

	/**
	 * Test get_amp_validity_rest_field.
	 *
	 * @covers AMP_Validation_Manager::get_amp_validity_rest_field()
	 * @covers AMP_Validation_Manager::validate_url()
	 */
	public function test_get_amp_validity_rest_field() {
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		AMP_Invalid_URL_Post_Type::register();
		AMP_Validation_Error_Taxonomy::register();

		$id = $this->factory()->post->create();
		$this->assertNull( AMP_Validation_Manager::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'GET' )
		) );

		// Create an error custom post for the ID, so this will return the errors in the field.
		$errors = array(
			array(
				'code' => 'test',
			),
		);
		$this->create_custom_post(
			$errors,
			amp_get_permalink( $id )
		);

		// Make sure capability check is honored.
		$this->assertNull( AMP_Validation_Manager::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'GET' )
		) );

		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );

		// GET request.
		$field = AMP_Validation_Manager::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'GET' )
		);
		$this->assertArrayHasKey( 'results', $field );
		$this->assertArrayHasKey( 'review_link', $field );
		$this->assertEquals(
			$field['results'],
			array_map(
				function ( $error ) {
					return array(
						'sanitized'   => false,
						'error'       => $error,
						'status'      => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
						'term_status' => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
						'forced'      => false,
					);
				},
				$errors
			)
		);

		// PUT request.
		add_filter( 'pre_http_request', function() {
			return array(
				'body'     => '<html><body></body><!--AMP_VALIDATION:{"results":[]}--></html>',
				'response' => array(
					'code'    => 200,
					'message' => 'ok',
				),
			);
		} );
		$field = AMP_Validation_Manager::get_amp_validity_rest_field(
			compact( 'id' ),
			'',
			new WP_REST_Request( 'PUT' )
		);
		$this->assertArrayHasKey( 'results', $field );
		$this->assertArrayHasKey( 'review_link', $field );
		$this->assertEmpty( $field['results'] );
	}

	/**
	 * Test has_cap.
	 *
	 * @covers AMP_Validation_Manager::has_cap()
	 */
	public function test_has_cap() {
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'subscriber',
		) ) );
		$this->assertFalse( AMP_Validation_Manager::has_cap() );

		$this->set_capability();
		$this->assertTrue( AMP_Validation_Manager::has_cap() );
	}

	/**
	 * Test add_validation_error.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error()
	 */
	public function test_add_validation_error_track_removed() {
		AMP_Validation_Manager::$should_locate_sources = true;
		$this->assertEmpty( AMP_Validation_Manager::$validation_results );

		$that = $this;
		$node = $this->node;
		add_filter( 'amp_validation_error', function( $error, $context ) use ( $node, $that ) {
			$error['filtered'] = true;
			$that->assertEquals( AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE, $error['code'] );
			$that->assertSame( $node, $context['node'] );
			return $error;
		}, 10, 2 );

		AMP_Validation_Manager::add_validation_error(
			array(
				'node_name'       => $this->node->nodeName,
				'code'            => AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE,
				'node_attributes' => array(),
			),
			array(
				'node' => $this->node,
			)
		);

		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals(
			array(
				'node_name'       => 'img',
				'sources'         => array(),
				'code'            => AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE,
				'node_attributes' => array(),
				'filtered'        => true,
			),
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
			array(
				'node' => $this->node,
			)
		);
		$this->assertNotEmpty( AMP_Validation_Manager::$validation_results );
	}

	/**
	 * Test reset_validation_results.
	 *
	 * @covers AMP_Validation_Manager::reset_validation_results()
	 */
	public function test_reset_validation_results() {
		AMP_Validation_Manager::add_validation_error( array(
			'code' => 'test',
		) );
		AMP_Validation_Manager::reset_validation_results();
		$this->assertEquals( array(), AMP_Validation_Manager::$validation_results );
	}

	/**
	 * Test print_edit_form_validation_status
	 *
	 * @covers AMP_Validation_Manager::print_edit_form_validation_status()
	 */
	public function test_print_edit_form_validation_status() {
		add_theme_support( 'amp' );

		AMP_Invalid_URL_Post_Type::register();
		AMP_Validation_Error_Taxonomy::register();
		$this->set_capability();
		$post = $this->factory()->post->create_and_get();
		ob_start();
		AMP_Validation_Manager::print_edit_form_validation_status( $post );
		$output = ob_get_clean();

		$this->assertNotContains( 'notice notice-warning', $output );

		AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array(
					'code'            => AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE,
					'node_name'       => $this->disallowed_tag_name,
					'parent_name'     => 'div',
					'node_attributes' => array(),
					'sources'         => array(
						array(
							'type' => 'plugin',
							'name' => $this->plugin_name,
						),
					),
				),
			),
			get_permalink( $post->ID )
		);
		ob_start();
		AMP_Validation_Manager::print_edit_form_validation_status( $post );
		$output = ob_get_clean();

		$this->assertContains( 'notice notice-warning', $output );
		$this->assertContains( '<code>script</code>', $output );
	}

	/**
	 * Test source comments.
	 *
	 * @covers AMP_Validation_Manager::locate_sources()
	 * @covers AMP_Validation_Manager::parse_source_comment()
	 * @covers AMP_Validation_Manager::get_source_comment()
	 * @covers AMP_Validation_Manager::remove_source_comments()
	 */
	public function test_source_comments() {
		$source1 = array(
			'type'      => 'plugin',
			'name'      => 'foo',
			'shortcode' => 'test',
			'function'  => __FUNCTION__,
		);
		$source2 = array(
			'type'     => 'theme',
			'name'     => 'bar',
			'function' => __FUNCTION__,
			'hook'     => 'something',
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( implode(
			'',
			array(
				AMP_Validation_Manager::get_source_comment( $source1, true ),
				AMP_Validation_Manager::get_source_comment( $source2, true ),
				'<b id="test">Test</b>',
				AMP_Validation_Manager::get_source_comment( $source2, false ),
				AMP_Validation_Manager::get_source_comment( $source1, false ),
			)
		) );

		/**
		 * Comments.
		 *
		 * @var DOMComment[] $comments
		 */
		$comments = array();
		$xpath    = new DOMXPath( $dom );
		foreach ( $xpath->query( '//comment()' ) as $comment ) {
			$comments[] = $comment;
		}
		$this->assertCount( 4, $comments );

		$sources = AMP_Validation_Manager::locate_sources( $dom->getElementById( 'test' ) );
		$this->assertInternalType( 'array', $sources );
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

		AMP_Validation_Manager::remove_source_comments( $dom );
		$this->assertEquals( 0, $xpath->query( '//comment()' )->length );
	}

	/**
	 * Get block data.
	 *
	 * @see Test_AMP_Validation_Utils::test_add_block_source_comments()
	 * @return array
	 */
	public function get_block_data() {
		return array(
			'paragraph'    => array(
				"<!-- wp:paragraph -->\n<p>Latest posts:</p>\n<!-- /wp:paragraph -->",
				"<!--amp-source-stack {\"block_name\":\"core\/paragraph\",\"post_id\":{{post_id}},\"block_content_index\":0}-->\n<p>Latest posts:</p>\n<!--/amp-source-stack {\"block_name\":\"core\/paragraph\",\"post_id\":{{post_id}}}-->",
				array(
					'element' => 'p',
					'blocks'  => array( 'core/paragraph' ),
				),
			),
			'latest_posts' => array(
				'<!-- wp:latest-posts /-->',
				'<!--amp-source-stack {"block_name":"core\/latest-posts","post_id":{{post_id}},"block_content_index":0,"type":"plugin","name":"gutenberg","function":"render_block_core_latest_posts"}--><ul class="wp-block-latest-posts"><li><a href="{{url}}">{{title}}</a></li></ul><!--/amp-source-stack {"block_name":"core\/latest-posts","post_id":{{post_id}},"type":"plugin","name":"gutenberg","function":"render_block_core_latest_posts"}-->',
				array(
					'element' => 'ul',
					'blocks'  => array( 'core/latest-posts' ),
				),
			),
			'columns'      => array(
				"<!-- wp:columns -->\n<div class=\"wp-block-columns has-2-columns\">\n    <!-- wp:quote {\"layout\":\"column-1\",\"foo\":{\"bar\":1}} -->\n    <blockquote class=\"wp-block-quote layout-column-1\">\n        <p>A quotation!</p><cite>Famous</cite></blockquote>\n    <!-- /wp:quote -->\n\n    <!-- wp:html {\"layout\":\"column-2\"} -->\n    <div class=\"layout-column-2\">\n        <script>\n            document.write('Not allowed!');\n        </script>\n    </div>\n    <!-- /wp:html -->\n</div>\n<!-- /wp:columns -->",
				"<!--amp-source-stack {\"block_name\":\"core\/columns\",\"post_id\":{{post_id}},\"block_content_index\":0}-->\n<div class=\"wp-block-columns has-2-columns\">\n\n\n\n<!--amp-source-stack {\"block_name\":\"core\/quote\",\"post_id\":{{post_id}},\"block_content_index\":1,\"block_attrs\":{\"layout\":\"column-1\",\"foo\":{\"bar\":1}}}-->\n    <blockquote class=\"wp-block-quote layout-column-1\">\n        <p>A quotation!</p><cite>Famous</cite></blockquote>\n    <!--/amp-source-stack {\"block_name\":\"core\/quote\",\"post_id\":{{post_id}}}--><!--amp-source-stack {\"block_name\":\"core\/html\",\"post_id\":{{post_id}},\"block_content_index\":2,\"block_attrs\":{\"layout\":\"column-2\"}}-->\n    <div class=\"layout-column-2\">\n        <script>\n            document.write('Not allowed!');\n        </script>\n    </div>\n    <!--/amp-source-stack {\"block_name\":\"core\/html\",\"post_id\":{{post_id}}}--></div>\n<!--/amp-source-stack {\"block_name\":\"core\/columns\",\"post_id\":{{post_id}}}-->",
				array(
					'element' => 'blockquote',
					'blocks'  => array(
						'core/columns',
						'core/quote',
					),
				),
			),
		);
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
		if ( ( version_compare( get_bloginfo( 'version' ), '4.9', '<' ) ) ) {
			$this->markTestSkipped( 'The WP version is less than 4.9, so Gutenberg did not init.' );
		}

		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: Override ok.
		$this->assertInstanceOf( 'WP_Post', get_post() );

		$rendered_block = do_blocks( AMP_Validation_Manager::add_block_source_comments( $content ) );

		$expected = str_replace(
			array(
				'{{post_id}}',
				'{{title}}',
				'{{url}}',
			),
			array(
				$post->ID,
				get_the_title( $post ),
				get_permalink( $post ),
			),
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
	 * @covers AMP_Validation_Manager::wrap_widget_callbacks()
	 */
	public function test_wrap_widget_callbacks() {
		global $wp_registered_widgets, $_wp_sidebars_widgets;

		$widget_id = 'search-2';
		$this->assertArrayHasKey( $widget_id, $wp_registered_widgets );
		$this->assertInternalType( 'array', $wp_registered_widgets[ $widget_id ]['callback'] );
		$this->assertInstanceOf( 'WP_Widget_Search', $wp_registered_widgets[ $widget_id ]['callback'][0] );

		AMP_Validation_Manager::wrap_widget_callbacks();
		$this->assertInstanceOf( 'Closure', $wp_registered_widgets[ $widget_id ]['callback'] );

		$sidebar_id = 'amp-sidebar';
		register_sidebar( array(
			'id'           => $sidebar_id,
			'after_widget' => '</li>',
		) );
		$_wp_sidebars_widgets[ $sidebar_id ] = array( $widget_id ); // WPCS: global override ok.

		AMP_Theme_Support::start_output_buffering();
		dynamic_sidebar( $sidebar_id );
		$output = ob_get_clean();

		$this->assertStringStartsWith(
			'<!--amp-source-stack {"type":"core","name":"wp-includes","function":"WP_Widget_Search::display_callback","widget_id":"search-2"}--><li id="search-2"',
			$output
		);
		$this->assertStringEndsWith(
			'</li><!--/amp-source-stack {"type":"core","name":"wp-includes","function":"WP_Widget_Search::display_callback","widget_id":"search-2"}-->',
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
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
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

		add_action( $action_function_callback, '_amp_print_php_version_admin_notice' );
		add_action( $action_no_argument, array( $this, 'output_div' ) );
		add_action( $action_one_argument, array( $this, 'output_notice' ) );
		add_action( $action_two_arguments, array( $this, 'output_message' ), 10, 2 );
		add_action( $action_no_output, array( $this, 'get_string' ), 10, 2 );
		add_action( $action_no_tag_output, 'the_ID' );
		add_action( $action_core_output, 'edit_post_link' );
		add_action( $action_no_output, '__return_false' );

		// All of the callback functions remain as-is. They will only change for a given hook at the 'all' action.
		$this->assertEquals( 10, has_action( $action_no_tag_output, 'the_ID' ) );
		$this->assertEquals( 10, has_action( $action_no_output, array( $this, 'get_string' ) ) );
		$this->assertEquals( 10, has_action( $action_no_argument, array( $this, 'output_div' ) ) );
		$this->assertEquals( 10, has_action( $action_one_argument, array( $this, 'output_notice' ) ) );
		$this->assertEquals( 10, has_action( $action_two_arguments, array( $this, 'output_message' ) ) );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_function_callback );
		$output = ob_get_clean();
		$this->assertContains( '<div class="notice notice-error">', $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_argument );
		$output = ob_get_clean();
		$this->assertContains( '<div></div>', $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_one_argument, $notice );
		$output = ob_get_clean();
		$this->assertContains( $notice, $output );
		$this->assertContains( sprintf( '<div class="notice notice-warning"><p>%s</p></div>', $notice ), $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		AMP_Theme_Support::start_output_buffering();
		do_action( $action_two_arguments, $notice, get_the_ID() );
		$output = ob_get_clean();
		AMP_Theme_Support::start_output_buffering();
		self::output_message( $notice, get_the_ID() );
		$expected_output = ob_get_clean();
		$this->assertContains( $expected_output, $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp"', $output );

		// This action's callback doesn't output any HTML tags, so no HTML should be present.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_tag_output );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--amp-source-stack ', $output );
		$this->assertNotContains( '<!--/amp-source-stack ', $output );

		// This action's callback comes from core.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_core_output );
		$output = ob_get_clean();
		$this->assertContains( '<!--amp-source-stack {"type":"core","name":"wp-includes"', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"core","name":"wp-includes"', $output );

		// This action's callback doesn't echo any markup, so it shouldn't be wrapped in comments.
		AMP_Theme_Support::start_output_buffering();
		do_action( $action_no_output );
		$output = ob_get_clean();
		$this->assertNotContains( '<!--amp-source-stack ', $output );
		$this->assertNotContains( '<!--/amp-source-stack ', $output );

		// Ensure that nested actions output the expected stack, and that has_action() works as expected in spite of the function wrapping.
		$handle_outer_action = function() use ( $that, &$handle_outer_action, &$handle_inner_action ) {
			$that->assertEquals( 10, has_action( 'outer_action', $handle_outer_action ) );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
			do_action( 'inner_action' );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
		};
		$handle_inner_action = function() use ( $that, &$handle_outer_action, &$handle_inner_action ) {
			$that->assertEquals( 10, has_action( 'outer_action', $handle_outer_action ) );
			$that->assertEquals( 10, has_action( 'inner_action', $handle_inner_action ) );
			echo '<b>Hello</b>';
		};
		add_action( 'outer_action', $handle_outer_action );
		add_action( 'inner_action', $handle_inner_action );
		AMP_Theme_Support::start_output_buffering();
		do_action( 'outer_action' );
		$output = ob_get_clean();
		$this->assertEquals(
			implode( '', array(
				'<!--amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"outer_action"}-->',
				'<!--amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"inner_action"}-->',
				'<b>Hello</b>',
				'<!--/amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"inner_action"}-->',
				'<!--/amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","hook":"outer_action"}-->',
			) ),
			$output
		);
	}

	/**
	 * Test has_parameters_passed_by_reference.
	 *
	 * @covers AMP_Validation_Manager::has_parameters_passed_by_reference()
	 */
	public function test_has_parameters_passed_by_reference() {
		$tested_method = new ReflectionMethod( 'AMP_Validation_Manager', 'has_parameters_passed_by_reference' );
		$tested_method->setAccessible( true );
		$reflection_by_reference = new ReflectionFunction( 'wp_default_styles' );
		$reflection_by_value     = new ReflectionFunction( 'get_bloginfo' );

		$this->assertTrue( $tested_method->invoke( null, $reflection_by_reference ) );
		$this->assertFalse( $tested_method->invoke( null, $reflection_by_value ) );
	}

	/**
	 * Test decorate_shortcode_source.
	 *
	 * @covers AMP_Validation_Manager::add_validation_error_sourcing()
	 * @covers AMP_Validation_Manager::decorate_shortcode_source()
	 * @covers AMP_Validation_Manager::decorate_filter_source()
	 */
	public function test_decorate_shortcode_and_filter_source() {
		AMP_Validation_Manager::add_validation_error_sourcing();
		add_shortcode( 'test', function() {
			return '<b>test</b>';
		} );

		$filtered_content = apply_filters( 'the_content', 'before[test]after' );

		$source_json = '{"hook":"the_content","filter":true,"sources":[{"type":"core","name":"wp-includes","function":"WP_Embed::run_shortcode"},{"type":"core","name":"wp-includes","function":"WP_Embed::autoembed"}';
		if ( 9 === has_filter( 'the_content', 'do_blocks' ) ) {
			$source_json .= ',{"type":"plugin","name":"gutenberg","function":"gutenberg_wpautop"},{"type":"plugin","name":"amp","function":"AMP_Validation_Manager::add_block_source_comments"},{"type":"plugin","name":"gutenberg","function":"do_blocks"},{"type":"core","name":"wp-includes","function":"wptexturize"},{"type":"core","name":"wp-includes","function":"shortcode_unautop"}';
		} else {
			$source_json .= ',{"type":"core","name":"wp-includes","function":"wptexturize"},{"type":"core","name":"wp-includes","function":"wpautop"},{"type":"core","name":"wp-includes","function":"shortcode_unautop"}';
		}
		$source_json .= ',{"type":"core","name":"wp-includes","function":"prepend_attachment"},{"type":"core","name":"wp-includes","function":"wp_make_content_images_responsive"},{"type":"core","name":"wp-includes","function":"capital_P_dangit"},{"type":"core","name":"wp-includes","function":"do_shortcode"},{"type":"core","name":"wp-includes","function":"convert_smilies"}]}';

		$expected_content = implode( '', array(
			"<!--amp-source-stack $source_json-->",
			'<p>before<!--amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","shortcode":"test"}--><b>test</b><!--/amp-source-stack {"type":"plugin","name":"amp","function":"{closure}","shortcode":"test"}-->after</p>' . "\n",
			"<!--/amp-source-stack $source_json-->",
		) );

		$this->assertEquals(
			preg_split( '/(?=<)/', $expected_content ),
			preg_split( '/(?=<)/', $filtered_content )
		);
	}

	/**
	 * Test get_source
	 *
	 * @covers AMP_Validation_Manager::get_source()
	 */
	public function test_get_source() {
		$source = AMP_Validation_Manager::get_source( 'amp_after_setup_theme' );
		$this->assertEquals( 'amp', $source['name'] );
		$this->assertEquals( 'plugin', $source['type'] );

		$source = AMP_Validation_Manager::get_source( 'the_content' );
		$this->assertEquals( 'wp-includes', $source['name'] );
		$this->assertEquals( 'core', $source['type'] );
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
	 * Test wrapped_callback
	 *
	 * @covers AMP_Validation_Manager::wrapped_callback()
	 */
	public function test_wrapped_callback() {
		$test_string = "<b class='\nfoo\nbar\n'>Cool!</b>";
		$callback    = array(
			'function'      => function() use ( $test_string ) {
				echo $test_string; // WPCS: XSS OK.
			},
			'accepted_args' => 0,
			'source'        => array(
				'type' => 'plugin',
				'name' => 'amp',
				'hook' => 'bar',
			),
		);

		$wrapped_callback = AMP_Validation_Manager::wrapped_callback( $callback );
		$this->assertTrue( $wrapped_callback instanceof Closure );
		AMP_Theme_Support::start_output_buffering();
		call_user_func( $wrapped_callback );
		$output = ob_get_clean();

		$this->assertEquals( 'Closure', get_class( $wrapped_callback ) );
		$this->assertContains( $test_string, $output );
		$this->assertContains( '<!--amp-source-stack {"type":"plugin","name":"amp","hook":"bar"}', $output );
		$this->assertContains( '<!--/amp-source-stack {"type":"plugin","name":"amp","hook":"bar"}', $output );

		$callback = array(
			'function'      => array( $this, 'get_string' ),
			'accepted_args' => 0,
			'source'        => array(
				'type' => 'plugin',
				'name' => 'amp',
				'hook' => 'bar',
			),
		);

		$wrapped_callback = AMP_Validation_Manager::wrapped_callback( $callback );
		$this->assertTrue( $wrapped_callback instanceof Closure );
		AMP_Theme_Support::start_output_buffering();
		$result = call_user_func( $wrapped_callback );
		$output = ob_get_clean();
		$this->assertEquals( 'Closure', get_class( $wrapped_callback ) );
		$this->assertEquals( '', $output );
		$this->assertEquals( call_user_func( array( $this, 'get_string' ) ), $result );
		unset( $post );
	}

	/**
	 * Test wrap_buffer_with_source_comments.
	 *
	 * @covers \AMP_Validation_Manager::wrap_buffer_with_source_comments()
	 */
	public function test_wrap_buffer_with_source_comments() {
		$initial_content = '<html><body></body></html>';
		$this->assertEquals( $initial_content, AMP_Validation_Manager::wrap_buffer_with_source_comments( $initial_content ) );

		$earliest_source = array( 'plugin' => 'foo' );
		$latest_source   = array( 'theme' => 'bar' );

		// Doesn't use array_merge, as wrap_buffer_with_source_comments() accesses this array with indices like 1 or 2.
		AMP_Validation_Manager::$hook_source_stack[] = $earliest_source;
		AMP_Validation_Manager::$hook_source_stack[] = $latest_source;

		$wrapped_content = AMP_Validation_Manager::wrap_buffer_with_source_comments( $initial_content );
		$this->assertContains( $initial_content, $wrapped_content );
		$this->assertContains( '<!--amp-source-stack', $wrapped_content );
		$this->assertContains( '<!--/amp-source-stack', $wrapped_content );
		$this->assertContains( wp_json_encode( $latest_source ), $wrapped_content );
		$this->assertNotContains( wp_json_encode( $earliest_source ), $wrapped_content );
	}

	/**
	 * Test get_amp_validate_nonce.
	 *
	 * @covers \AMP_Validation_Manager::get_amp_validate_nonce()
	 */
	public function test_get_amp_validate_nonce() {
		$nonce = AMP_Validation_Manager::get_amp_validate_nonce();
		$this->assertTrue( is_string( $nonce ) );
		$this->assertEquals( 10, strlen( $nonce ) );
	}

	/**
	 * Test should_validate_response.
	 *
	 * @covers AMP_Validation_Manager::should_validate_response()
	 */
	public function test_should_validate_response() {
		global $post;
		$post = $this->factory()->post->create(); // WPCS: global override ok.
		$this->assertFalse( AMP_Validation_Manager::should_validate_response() );
		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = 1;
		$this->assertFalse( AMP_Validation_Manager::should_validate_response() );
		$this->set_capability();
		$this->assertTrue( AMP_Validation_Manager::should_validate_response() );
	}

	/**
	 * Test finalize_validation.
	 *
	 * @covers \AMP_Validation_Manager::finalize_validation()
	 */
	public function test_finalize_validation() {
		global $post, $show_admin_bar;

		$show_admin_bar = true; // WPCS: Global override OK.
		$dom            = new DOMDocument( '1.0' );
		$html           = '<html><body><div id="wp-admin-bar-amp-validity"><a href="#"></a></div><span id="amp-admin-bar-item-status-icon"></span><br></body></html>';
		$dom->loadHTML( $html );

		$validation_results                         = array(
			array(
				'error'       => array( 'code' => 'invalid_attribute' ),
				'sanitized'   => false,
				'slug'        => '98765b4',
				'term_status' => 0,
			),
		);
		AMP_Validation_Manager::$validation_results = $validation_results;

		// should_validate_response() will be false, so finalize_validation() won't append the _RESULTS comment.
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertNotContains( 'AMP_VALIDATION:{', $dom->documentElement->lastChild->nodeValue );

		// Ensure that should_validate_response() is true, so finalize_validation() will append the AMP_VALIDATION comment.
		$post = $this->factory()->post->create(); // WPCS: global override ok.
		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = 1;
		$this->set_capability();
		AMP_Validation_Manager::finalize_validation( $dom );
		$this->assertTrue( (bool) preg_match( '#AMP_VALIDATION:({.+})#s', $dom->documentElement->lastChild->nodeValue, $matches ) );
		$returned_valudation = json_decode( $matches[1], true );
		$this->assertEquals( $validation_results, $returned_valudation['results'] );
	}

	/**
	 * Test filter_sanitizer_args
	 *
	 * @covers AMP_Validation_Manager::filter_sanitizer_args()
	 */
	public function test_filter_sanitizer_args() {
		global $post;
		$post       = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$sanitizers = array(
			'AMP_Img_Sanitizer'      => array(),
			'AMP_Form_Sanitizer'     => array(),
			'AMP_Comments_Sanitizer' => array(),
		);

		$expected_callback   = self::TESTED_CLASS . '::add_validation_error';
		$filtered_sanitizers = AMP_Validation_Manager::filter_sanitizer_args( $sanitizers );
		foreach ( $filtered_sanitizers as $sanitizer => $args ) {
			$this->assertEquals( $expected_callback, $args['validation_error_callback'] );
		}
		remove_theme_support( 'amp' );
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

		$validation_error = array(
			'code' => 'example',
		);

		$validation = array(
			'results' => array(
				array(
					'error'     => $validation_error,
					'sanitized' => false,
				),
			),
		);

		$this->factory()->post->create();
		$filter = function() use ( $validation ) {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION:' . wp_json_encode( $validation )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$r = AMP_Validation_Manager::validate_after_plugin_activation();
		remove_filter( 'pre_http_request', $filter );
		$this->assertEquals( array( $validation_error ), $r );
		$this->assertEquals( array( $validation_error ), get_transient( AMP_Validation_Manager::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY ) );
	}

	/**
	 * Test for validate_url().
	 *
	 * @covers AMP_Validation_Manager::validate_url()
	 */
	public function test_validate_url() {
		add_theme_support( 'amp' );

		$validation_errors = array(
			array(
				'code' => 'example',
			),
		);

		// Test headers absent.
		$this->factory()->post->create();
		$filter = function() use ( $validation_errors ) {
			return array(
				'body'    => '',
				'headers' => array(),
			);
		};
		add_filter( 'pre_http_request', $filter );
		$r = AMP_Validation_Manager::validate_url( home_url( '/' ) );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'response_comment_absent', $r->get_error_code() );
		remove_filter( 'pre_http_request', $filter );

		// Test success.
		$that          = $this;
		$validated_url = home_url( '/foo/' );
		$filter        = function( $pre, $r, $url ) use ( $validation_errors, $validated_url, $that ) {
			unset( $pre, $r );
			$that->assertStringStartsWith(
				add_query_arg(
					AMP_Validation_Manager::VALIDATE_QUERY_VAR,
					'',
					$validated_url
				),
				$url
			);
			$validation = array( 'results' => array() );
			foreach ( $validation_errors as $error ) {
				$sanitized            = false;
				$validation['results'][] = compact( 'error', 'sanitized' );
			}

			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION:' . wp_json_encode( $validation )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$r = AMP_Validation_Manager::validate_url( $validated_url );
		$this->assertInternalType( 'array', $r );
		$this->assertEquals( $validated_url, $r['url'] );
		$this->assertEquals( $validation_errors, wp_list_pluck( $r['results'], 'error' ) );
		remove_filter( 'pre_http_request', $filter );
	}

	/**
	 * Test for print_plugin_notice()
	 *
	 * @covers AMP_Validation_Manager::print_plugin_notice()
	 */
	public function test_print_plugin_notice() {
		global $pagenow;
		ob_start();
		AMP_Validation_Manager::print_plugin_notice();
		$output = ob_get_clean();
		$this->assertEmpty( $output );
		$pagenow          = 'plugins.php'; // WPCS: global override ok.
		$_GET['activate'] = 'true';

		set_transient( AMP_Validation_Manager::PLUGIN_ACTIVATION_VALIDATION_ERRORS_TRANSIENT_KEY, array(
			array(
				'code'    => 'example',
				'sources' => array(
					array(
						'type' => 'plugin',
						'name' => 'foo-bar',
					),
				),
			),
		) );
		ob_start();
		AMP_Validation_Manager::print_plugin_notice();
		$output = ob_get_clean();
		$this->assertContains( 'Warning: The following plugin may be incompatible with AMP', $output );
		$this->assertContains( $this->plugin_name, $output );
		$this->assertContains( 'More details', $output );
		$this->assertContains( admin_url( 'edit.php' ), $output );
	}

	/**
	 * Test enqueue_block_validation.
	 *
	 * @covers AMP_Validation_Manager::enqueue_block_validation()
	 */
	public function test_enqueue_block_validation() {
		if ( ! function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$this->markTestSkipped( 'Gutenberg not available.' );
		}

		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$slug = 'amp-block-validation';
		$this->set_capability();
		AMP_Validation_Manager::enqueue_block_validation();

		$script        = wp_scripts()->registered[ $slug ];
		$inline_script = $script->extra['after'][1];
		$this->assertContains( 'js/amp-block-validation.js', $script->src );
		$this->assertEqualSets( array( 'underscore', AMP_Post_Meta_Box::BLOCK_ASSET_HANDLE ), $script->deps );
		$this->assertEquals( AMP__VERSION, $script->ver );
		$this->assertTrue( in_array( $slug, wp_scripts()->queue, true ) );
		$this->assertContains( 'ampBlockValidation.boot', $inline_script );
		$this->assertContains( AMP_Validation_Manager::VALIDITY_REST_FIELD_NAME, $inline_script );
		$this->assertContains( '"domain":"amp"', $inline_script );
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
		AMP_Theme_Support::register_content_embed_handlers();

		/** This filter is documented in wp-includes/post-template.php */
		$markup = apply_filters( 'the_content', $markup );
		$args   = array(
			'content_max_width'         => ! empty( $content_width ) ? $content_width : AMP_Post_Template::CONTENT_MAX_WIDTH,
			'validation_error_callback' => 'AMP_Validation_Manager::add_validation_error',
		);

		$results = AMP_Content_Sanitizer::sanitize( $markup, amp_get_content_sanitizers(), $args );
		return $results[0];
	}

	/**
	 * Test process_markup.
	 */
	public function test_process_markup() {
		$this->set_capability();
		$this->process_markup( $this->valid_amp_img );
		$this->assertEquals( array(), AMP_Validation_Manager::$validation_results );

		AMP_Validation_Manager::reset_validation_results();
		$video = '<video src="https://example.com/video">';
		$this->process_markup( $video );
		// This isn't valid AMP, but the sanitizer should convert it to an <amp-video>, without stripping anything.
		$this->assertEquals( array(), AMP_Validation_Manager::$validation_results );

		AMP_Validation_Manager::reset_validation_results();

		$this->process_markup( $this->disallowed_tag );
		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals( 'script', AMP_Validation_Manager::$validation_results[0]['error']['node_name'] );

		AMP_Validation_Manager::reset_validation_results();
		$disallowed_style = '<div style="display:none"></div>';
		$this->process_markup( $disallowed_style );
		$this->assertEquals( array(), AMP_Validation_Manager::$validation_results );

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
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'administrator',
		) ) );
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
	public function create_custom_post( $errors = array(), $url = null ) {
		if ( ! $url ) {
			$url = home_url( '/' );
		}

		return AMP_Invalid_URL_Post_Type::store_validation_errors( $errors, $url );
	}
}
