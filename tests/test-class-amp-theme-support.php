<?php
/**
 * Tests for Theme Support.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Tests for Theme Support.
 *
 * @covers AMP_Theme_Support
 */
class Test_AMP_Theme_Support extends WP_UnitTestCase {

	/**
	 * The name of the tested class.
	 *
	 * @var string
	 */
	const TESTED_CLASS = 'AMP_Theme_Support';

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		AMP_Validation_Manager::reset_validation_results();
		unset( $GLOBALS['current_screen'] );
		remove_theme_support( 'amp' );
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 *
	 * @global WP_Scripts $wp_scripts
	 */
	public function tearDown() {
		global $wp_scripts;
		$wp_scripts = null;
		parent::tearDown();
		AMP_Validation_Manager::reset_validation_results();
		remove_theme_support( 'amp' );
		remove_theme_support( 'custom-header' );
		$_REQUEST                = array(); // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
		$_SERVER['QUERY_STRING'] = '';
		unset( $_SERVER['REQUEST_URI'] );
		unset( $_SERVER['REQUEST_METHOD'] );
		unset( $GLOBALS['content_width'] );
		if ( isset( $GLOBALS['wp_customize'] ) ) {
			$GLOBALS['wp_customize']->stop_previewing_theme();
		}
		AMP_HTTP::$headers_sent = array();
	}

	/**
	 * Test init.
	 *
	 * @covers AMP_Theme_Support::init()
	 */
	public function test_init() {
		$_REQUEST['__amp_source_origin'] = 'foo';
		$_GET['__amp_source_origin']     = 'foo';
		AMP_Theme_Support::init();
		$this->assertFalse( has_action( 'widgets_init', array( self::TESTED_CLASS, 'register_widgets' ) ) );
		$this->assertFalse( has_action( 'wp', array( self::TESTED_CLASS, 'finish_init' ) ) );

		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		$this->assertEquals( 10, has_action( 'widgets_init', array( self::TESTED_CLASS, 'register_widgets' ) ) );
		$this->assertEquals( PHP_INT_MAX, has_action( 'wp', array( self::TESTED_CLASS, 'finish_init' ) ) );
	}

	/**
	 * Test add_theme_support(amp) with invalid args.
	 *
	 * @expectedIncorrectUsage add_theme_support
	 * @covers \AMP_Theme_Support::read_theme_support()
	 * @covers \AMP_Theme_Support::get_theme_support_args()
	 */
	public function test_read_theme_support_bad_args_array() {
		$args = array(
			'paired'            => false,
			'invalid_param_key' => array(),
		);
		add_theme_support( 'amp', $args );
		AMP_Theme_Support::read_theme_support();
		$this->assertTrue( current_theme_supports( 'amp' ) );
		$this->assertEquals( $args, AMP_Theme_Support::get_theme_support_args() );
	}

	/**
	 * Test add_theme_support(amp) with invalid args.
	 *
	 * @expectedIncorrectUsage add_theme_support
	 * @covers \AMP_Theme_Support::read_theme_support()
	 */
	public function test_read_theme_support_bad_available_callback() {
		add_theme_support( 'amp', array(
			'available_callback' => function() {
				return (bool) wp_rand( 0, 1 );
			},
		) );
		AMP_Theme_Support::read_theme_support();
		$this->assertTrue( current_theme_supports( 'amp' ) );
	}

	/**
	 * Test read_theme_support, get_theme_support_args, and is_support_added_via_option.
	 *
	 * @covers \AMP_Theme_Support::read_theme_support()
	 * @covers \AMP_Theme_Support::is_support_added_via_option()
	 * @covers \AMP_Theme_Support::get_theme_support_args()
	 */
	public function test_read_theme_support_and_support_args() {

		// Test with option set, but some configs supplied via theme support.
		AMP_Options_Manager::update_option( 'theme_support', 'native' ); // Will be ignored since theme support flag set.
		$args = array(
			'templates_supported' => 'all',
			'paired'              => true,
			'comments_live_list'  => true,
		);
		add_theme_support( 'amp', $args );
		AMP_Theme_Support::read_theme_support();
		$this->assertEquals( $args, AMP_Theme_Support::get_theme_support_args() );
		$this->assertFalse( AMP_Theme_Support::is_support_added_via_option() );
		$this->assertTrue( current_theme_supports( 'amp' ) );

		add_theme_support( 'amp' );
		$this->assertTrue( current_theme_supports( 'amp' ) );
		$this->assertFalse( AMP_Theme_Support::is_support_added_via_option() );
		$this->assertEquals( array( 'paired' => false ), AMP_Theme_Support::get_theme_support_args() );

		remove_theme_support( 'amp' );
		AMP_Options_Manager::update_option( 'theme_support', 'native' ); // Will be ignored since theme support flag set.
		AMP_Theme_Support::read_theme_support();
		$this->assertTrue( AMP_Theme_Support::is_support_added_via_option() );
		$this->assertTrue( current_theme_supports( 'amp' ) );

		remove_theme_support( 'amp' );
		AMP_Options_Manager::update_option( 'theme_support', 'disabled' );
		$_GET[ AMP_Validation_Manager::VALIDATE_QUERY_VAR ] = AMP_Validation_Manager::get_amp_validate_nonce();
		AMP_Theme_Support::read_theme_support();
		$this->assertTrue( AMP_Theme_Support::is_support_added_via_option() );
		$this->assertTrue( get_theme_support( 'amp' ) );
	}

	/**
	 * Test that init finalization, including amphtml link is added at the right time.
	 *
	 * @covers AMP_Theme_Support::finish_init()
	 */
	public function test_finish_init() {
		$post_id = $this->factory()->post->create( array( 'post_title' => 'Test' ) );
		add_theme_support( 'amp', array(
			'paired'       => true,
			'template_dir' => 'amp',
		) );

		// Test paired mode singular, where not on endpoint that it causes amphtml link to be added.
		remove_action( 'wp_head', 'amp_add_amphtml_link' );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertFalse( is_amp_endpoint() );
		AMP_Theme_Support::finish_init();
		$this->assertEquals( 10, has_action( 'wp_head', 'amp_add_amphtml_link' ) );

		// Test paired mode homepage, where still not on endpoint that it causes amphtml link to be added.
		remove_action( 'wp_head', 'amp_add_amphtml_link' );
		$this->go_to( home_url() );
		$this->assertFalse( is_amp_endpoint() );
		AMP_Theme_Support::finish_init();
		$this->assertEquals( 10, has_action( 'wp_head', 'amp_add_amphtml_link' ) );

		// Test canonical, so amphtml link is not added and init finalizes.
		remove_action( 'wp_head', 'amp_add_amphtml_link' );
		add_theme_support( 'amp', array(
			'paired'       => false,
			'template_dir' => 'amp',
		) );
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( is_amp_endpoint() );
		AMP_Theme_Support::finish_init();
		$this->assertFalse( has_action( 'wp_head', 'amp_add_amphtml_link' ) );
		$this->assertEquals( 10, has_filter( 'index_template_hierarchy', array( 'AMP_Theme_Support', 'filter_amp_template_hierarchy' ) ), 'Expected add_amp_template_filters to have been called since template_dir is not empty' );
		$this->assertEquals( 20, has_action( 'wp_head', 'amp_add_generator_metadata' ), 'Expected add_hooks to have been called' );
	}

	/**
	 * Test ensure_proper_amp_location for canonical.
	 *
	 * @covers AMP_Theme_Support::ensure_proper_amp_location()
	 */
	public function test_ensure_proper_amp_location_canonical() {
		add_theme_support( 'amp' );
		$e = null;

		// Already canonical.
		$_SERVER['REQUEST_URI'] = '/foo/bar/';
		$this->assertFalse( AMP_Theme_Support::ensure_proper_amp_location( false ) );

		// URL query param.
		$_GET[ amp_get_slug() ] = '';
		$_SERVER['REQUEST_URI'] = add_query_arg( amp_get_slug(), '', '/foo/bar' );
		try {
			$this->assertTrue( AMP_Theme_Support::ensure_proper_amp_location( false ) );
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertTrue( isset( $e ) ); // wp_safe_redirect() modifies the headers, and causes an error.
		$this->assertContains( 'headers already sent', $e->getMessage() );
		$e = null;

		// Endpoint.
		unset( $_GET[ amp_get_slug() ] );
		set_query_var( amp_get_slug(), '' );
		$_SERVER['REQUEST_URI'] = '/2016/01/24/foo/amp/';
		try {
			$this->assertTrue( AMP_Theme_Support::ensure_proper_amp_location( false ) );
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertContains( 'headers already sent', $e->getMessage() );
		$e = null;
	}

	/**
	 * Test ensure_proper_amp_location for paired.
	 *
	 * @covers AMP_Theme_Support::ensure_proper_amp_location()
	 */
	public function test_ensure_proper_amp_location_paired() {
		add_theme_support( 'amp', array(
			'template_dir' => './',
		) );
		$e = null;

		// URL query param, no redirection.
		$_GET[ amp_get_slug() ] = '';
		$_SERVER['REQUEST_URI'] = add_query_arg( amp_get_slug(), '', '/foo/bar' );
		$this->assertFalse( AMP_Theme_Support::ensure_proper_amp_location( false ) );

		// Endpoint, redirect.
		unset( $_GET[ amp_get_slug() ] );
		set_query_var( amp_get_slug(), '' );
		$_SERVER['REQUEST_URI'] = '/2016/01/24/foo/amp/';
		try {
			$this->assertTrue( AMP_Theme_Support::ensure_proper_amp_location( false ) );
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertContains( 'headers already sent', $e->getMessage() );
	}

	/**
	 * Test redirect_ampless_url.
	 *
	 * @covers AMP_Theme_Support::redirect_ampless_url()
	 */
	public function test_redirect_ampless_url() {
		$e = null;

		// Try AMP URL param.
		$_SERVER['REQUEST_URI'] = add_query_arg( amp_get_slug(), '', '/foo/bar' );
		try {
			$this->assertTrue( AMP_Theme_Support::redirect_ampless_url() );
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertTrue( isset( $e ) );
		$this->assertContains( 'headers already sent', $e->getMessage() );
		$e = null;

		// Try AMP URL endpoint.
		$_SERVER['REQUEST_URI'] = '/2016/01/24/foo/amp/';
		try {
			$this->assertTrue( AMP_Theme_Support::redirect_ampless_url() );
		} catch ( Exception $exception ) {
			$e = $exception;
		}
		$this->assertTrue( isset( $e ) ); // wp_safe_redirect() modifies the headers, and causes an error.
		$this->assertContains( 'headers already sent', $e->getMessage() );
		$e = null;

		// Make sure that if the URL doesn't have AMP that there should be no redirect.
		$_SERVER['REQUEST_URI'] = '/foo/bar';
		$this->assertFalse( AMP_Theme_Support::redirect_ampless_url() );
	}

	/**
	 * Test is_paired_available.
	 *
	 * @covers AMP_Theme_Support::is_paired_available()
	 */
	public function test_is_paired_available() {

		// Establish initial state.
		$post_id = $this->factory()->post->create( array( 'post_title' => 'Test' ) );
		remove_theme_support( 'amp' );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );

		// Paired support is not available if theme support is not present or canonical.
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		add_theme_support( 'amp' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );

		// Paired mode is available once template_dir is supplied.
		add_theme_support( 'amp', array(
			'template_dir' => 'amp-templates',
		) );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		// Paired mode not available when post does not support AMP.
		add_filter( 'amp_skip_post', '__return_true' );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
		$this->assertTrue( is_singular() );
		query_posts( array( 's' => 'test' ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );
		remove_filter( 'amp_skip_post', '__return_true' );

		// Check that mode=paired works.
		add_theme_support( 'amp', array(
			'paired' => true,
		) );
		add_filter( 'amp_supportable_templates', function( $supportable_templates ) {
			$supportable_templates['is_singular']['supported'] = true;
			$supportable_templates['is_search']['supported']   = false;
			return $supportable_templates;
		} );
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_singular() );
		$this->assertTrue( AMP_Theme_Support::is_paired_available() );

		query_posts( array( 's' => $post_id ) ); // phpcs:ignore
		$this->assertTrue( is_search() );
		$this->assertFalse( AMP_Theme_Support::is_paired_available() );
	}

	/**
	 * Test is_customize_preview_iframe.
	 *
	 * @covers AMP_Theme_Support::is_customize_preview_iframe()
	 */
	public function test_is_customize_preview_iframe() {
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$GLOBALS['wp_customize'] = new WP_Customize_Manager();
		$this->assertFalse( AMP_Theme_Support::is_customize_preview_iframe() );
		$GLOBALS['wp_customize'] = new WP_Customize_Manager( array(
			'messenger_channel' => 'baz',
		) );
		$this->assertFalse( AMP_Theme_Support::is_customize_preview_iframe() );
		$GLOBALS['wp_customize']->start_previewing_theme();
		$this->assertTrue( AMP_Theme_Support::is_customize_preview_iframe() );
	}

	/**
	 * Test add_amp_template_filters.
	 *
	 * @covers AMP_Theme_Support::add_amp_template_filters()
	 */
	public function test_add_amp_template_filters() {
		$template_types = array(
			'paged',
			'index',
			'404',
			'archive',
			'author',
			'category',
		);
		AMP_Theme_Support::add_amp_template_filters();
		foreach ( $template_types as $template_type ) {
			$this->assertEquals( 10, has_filter( "{$template_type}_template_hierarchy", array( self::TESTED_CLASS, 'filter_amp_template_hierarchy' ) ) );
		}
	}

	/**
	 * Test validate_non_amp_theme.
	 *
	 * @global WP_Widget_Factory $wp_widget_factory
	 * @global WP_Scripts $wp_scripts
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_validate_non_amp_theme() {
		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		ob_start();
		?>
		<!DOCTYPE html>
		<html lang="en-US" class="no-js">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width">
				<?php wp_head(); ?>
			</head>
			<body>
				<?php wp_print_scripts( 'amp-mathml' ); ?>
			</body>
		</html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html );

		// Invalid viewport meta tag is not present.
		$this->assertNotContains( '<meta name="viewport" content="width=device-width">', $sanitized_html );

		// Correct viewport meta tag was added.
		$this->assertContains( '<meta name="viewport" content="width=device-width,minimum-scale=1">', $sanitized_html );

		// MathML script was added.
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-mathml-latest.js" async custom-element="amp-mathml"></script>', $sanitized_html ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}

	/**
	 * Test incorrect usage for get_template_availability.
	 *
	 * @expectedIncorrectUsage AMP_Theme_Support::get_template_availability
	 * @covers AMP_Theme_Support::get_template_availability()
	 */
	public function test_incorrect_usage_get_template_availability() {
		global $wp_query;

		// Test no query available.
		$wp_query     = null; // WPCS: override ok.
		$availability = AMP_Theme_Support::get_template_availability();
		$this->assertInternalType( 'array', $availability );
		$this->assertEquals( array( 'no_query_available' ), $availability['errors'] );
		$this->assertFalse( $availability['supported'] );
		$this->assertNull( $availability['immutable'] );
		$this->assertNull( $availability['template'] );

		// Test no theme support.
		remove_theme_support( 'amp' );
		$this->go_to( get_permalink( $this->factory()->post->create() ) );
		$availability = AMP_Theme_Support::get_template_availability();
		$this->assertEquals( array( 'no_theme_support' ), $availability['errors'] );
		$this->assertFalse( $availability['supported'] );
		$this->assertNull( $availability['immutable'] );
		$this->assertNull( $availability['template'] );
	}

	/**
	 * Test get_template_availability with available_callback.
	 *
	 * @expectedIncorrectUsage add_theme_support
	 * @covers AMP_Theme_Support::get_template_availability()
	 */
	public function test_get_template_availability_with_available_callback() {
		$this->go_to( get_permalink( $this->factory()->post->create() ) );
		add_theme_support( 'amp', array(
			'available_callback' => '__return_true',
		) );
		AMP_Theme_Support::init();
		$availability = AMP_Theme_Support::get_template_availability();
		$this->assertEquals(
			$availability,
			array(
				'supported' => true,
				'immutable' => true,
				'template'  => null,
				'errors'    => array(),
			)
		);

		add_theme_support( 'amp', array(
			'available_callback' => '__return_false',
		) );
		AMP_Theme_Support::init();
		$availability = AMP_Theme_Support::get_template_availability();
		$this->assertEquals(
			$availability,
			array(
				'supported' => false,
				'immutable' => true,
				'template'  => null,
				'errors'    => array( 'available_callback' ),
			)
		);
	}

	/**
	 * Test get_template_availability.
	 *
	 * @covers AMP_Theme_Support::get_template_availability()
	 */
	public function test_get_template_availability() {
		global $wp_query;
		$post_id = $this->factory()->post->create();
		query_posts( array( 'p' => $post_id ) ); // phpcs:ignore

		// Test successful match of singular template.
		$this->assertTrue( is_singular() );
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		add_theme_support( 'amp' );
		$availability = AMP_Theme_Support::get_template_availability();
		$this->assertEmpty( $availability['errors'] );
		$this->assertTrue( $availability['supported'] );
		$this->assertFalse( $availability['immutable'] );
		$this->assertEquals( 'is_singular', $availability['template'] );

		// Test successful match when passing WP_Query and WP_Post into method.
		$query        = $wp_query;
		$wp_query     = null; // WPCS: override ok.
		$availability = AMP_Theme_Support::get_template_availability( $query );
		$this->assertTrue( $availability['supported'] );
		$this->assertEquals( 'is_singular', $availability['template'] );
		$availability = AMP_Theme_Support::get_template_availability( get_post( $post_id ) );
		$this->assertTrue( $availability['supported'] );
		$this->assertEquals( 'is_singular', $availability['template'] );
		$this->assertNull( $wp_query ); // Make sure it is reset.

		// Test nested hierarchy.
		AMP_Options_Manager::update_option( 'supported_templates', array( 'is_special', 'is_custom' ) );
		add_filter( 'amp_supportable_templates', function( $templates ) {
			$templates['is_single']  = array(
				'label'     => 'Single post',
				'supported' => false,
				'parent'    => 'is_singular',
			);
			$templates['is_special'] = array(
				'label'    => 'Special post',
				'parent'   => 'is_single',
				'callback' => function( WP_Query $query ) {
					return $query->is_singular() && 'special' === get_post( $query->get_queried_object_id() )->post_name;
				},
			);
			$templates['is_page']    = array(
				'label'     => 'Page',
				'supported' => true,
				'parent'    => 'is_singular',
			);
			$templates['is_custom']  = array(
				'label'    => 'Custom',
				'callback' => function( WP_Query $query ) {
					return false !== $query->get( 'custom', false );
				},
			);
			return $templates;
		} );
		add_filter( 'query_vars', function( $vars ) {
			$vars[] = 'custom';
			return $vars;
		} );

		$availability = AMP_Theme_Support::get_template_availability( get_post( $post_id ) );
		$this->assertFalse( $availability['supported'] );
		$this->assertTrue( $availability['immutable'] );
		$this->assertEquals( 'is_single', $availability['template'] );

		$special_id   = $this->factory()->post->create( array(
			'post_type' => 'post',
			'post_name' => 'special',
		) );
		$availability = AMP_Theme_Support::get_template_availability( get_post( $special_id ) );
		$this->assertTrue( $availability['supported'] );
		$this->assertEquals( 'is_special', $availability['template'] );
		$this->assertFalse( $availability['immutable'] );

		$availability = AMP_Theme_Support::get_template_availability( $this->factory()->post->create_and_get( array( 'post_type' => 'page' ) ) );
		$this->assertFalse( $availability['supported'] );
		$this->assertEquals( array( 'post-type-support' ), $availability['errors'] );
		$this->assertEquals( 'is_page', $availability['template'] );
		add_post_type_support( 'page', 'amp' );
		$availability = AMP_Theme_Support::get_template_availability( $this->factory()->post->create_and_get( array( 'post_type' => 'page' ) ) );
		$this->assertTrue( $availability['supported'] );

		// Test custom.
		$this->go_to( '/?custom=1' );
		$availability = AMP_Theme_Support::get_template_availability();
		$this->assertTrue( $availability['supported'] );
		$this->assertEquals( 'is_custom', $availability['template'] );
	}

	/**
	 * Test get_supportable_templates.
	 *
	 * @covers AMP_Theme_Support::get_supportable_templates()
	 */
	public function test_get_supportable_templates() {

		register_taxonomy( 'accolade', 'post', array(
			'public' => true,
		) );
		register_taxonomy( 'complaint', 'post', array(
			'public' => false,
		) );
		register_post_type( 'announcement', array(
			'public'      => true,
			'has_archive' => true,
		) );

		// Test default case with non-static front page.
		update_option( 'show_on_front', 'posts' );
		AMP_Options_Manager::update_option( 'all_templates_supported', true );
		$supportable_templates = AMP_Theme_Support::get_supportable_templates();
		foreach ( $supportable_templates as $id => $supportable_template ) {
			$this->assertFalse( is_numeric( $id ) );
			$this->assertArrayHasKey( 'label', $supportable_template, "$id has label" );
			$this->assertTrue( $supportable_template['supported'] );
			$this->assertFalse( $supportable_template['immutable'] );
		}
		$this->assertArrayHasKey( 'is_singular', $supportable_templates );
		$this->assertArrayNotHasKey( 'is_front_page', $supportable_templates );
		$this->assertArrayHasKey( 'is_home', $supportable_templates );
		$this->assertArrayNotHasKey( 'parent', $supportable_templates['is_home'] );

		// Test common templates.
		$this->assertArrayHasKey( 'is_singular', $supportable_templates );
		$this->assertArrayHasKey( 'is_archive', $supportable_templates );
		$this->assertArrayHasKey( 'is_author', $supportable_templates );
		$this->assertArrayHasKey( 'is_date', $supportable_templates );
		$this->assertArrayHasKey( 'is_search', $supportable_templates );
		$this->assertArrayHasKey( 'is_404', $supportable_templates );
		$this->assertArrayHasKey( 'is_category', $supportable_templates );
		$this->assertArrayHasKey( 'is_tag', $supportable_templates );
		$this->assertArrayHasKey( 'is_tax[accolade]', $supportable_templates );
		$this->assertEquals( 'is_archive', $supportable_templates['is_tax[accolade]']['parent'] );
		$this->assertArrayNotHasKey( 'is_tax[complaint]', $supportable_templates );
		$this->assertArrayHasKey( 'is_post_type_archive[announcement]', $supportable_templates );
		$this->assertEquals( 'is_archive', $supportable_templates['is_post_type_archive[announcement]']['parent'] );

		// Test static homepage and page for posts.
		$page_on_front  = $this->factory()->post->create( array( 'post_type' => 'page' ) );
		$page_for_posts = $this->factory()->post->create( array( 'post_type' => 'page' ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_for_posts', $page_for_posts );
		update_option( 'page_on_front', $page_on_front );
		$supportable_templates = AMP_Theme_Support::get_supportable_templates();
		foreach ( $supportable_templates as $id => $supportable_template ) {
			$this->assertFalse( is_numeric( $id ) );
			$this->assertArrayHasKey( 'label', $supportable_template, "$id has label" );
		}
		$this->assertArrayHasKey( 'is_front_page', $supportable_templates );
		$this->assertArrayHasKey( 'parent', $supportable_templates['is_front_page'] );
		$this->assertEquals( 'is_singular', $supportable_templates['is_front_page']['parent'] );

		// Test inclusion of custom template, forcing category to be not-supported, and singular to be supported.
		add_filter( 'amp_supportable_templates', function( $templates ) {
			$templates['is_category']['supported'] = false;
			$templates['is_singular']['supported'] = true;

			$templates['is_custom'] = array(
				'label'    => 'Custom',
				'callback' => function( WP_Query $query ) {
					return false !== $query->get( 'custom', false );
				},
			);
			return $templates;
		} );
		$supportable_templates = AMP_Theme_Support::get_supportable_templates();
		$this->assertTrue( $supportable_templates['is_category']['immutable'] );
		$this->assertFalse( $supportable_templates['is_category']['supported'] );
		$this->assertFalse( $supportable_templates['is_category']['user_supported'] );
		$this->assertTrue( $supportable_templates['is_singular']['immutable'] );
		$this->assertTrue( $supportable_templates['is_singular']['supported'] );
		$this->assertTrue( $supportable_templates['is_singular']['user_supported'] );
		$this->assertArrayHasKey( 'is_custom', $supportable_templates );
		remove_all_filters( 'amp_supportable_templates' );

		// Test supporting templates by theme support args: all.
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		add_theme_support( 'amp', array(
			'templates_supported' => 'all',
		) );
		AMP_Theme_Support::init();
		$supportable_templates = AMP_Theme_Support::get_supportable_templates();
		foreach ( $supportable_templates as $supportable_template ) {
			$this->assertTrue( $supportable_template['supported'] );
			$this->assertTrue( $supportable_template['immutable'] );
		}

		// Test supporting templates by theme support args: selective templates.
		AMP_Options_Manager::update_option( 'all_templates_supported', false );
		add_theme_support( 'amp', array(
			'templates_supported' => array(
				'is_date'   => true,
				'is_author' => false,
			),
		) );
		AMP_Theme_Support::init();
		$supportable_templates = AMP_Theme_Support::get_supportable_templates();
		$this->assertTrue( $supportable_templates['is_date']['supported'] );
		$this->assertTrue( $supportable_templates['is_date']['immutable'] );
		$this->assertFalse( $supportable_templates['is_author']['supported'] );
		$this->assertTrue( $supportable_templates['is_author']['immutable'] );
		$this->assertTrue( $supportable_templates['is_singular']['supported'] );
		$this->assertFalse( $supportable_templates['is_singular']['immutable'] );
		$this->assertFalse( $supportable_templates['is_category']['supported'] );
		$this->assertFalse( $supportable_templates['is_category']['immutable'] );
	}

	/**
	 * Test add_hooks.
	 *
	 * @covers AMP_Theme_Support::add_hooks()
	 */
	public function test_add_hooks() {
		AMP_Theme_Support::add_hooks();
		$this->assertFalse( has_action( 'wp_head', 'wp_post_preview_js' ) );
		$this->assertFalse( has_action( 'wp_head', 'print_emoji_detection_script' ) );
		$this->assertFalse( has_action( 'wp_print_styles', 'print_emoji_styles' ) );
		$this->assertFalse( has_action( 'wp_head', 'wp_oembed_add_host_js' ) );

		$this->assertEquals( 20, has_action( 'wp_head', 'amp_add_generator_metadata' ) );
		$this->assertEquals( 10, has_action( 'wp_enqueue_scripts', array( self::TESTED_CLASS, 'enqueue_assets' ) ) );

		$this->assertEquals( 1000, has_action( 'wp_enqueue_scripts', array( self::TESTED_CLASS, 'dequeue_customize_preview_scripts' ) ) );
		$this->assertEquals( 10, has_filter( 'customize_partial_render', array( self::TESTED_CLASS, 'filter_customize_partial_render' ) ) );
		$this->assertEquals( 10, has_action( 'wp_footer', 'amp_print_analytics' ) );
		$this->assertEquals( 10, has_action( 'admin_bar_init', array( self::TESTED_CLASS, 'init_admin_bar' ) ) );
		$priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.PHP.NewConstants.php_int_minFound
		$this->assertEquals( $priority, has_action( 'template_redirect', array( self::TESTED_CLASS, 'start_output_buffering' ) ) );

		$this->assertEquals( PHP_INT_MAX, has_filter( 'wp_list_comments_args', array( self::TESTED_CLASS, 'set_comments_walker' ) ) );
		$this->assertEquals( 10, has_filter( 'comment_form_defaults', array( self::TESTED_CLASS, 'filter_comment_form_defaults' ) ) );
		$this->assertEquals( 10, has_filter( 'comment_reply_link', array( self::TESTED_CLASS, 'filter_comment_reply_link' ) ) );
		$this->assertEquals( 10, has_filter( 'cancel_comment_reply_link', array( self::TESTED_CLASS, 'filter_cancel_comment_reply_link' ) ) );
		$this->assertEquals( 100, has_action( 'comment_form', array( self::TESTED_CLASS, 'amend_comment_form' ) ) );
		$this->assertFalse( has_action( 'comment_form', 'wp_comment_form_unfiltered_html_nonce' ) );
		$this->assertEquals( PHP_INT_MAX, has_filter( 'get_header_image_tag', array( self::TESTED_CLASS, 'amend_header_image_with_video_header' ) ) );
	}

	/**
	 * Test add_hooks() when admin bar is turned off.
	 */
	public function test_add_hooks_no_admin_bar() {
		AMP_Options_Manager::update_option( 'disable_admin_bar', true );
		AMP_Theme_Support::add_hooks();
		$this->assertEquals( 100, has_filter( 'show_admin_bar', '__return_false' ) );
	}

	/**
	 * Test register_widgets().
	 *
	 * @covers AMP_Theme_Support::register_widgets()
	 * @global WP_Widget_Factory $wp_widget_factory
	 */
	public function test_register_widgets() {
		global $wp_widget_factory;
		remove_all_actions( 'widgets_init' );
		$wp_widget_factory->widgets = array();
		wp_widgets_init();
		AMP_Theme_Support::register_widgets();

		$this->assertArrayNotHasKey( 'WP_Widget_Categories', $wp_widget_factory->widgets );
		$this->assertArrayHasKey( 'AMP_Widget_Categories', $wp_widget_factory->widgets );
	}

	/**
	 * Test register_content_embed_handlers.
	 *
	 * @covers AMP_Theme_Support::register_content_embed_handlers()
	 * @global int $content_width
	 */
	public function test_register_content_embed_handlers() {
		global $content_width;
		$content_width  = 1234;
		$embed_handlers = AMP_Theme_Support::register_content_embed_handlers();
		foreach ( $embed_handlers as $embed_handler ) {
			$this->assertTrue( is_subclass_of( $embed_handler, 'AMP_Base_Embed_Handler' ) );
			$reflection = new ReflectionObject( $embed_handler );
			$args       = $reflection->getProperty( 'args' );
			$args->setAccessible( true );
			$property = $args->getValue( $embed_handler );
			$this->assertEquals( $content_width, $property['content_max_width'] );
		}
	}

	/**
	 * Test set_comments_walker.
	 *
	 * @covers AMP_Theme_Support::set_comments_walker()
	 */
	public function test_set_comments_walker() {
		$args = AMP_Theme_Support::set_comments_walker( array(
			'walker' => null,
		) );
		$this->assertInstanceOf( 'AMP_Comment_Walker', $args['walker'] );
	}

	/**
	 * Test amend_comment_form().
	 *
	 * @covers AMP_Theme_Support::amend_comment_form()
	 */
	public function test_amend_comment_form() {
		$post_id = $this->factory()->post->create();
		$this->go_to( get_permalink( $post_id ) );
		$this->assertTrue( is_singular() );

		// Test native AMP.
		add_theme_support( 'amp' );
		$this->assertTrue( amp_is_canonical() );
		ob_start();
		AMP_Theme_Support::amend_comment_form();
		$output = ob_get_clean();
		$this->assertNotContains( '<input type="hidden" name="redirect_to"', $output );
		$this->assertContains( '<div submit-success>', $output );
		$this->assertContains( '<div submit-error>', $output );

		// Test paired AMP.
		add_theme_support( 'amp', array(
			'template_dir' => 'amp-templates',
		) );
		$this->assertFalse( amp_is_canonical() );
		ob_start();
		AMP_Theme_Support::amend_comment_form();
		$output = ob_get_clean();
		$this->assertContains( '<input type="hidden" name="redirect_to"', $output );
		$this->assertContains( '<div submit-success>', $output );
		$this->assertContains( '<div submit-error>', $output );
	}

	/**
	 * Test filter_amp_template_hierarchy.
	 *
	 * @covers AMP_Theme_Support::filter_amp_template_hierarchy()
	 */
	public function test_filter_amp_template_hierarchy() {
		$template_dir = 'amp-templates';
		add_theme_support( 'amp', array(
			'template_dir' => $template_dir,
		) );
		$templates          = array(
			'single-post-example.php',
			'single-post.php',
			'single.php',
		);
		$filtered_templates = AMP_Theme_Support::filter_amp_template_hierarchy( $templates );

		$expected_templates = array();
		foreach ( $templates as $template ) {
			$expected_templates[] = $template_dir . '/' . $template;
			$expected_templates[] = $template;
		}

		$this->assertEquals( $expected_templates, $filtered_templates );
	}

	/**
	 * Test get_current_canonical_url.
	 *
	 * @covers AMP_Theme_Support::get_current_canonical_url()
	 */
	public function test_get_current_canonical_url() {
		global $post, $wp;
		$home_url = home_url( '/' );
		$this->assertEquals( $home_url, AMP_Theme_Support::get_current_canonical_url() );

		$added_query_vars = array(
			'foo' => 'bar',
		);
		$wp->query_vars   = $added_query_vars;
		$this->assertEquals( add_query_arg( $added_query_vars, $home_url ), AMP_Theme_Support::get_current_canonical_url() );

		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$this->go_to( get_permalink( $post ) );
		$this->assertEquals( wp_get_canonical_url(), AMP_Theme_Support::get_current_canonical_url() );

	}

	/**
	 * Test get_comment_form_state_id.
	 *
	 * @covers AMP_Theme_Support::get_comment_form_state_id()
	 */
	public function test_get_comment_form_state_id() {
		$post_id = 54;
		$this->assertEquals( 'commentform_post_' . $post_id, AMP_Theme_Support::get_comment_form_state_id( $post_id ) );
		$post_id = 91542;
		$this->assertEquals( 'commentform_post_' . $post_id, AMP_Theme_Support::get_comment_form_state_id( $post_id ) );
	}

	/**
	 * Test filter_comment_form_defaults.
	 *
	 * @covers AMP_Theme_Support::filter_comment_form_defaults()
	 */
	public function test_filter_comment_form_defaults() {
		global $post;
		$post     = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$defaults = AMP_Theme_Support::filter_comment_form_defaults( array(
			'title_reply_to'      => 'Reply To',
			'title_reply'         => 'Reply',
			'cancel_reply_before' => '',
			'title_reply_before'  => '',
		) );
		$this->assertContains( AMP_Theme_Support::get_comment_form_state_id( get_the_ID() ), $defaults['title_reply_before'] );
		$this->assertContains( 'replyToName ?', $defaults['title_reply_before'] );
		$this->assertContains( '</span>', $defaults['cancel_reply_before'] );
	}

	/**
	 * Test filter_comment_reply_link.
	 *
	 * @covers AMP_Theme_Support::filter_comment_reply_link()
	 */
	public function test_filter_comment_reply_link() {
		global $post;
		$post          = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$comment       = $this->factory()->comment->create_and_get();
		$link          = sprintf( '<a href="%s">', get_comment_link( $comment ) );
		$respond_id    = '5234';
		$reply_text    = 'Reply';
		$reply_to_text = 'Reply to';
		$before        = '<div class="reply">';
		$after         = '</div>';
		$args          = compact( 'respond_id', 'reply_text', 'reply_to_text', 'before', 'after' );
		$comment       = $this->factory()->comment->create_and_get();

		update_option( 'comment_registration', true );
		$filtered_link = AMP_Theme_Support::filter_comment_reply_link( $link, $args, $comment );
		$this->assertEquals( $before . $link . $after, $filtered_link );
		update_option( 'comment_registration', false );

		$filtered_link = AMP_Theme_Support::filter_comment_reply_link( $link, $args, $comment );
		$this->assertStringStartsWith( $before, $filtered_link );
		$this->assertStringEndsWith( $after, $filtered_link );
		$this->assertContains( AMP_Theme_Support::get_comment_form_state_id( get_the_ID() ), $filtered_link );
		$this->assertContains( $comment->comment_author, $filtered_link );
		$this->assertContains( $comment->comment_ID, $filtered_link );
		$this->assertContains( 'tap:AMP.setState', $filtered_link );
		$this->assertContains( $reply_text, $filtered_link );
		$this->assertContains( $reply_to_text, $filtered_link );
		$this->assertContains( $respond_id, $filtered_link );
	}

	/**
	 * Test filter_cancel_comment_reply_link.
	 *
	 * @covers AMP_Theme_Support::filter_cancel_comment_reply_link()
	 */
	public function test_filter_cancel_comment_reply_link() {
		global $post;
		$post                   = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$url                    = get_permalink( $post );
		$_SERVER['REQUEST_URI'] = $url;
		$this->factory()->comment->create_and_get();
		$formatted_link = get_cancel_comment_reply_link();
		$link           = remove_query_arg( 'replytocom' );
		$text           = 'Cancel your reply';
		$filtered_link  = AMP_Theme_Support::filter_cancel_comment_reply_link( $formatted_link, $link, $text );
		$this->assertContains( $url, $filtered_link );
		$this->assertContains( $text, $filtered_link );
		$this->assertContains( '<a id="cancel-comment-reply-link"', $filtered_link );
		$this->assertContains( '.values.comment_parent ==', $filtered_link );
		$this->assertContains( 'tap:AMP.setState(', $filtered_link );

		$filtered_link_no_text_passed = AMP_Theme_Support::filter_cancel_comment_reply_link( $formatted_link, $link, '' );
		$this->assertContains( 'Click here to cancel reply.', $filtered_link_no_text_passed );
	}

	/**
	 * Test init_admin_bar.
	 *
	 * @covers \AMP_Theme_Support::init_admin_bar()
	 */
	public function test_init_admin_bar() {
		global $wp_styles, $wp_scripts;
		$wp_styles  = null;
		$wp_scripts = null;
		$this->assertNotEquals( AMP__VERSION, wp_styles()->registered['admin-bar']->ver );

		AMP_Theme_Support::init_admin_bar();
		$this->assertEquals( AMP__VERSION, wp_styles()->registered['admin-bar']->ver );
		$this->assertFalse( wp_scripts()->query( 'admin-bar', 'enqueued' ) );
		$body_classes = get_body_class();
		$this->assertContains( 'customize-support', $body_classes );
		$this->assertNotContains( 'no-customize-support', $body_classes );
	}

	/**
	 * Data provider for test_ensure_required_markup.
	 *
	 * @return array
	 */
	public function get_schema_script_data() {
		return array(
			'schema_org_not_present'        => array(
				'',
				1,
			),
			'schema_org_present'            => array(
				wp_json_encode( array( '@context' => 'http://schema.org' ) ),
				1,
			),
			'schema_org_output_not_escaped' => array(
				'{"@context":"http://schema.org"',
				1,
			),
			'schema_org_another_key'        => array(
				wp_json_encode( array( '@anothercontext' => 'https://schema.org' ) ),
				1,
			),
		);
	}

	/**
	 * Test ensure_required_markup().
	 *
	 * @dataProvider get_schema_script_data
	 * @covers AMP_Theme_Support::ensure_required_markup()
	 * @param string  $script The value of the script.
	 * @param boolean $expected The expected result.
	 */
	public function test_ensure_required_markup_schemaorg( $script, $expected ) {
		$page = '<html><head><script type="application/ld+json">%s</script></head><body>Test</body></html>';
		$dom  = new DOMDocument();
		$dom->loadHTML( sprintf( $page, $script ) );
		AMP_Theme_Support::ensure_required_markup( $dom );
		$this->assertEquals( $expected, substr_count( $dom->saveHTML(), 'schema.org' ) );
	}

	/**
	 * Test dequeue_customize_preview_scripts.
	 *
	 * @covers AMP_Theme_Support::dequeue_customize_preview_scripts()
	 */
	public function test_dequeue_customize_preview_scripts() {
		// Ensure AMP_Theme_Support::is_customize_preview_iframe() is true.
		require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		$GLOBALS['wp_customize'] = new WP_Customize_Manager( array(
			'messenger_channel' => 'baz',
		) );
		$GLOBALS['wp_customize']->start_previewing_theme();
		$customize_preview = 'customize-preview';
		$preview_style     = 'example-preview-style';
		wp_enqueue_style( $preview_style, home_url( '/' ), array( $customize_preview ) );
		AMP_Theme_Support::dequeue_customize_preview_scripts();
		$this->assertTrue( wp_style_is( $preview_style ) );
		$this->assertTrue( wp_style_is( $customize_preview ) );

		wp_enqueue_style( $preview_style, home_url( '/' ), array( $customize_preview ) );
		wp_enqueue_style( $customize_preview );
		// Ensure AMP_Theme_Support::is_customize_preview_iframe() is false.
		$GLOBALS['wp_customize'] = new WP_Customize_Manager();
		AMP_Theme_Support::dequeue_customize_preview_scripts();
		$this->assertFalse( wp_style_is( $preview_style ) );
		$this->assertFalse( wp_style_is( $customize_preview ) );
	}

	/**
	 * Test start_output_buffering.
	 *
	 * @covers AMP_Theme_Support::start_output_buffering()
	 * @covers AMP_Theme_Support::is_output_buffering()
	 * @covers AMP_Theme_Support::finish_output_buffering()
	 */
	public function test_start_output_buffering() {
		if ( ! function_exists( 'newrelic_disable_autorum ' ) ) {
			/**
			 * Define newrelic_disable_autorum to allow passing line.
			 */
			function newrelic_disable_autorum() {
				return true;
			}
		}

		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		$initial_ob_level = ob_get_level();
		AMP_Theme_Support::start_output_buffering();
		$this->assertEquals( $initial_ob_level + 1, ob_get_level() );
		ob_end_flush();
		$this->assertEquals( $initial_ob_level, ob_get_level() );
	}

	/**
	 * Test finish_output_buffering.
	 *
	 * @covers AMP_Theme_Support::finish_output_buffering()
	 * @covers AMP_Theme_Support::is_output_buffering()
	 */
	public function test_finish_output_buffering() {
		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		$status = ob_get_status();
		$this->assertSame( 1, ob_get_level() );
		$this->assertEquals( 'default output handler', $status['name'] );
		$this->assertFalse( AMP_Theme_Support::is_output_buffering() );

		// start first layer buffer.
		ob_start();
		AMP_Theme_Support::start_output_buffering();
		$this->assertTrue( AMP_Theme_Support::is_output_buffering() );
		$this->assertSame( 3, ob_get_level() );

		echo '<img src="test.png"><script data-test>document.write(\'Illegal\');</script>';

		// Additional nested output bufferings which aren't getting closed.
		ob_start();
		echo 'foo';
		ob_start( function( $response ) {
			return strtoupper( $response );
		} );
		echo 'bar';

		$this->assertTrue( AMP_Theme_Support::is_output_buffering() );
		while ( ob_get_level() > 2 ) {
			ob_end_flush();
		}
		$this->assertFalse( AMP_Theme_Support::is_output_buffering() );
		$output = ob_get_clean();
		$this->assertEquals( 1, ob_get_level() );

		$this->assertContains( '<html amp', $output );
		$this->assertContains( 'foo', $output );
		$this->assertContains( 'BAR', $output );
		$this->assertContains( '<amp-img src="test.png"', $output );
		$this->assertNotContains( '<script data-test', $output );

	}

	/**
	 * Test filter_customize_partial_render.
	 *
	 * @covers AMP_Theme_Support::filter_customize_partial_render()
	 */
	public function test_filter_customize_partial_render() {
		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		$partial = '<img src="test.png"><script data-head>document.write(\'Illegal\');</script>';
		$output  = AMP_Theme_Support::filter_customize_partial_render( $partial );
		$this->assertContains( '<amp-img src="test.png"', $output );
		$this->assertNotContains( '<script', $output );
		$this->assertNotContains( '<html', $output );
	}

	/**
	 * Test exceeded_cache_miss_threshold
	 *
	 * @covers AMP_Theme_Support::exceeded_cache_miss_threshold()
	 */
	public function test_exceeded_cache_miss_threshold() {
		$this->assertFalse( AMP_Theme_Support::exceeded_cache_miss_threshold() );
		add_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, site_url() );
		$this->assertTrue( AMP_Theme_Support::exceeded_cache_miss_threshold() );
	}

	/**
	 * Test prepare_response.
	 *
	 * @global WP_Widget_Factory $wp_widget_factory
	 * @global WP_Scripts $wp_scripts
	 * @covers AMP_Theme_Support::prepare_response()
	 * @covers AMP_Theme_Support::ensure_required_markup()
	 * @covers \amp_render_scripts()
	 */
	public function test_prepare_response() {
		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$original_html  = $this->get_original_html();
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html );

		$this->assertNotContains( 'handle=', $sanitized_html );
		$this->assertEquals( 2, substr_count( $sanitized_html, '<!-- wp_print_scripts -->' ) );

		$ordered_contains = array(
			'<meta charset="' . get_bloginfo( 'charset' ) . '">',
			'<meta name="viewport" content="width=device-width,minimum-scale=1">',
			'<meta name="generator" content="AMP Plugin',
			'<title>',
			'<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">',
			'<link rel="dns-prefetch" href="//cdn.ampproject.org">',
			'<link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">',
			'<link rel="preload" as="script" href="https://cdn.ampproject.org/v0/amp-experiment-latest.js">',
			'<link rel="preload" as="script" href="https://cdn.ampproject.org/v0/amp-dynamic-css-classes-0.1.js">',
			'<script type="text/javascript" src="https://cdn.ampproject.org/v0.js" async></script>',
			'<script src="https://cdn.ampproject.org/v0/amp-experiment-latest.js" async="" custom-element="amp-experiment"></script>',
			'<script async custom-element="amp-dynamic-css-classes" src="https://cdn.ampproject.org/v0/amp-dynamic-css-classes-0.1.js"></script>',
			'<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-list-latest.js" async custom-element="amp-list"></script>',
			'<script type="text/javascript" src="https://cdn.ampproject.org/v0/amp-mathml-latest.js" async custom-element="amp-mathml"></script>',

			// Note these are single-quoted because they are injected after the DOM has been re-serialized, so the type and src attributes come from WP_Scripts::do_item().
			'<script src="https://cdn.ampproject.org/v0/amp-audio-latest.js" async="" custom-element="amp-audio"></script>',
			'<script src="https://cdn.ampproject.org/v0/amp-ad-latest.js" async="" custom-element="amp-ad"></script>',

			'<link rel="icon" href="http://example.org/favicon.png" sizes="32x32">',
			'<link rel="icon" href="http://example.org/favicon.png" sizes="192x192">',

			'#<style amp-custom>.*?body\s*{\s*background:\s*black;?\s*}.*?</style>#s',
			'<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine" crossorigin="anonymous">',
			'<style amp-boilerplate>',
			'<noscript><style amp-boilerplate>',
			'<script type="application/ld+json">{"@context"',
			'<link rel="canonical" href="',
			'</head>',
		);

		$last_position        = -1;
		$prev_ordered_contain = '';
		foreach ( $ordered_contains as $ordered_contain ) {
			if ( '#' === substr( $ordered_contain, 0, 1 ) ) {
				$this->assertEquals( 1, preg_match( $ordered_contain, $sanitized_html, $matches, PREG_OFFSET_CAPTURE ), "Failed to find: $ordered_contain" );
				$this->assertGreaterThan( $last_position, $matches[0][1], "'$ordered_contain' is not after '$prev_ordered_contain'" );
				$last_position = $matches[0][1];
			} else {
				$this_position = strpos( $sanitized_html, $ordered_contain );
				$this->assertNotFalse( $this_position, "Failed to find: $ordered_contain" );
				$this->assertGreaterThan( $last_position, (int) $this_position, "'$ordered_contain' is not after '$prev_ordered_contain'" );
				$last_position = $this_position;
			}
			$prev_ordered_contain = $ordered_contain;
		}

		$this->assertNotContains( '<img', $sanitized_html );
		$this->assertContains( '<amp-img', $sanitized_html );

		$this->assertNotContains( '<audio', $sanitized_html );
		$this->assertContains( '<amp-audio', $sanitized_html );

		$removed_nodes = array();
		foreach ( AMP_Validation_Manager::$validation_results as $result ) {
			if ( $result['sanitized'] && isset( $result['error']['node_name'] ) ) {
				$node_name = $result['error']['node_name'];
				if ( ! isset( $removed_nodes[ $node_name ] ) ) {
					$removed_nodes[ $node_name ] = 0;
				}
				$removed_nodes[ $node_name ]++;
			}
		}

		$this->assertContains( '<button>no-onclick</button>', $sanitized_html );
		$this->assertCount( 5, AMP_Validation_Manager::$validation_results );
		$this->assertEquals(
			array(
				'onclick' => 1,
				'handle'  => 3,
				'script'  => 1,
			),
			$removed_nodes
		);

		// Make sure trailing content after </html> gets moved.
		$this->assertRegExp( '#<!--comment-after-html-->\s*<div id="after-html"></div>\s*<!--comment-end-html-->\s*</body>\s*</html>\s*$#s', $sanitized_html );

		$prepare_response_args = array(
			'enable_response_caching' => true,
		);

		$call_prepare_response = function() use ( $original_html, &$prepare_response_args ) {
			AMP_HTTP::$headers_sent                     = array();
			AMP_Validation_Manager::$validation_results = array();
			return AMP_Theme_Support::prepare_response( $original_html, $prepare_response_args );
		};

		// Test that first response isn't cached.
		$first_response = $call_prepare_response();
		$this->assertGreaterThan( 0, $this->get_server_timing_header_count() );
		$this->assertContains( '<html amp>', $first_response ); // Note: AMP because sanitized validation errors.
		$this->reset_post_processor_cache_effectiveness();

		// Test that response cache is return upon second call.
		$this->assertEquals( $first_response, $call_prepare_response() );
		$this->assertSame( 0, $this->get_server_timing_header_count() );
		$this->reset_post_processor_cache_effectiveness();

		// Test new cache upon argument change.
		$prepare_response_args['test_reset_by_arg'] = true;
		$call_prepare_response();
		$this->assertGreaterThan( 0, $this->get_server_timing_header_count() );
		$this->reset_post_processor_cache_effectiveness();

		// Test response is cached.
		$call_prepare_response();
		$this->assertSame( 0, $this->get_server_timing_header_count() );
		$this->reset_post_processor_cache_effectiveness();

		// Test that response is no longer cached due to a change whether validation errors are sanitized.
		remove_filter( 'amp_validation_error_sanitized', '__return_true' );
		add_filter( 'amp_validation_error_sanitized', '__return_false' );
		$prepared_html = $call_prepare_response();
		$this->assertGreaterThan( 0, $this->get_server_timing_header_count() );
		$this->assertContains( '<html>', $prepared_html ); // Note: no AMP because unsanitized validation error.
		$this->reset_post_processor_cache_effectiveness();

		// And test response is cached.
		$call_prepare_response();
		$this->assertSame( 0, $this->get_server_timing_header_count() );

		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	}

	/**
	 * Test post-processor cache effectiveness in AMP_Theme_Support::prepare_response().
	 */
	public function test_post_processor_cache_effectiveness() {
		$original_html = $this->get_original_html();
		$args          = array( 'enable_response_caching' => true );
		wp_using_ext_object_cache( true ); // turn on external object cache flag.
		$this->reset_post_processor_cache_effectiveness();

		// Test the response is not cached after exceeding the cache miss threshold.
		for ( $num_calls = 1, $max = AMP_Theme_Support::CACHE_MISS_THRESHOLD + 2; $num_calls <= $max; $num_calls++ ) {
			// Simulate dynamic changes in the content.
			$original_html = str_replace( 'dynamic-id-', "dynamic-id-{$num_calls}-", $original_html );

			AMP_HTTP::$headers_sent                     = array();
			AMP_Validation_Manager::$validation_results = array();
			AMP_Theme_Support::prepare_response( $original_html, $args );

			$caches_for_url = wp_cache_get( AMP_Theme_Support::POST_PROCESSOR_CACHE_EFFECTIVENESS_KEY, AMP_Theme_Support::POST_PROCESSOR_CACHE_EFFECTIVENESS_GROUP );
			$cache_miss_url = get_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION, false );

			// When we've met the threshold, check that caching did not happen.
			if ( $num_calls > AMP_Theme_Support::CACHE_MISS_THRESHOLD ) {
				$this->assertEquals( AMP_Theme_Support::CACHE_MISS_THRESHOLD, count( $caches_for_url ) );
				$this->assertEquals( amp_get_current_url(), $cache_miss_url );

				// Check that response caching was automatically disabled.
				$this->assertFalse( AMP_Options_Manager::get_option( 'enable_response_caching' ) );
			} else {
				$this->assertEquals( $num_calls, count( $caches_for_url ) );
				$this->assertFalse( $cache_miss_url );
				$this->assertTrue( AMP_Options_Manager::get_option( 'enable_response_caching' ) );
			}

			$this->assertGreaterThan( 0, $this->get_server_timing_header_count() );
		}

		// Reset.
		wp_using_ext_object_cache( false );
	}

	/**
	 * Initializes and returns the original HTML.
	 */
	private function get_original_html() {
		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		global $wp_widget_factory, $wp_scripts, $wp_styles;
		$wp_scripts = null;
		$wp_styles  = null;

		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();
		$wp_widget_factory = new WP_Widget_Factory();
		wp_widgets_init();

		$this->assertTrue( is_amp_endpoint() );

		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_script( 'amp-list' );
		} );
		add_action( 'wp_print_scripts', function() {
			echo '<!-- wp_print_scripts -->';
		} );

		add_action( 'wp_print_styles', function() {
			echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine">';
		} );

		add_filter( 'script_loader_tag', function( $tag, $handle ) {
			if ( ! wp_scripts()->get_data( $handle, 'conditional' ) ) {
				$tag = preg_replace( '/(?<=<script)/', " handle='$handle' ", $tag );
			}
			return $tag;
		}, 10, 2 );

		add_action( 'wp_footer', function() {
			wp_print_scripts( 'amp-mathml' );
			?>
			<amp-mathml layout="container" data-formula="\[x = {-b \pm \sqrt{b^2-4ac} \over 2a}.\]"></amp-mathml>
			<?php
		}, 1 );

		add_filter( 'get_site_icon_url', function() {
			return home_url( '/favicon.png' );
		} );

		ob_start();
		?>
		<!DOCTYPE html>
		<html amp>
		<head>
			<?php wp_head(); ?>
			<script data-head>document.write('Illegal');</script>
			<script async custom-element="amp-dynamic-css-classes" src="https://cdn.ampproject.org/v0/amp-dynamic-css-classes-0.1.js"></script>
		</head>
		<body><!-- </body></html> -->
		<div id="dynamic-id-0"></div>
		<img width="100" height="100" src="https://example.com/test.png">
		<audio width="400" height="300" src="https://example.com/audios/myaudio.mp3"></audio>
		<amp-ad type="a9"
				width="300"
				height="250"
				data-aax_size="300x250"
				data-aax_pubname="test123"
				data-aax_src="302"></amp-ad>

		<?php wp_footer(); ?>

		<button onclick="alert('Illegal');">no-onclick</button>

		<style>body { background: black; }</style>

		<amp-experiment>
			<script type="application/json">
				{ "aExperiment": {} }
			</script>
		</amp-experiment>
		</body>
		</html>
		<!--comment-after-html-->
		<div id="after-html"></div>
		<!--comment-end-html-->
		<?php
		return trim( ob_get_clean() );
		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	}

	/**
	 * Returns the "Server-Timing" header count.
	 */
	private function get_server_timing_header_count() {
		return count( array_filter(
			AMP_HTTP::$headers_sent,
			function( $header ) {
				return 'Server-Timing' === $header['name'];
			}
		) );
	}

	/**
	 * Reset cached URLs in post-processor cache effectiveness.
	 */
	private function reset_post_processor_cache_effectiveness() {
		wp_cache_delete( AMP_Theme_Support::POST_PROCESSOR_CACHE_EFFECTIVENESS_KEY, AMP_Theme_Support::POST_PROCESSOR_CACHE_EFFECTIVENESS_GROUP );
		delete_option( AMP_Theme_Support::CACHE_MISS_URL_OPTION );
	}

	/**
	 * Test prepare_response for bad/non-HTML.
	 *
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response_bad_html() {
		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();

		// JSON.
		$input = '{"success":true}';
		$this->assertEquals( $input, AMP_Theme_Support::prepare_response( $input ) );

		// Nothing, for redirect.
		$input = '';
		$this->assertEquals( $input, AMP_Theme_Support::prepare_response( $input ) );

		// HTML, but very stripped down.
		$input  = '<html>Hello</html>';
		$output = AMP_Theme_Support::prepare_response( $input );
		$this->assertContains( '<html amp', $output );
	}

	/**
	 * Test prepare_response to inject html[amp] attribute and ensure HTML5 doctype.
	 *
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response_to_add_html5_doctype_and_amp_attribute() {
		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		add_theme_support( 'amp' );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();
		ob_start();
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><?php wp_head(); ?></head><body><?php wp_footer(); ?></body></html>
		<?php
		$original_html  = trim( ob_get_clean() );
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html );

		$this->assertStringStartsWith( '<!DOCTYPE html>', $sanitized_html );
		$this->assertContains( '<html amp', $sanitized_html );
		$this->assertContains( '<meta charset="utf-8">', $sanitized_html );
	}

	/**
	 * Test prepare_response will cache redirects when validation errors happen.
	 *
	 * @covers AMP_Theme_Support::prepare_response()
	 */
	public function test_prepare_response_redirect() {
		add_filter( 'amp_validation_error_sanitized', '__return_false', 100 );

		$this->go_to( home_url( '/?amp' ) );
		add_theme_support( 'amp', array(
			'paired' => true,
		) );
		add_filter( 'amp_content_sanitizers', function( $sanitizers ) {
			$sanitizers['AMP_Theme_Support_Sanitizer_Counter'] = array();
			return $sanitizers;
		} );
		AMP_Theme_Support::init();
		AMP_Theme_Support::finish_init();
		$this->assertTrue( is_amp_endpoint() );

		ob_start();
		?>
		<html>
			<head>
			</head>
			<body>
				<script>bad</script>
			</body>
		</html>
		<?php
		$original_html = trim( ob_get_clean() );

		$redirects = array();
		add_filter( 'wp_redirect', function( $url ) use ( &$redirects ) {
			array_unshift( $redirects, $url );
			return '';
		} );

		AMP_Theme_Support_Sanitizer_Counter::$count = 0;
		AMP_Validation_Manager::reset_validation_results();
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html, array( 'enable_response_caching' => true ) );
		$this->assertStringStartsWith( 'Redirecting to non-AMP version', $sanitized_html );
		$this->assertCount( 1, $redirects );
		$this->assertEquals( home_url( '/' ), $redirects[0] );
		$this->assertEquals( 1, AMP_Theme_Support_Sanitizer_Counter::$count );

		AMP_Validation_Manager::reset_validation_results();
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html, array( 'enable_response_caching' => true ) );
		$this->assertStringStartsWith( 'Redirecting to non-AMP version', $sanitized_html );
		$this->assertCount( 2, $redirects );
		$this->assertEquals( home_url( '/' ), $redirects[0] );
		$this->assertEquals( 1, AMP_Theme_Support_Sanitizer_Counter::$count, 'Expected sanitizer to not be invoked.' );

		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		AMP_Validation_Manager::add_validation_error_sourcing();

		AMP_Validation_Manager::reset_validation_results();
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html, array( 'enable_response_caching' => true ) );
		$this->assertStringStartsWith( 'Redirecting to non-AMP version', $sanitized_html );
		$this->assertCount( 3, $redirects );
		$this->assertEquals( home_url( '/?amp_validation_errors=1' ), $redirects[0] );
		$this->assertEquals( 2, AMP_Theme_Support_Sanitizer_Counter::$count, 'Expected sanitizer be invoked after validation changed.' );

		AMP_Validation_Manager::reset_validation_results();
		$sanitized_html = AMP_Theme_Support::prepare_response( $original_html, array( 'enable_response_caching' => true ) );
		$this->assertStringStartsWith( 'Redirecting to non-AMP version', $sanitized_html );
		$this->assertCount( 4, $redirects );
		$this->assertEquals( home_url( '/?amp_validation_errors=1' ), $redirects[0] );
		$this->assertEquals( 2, AMP_Theme_Support_Sanitizer_Counter::$count, 'Expected sanitizer to not now be invoked since previous validation results now cached.' );

	}

	/**
	 * Test enqueue_assets().
	 *
	 * @covers AMP_Theme_Support::enqueue_assets()
	 */
	public function test_enqueue_assets() {
		$script_slug = 'amp-runtime';
		$style_slug  = 'amp-default';
		wp_dequeue_script( $script_slug );
		wp_dequeue_style( $style_slug );
		AMP_Theme_Support::enqueue_assets();
		$this->assertTrue( in_array( $script_slug, wp_scripts()->queue, true ) );
		$this->assertTrue( in_array( $style_slug, wp_styles()->queue, true ) );
	}

	/**
	 * Test AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html().
	 *
	 * @see AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html()
	 */
	public function test_whitelist_layout_in_wp_kses_allowed_html() {
		$attribute             = 'data-amp-layout';
		$image_no_dimensions   = array(
			'img' => array(
				$attribute => true,
			),
		);
		$image_with_dimensions = array_merge(
			$image_no_dimensions,
			array(
				'height' => '100',
				'width'  => '100',
			)
		);

		$this->assertEquals( array(), AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( array() ) );
		$this->assertEquals( $image_no_dimensions, AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( $image_no_dimensions ) );

		$context = AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( $image_with_dimensions );
		$this->assertTrue( $context['img'][ $attribute ] );

		$context = AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html( $image_with_dimensions );
		$this->assertTrue( $context['img'][ $attribute ] );

		add_filter( 'wp_kses_allowed_html', 'AMP_Theme_Support::whitelist_layout_in_wp_kses_allowed_html', 10, 2 );
		$image = '<img data-amp-layout="fill">';
		$this->assertEquals( $image, wp_kses_post( $image ) );
	}

	/**
	 * Test AMP_Theme_Support::amend_header_image_with_video_header().
	 *
	 * @see AMP_Theme_Support::amend_header_image_with_video_header()
	 */
	public function test_amend_header_image_with_video_header() {
		$mock_image = '<img src="https://example.com/flower.jpeg">';

		// If there's no theme support for 'custom-header', the callback should simply return the image.
		$this->assertEquals(
			$mock_image,
			AMP_Theme_Support::amend_header_image_with_video_header( $mock_image )
		);

		// If theme support is present, but there isn't a header video selected, the callback should again return the image.
		add_theme_support( 'custom-header', array(
			'video' => true,
		) );

		// There's a YouTube URL as the header video.
		set_theme_mod( 'external_header_video', 'https://www.youtube.com/watch?v=a8NScvBhVnc' );
		$this->assertEquals(
			$mock_image . '<amp-youtube media="(min-width: 900px)" width="0" height="0" layout="responsive" autoplay id="wp-custom-header-video" data-videoid="a8NScvBhVnc" data-param-rel="0" data-param-showinfo="0" data-param-controls="0"></amp-youtube>',
			AMP_Theme_Support::amend_header_image_with_video_header( $mock_image )
		);
	}
}

// phpcs:disable Generic.Files.OneClassPerFile.MultipleFound

/**
 * Class AMP_Theme_Support_Sanitizer_Counter
 */
class AMP_Theme_Support_Sanitizer_Counter extends AMP_Base_Sanitizer {

	/**
	 * Count.
	 *
	 * @var int
	 */
	public static $count = 0;

	/**
	 * "Sanitize".
	 */
	public function sanitize() {
		self::$count++;
	}
}
