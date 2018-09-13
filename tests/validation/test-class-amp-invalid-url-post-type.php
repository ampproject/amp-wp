<?php
/**
 * Tests for AMP_Invalid_URL_Post_Type class.
 *
 * @package AMP
 */

// phpcs:disable WordPress.Variables.GlobalVariables.OverrideProhibited

/**
 * Tests for AMP_Invalid_URL_Post_Type class.
 *
 * @covers AMP_Invalid_URL_Post_Type
 */
class Test_AMP_Invalid_URL_Post_Type extends \WP_UnitTestCase {

	const TESTED_CLASS = 'AMP_Invalid_URL_Post_Type';

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		global $current_screen;
		parent::tearDown();
		$current_screen = null; // WPCS: override ok.
	}

	/**
	 * Test register.
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::register()
	 * @covers \AMP_Invalid_URL_Post_Type::add_admin_hooks()
	 */
	public function test_register() {
		add_theme_support( 'amp' );
		$this->assertFalse( is_admin() );

		AMP_Invalid_URL_Post_Type::register();
		$amp_post_type = get_post_type_object( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG );

		$this->assertTrue( in_array( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, get_post_types(), true ) );
		$this->assertEquals( array(), get_all_post_type_supports( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ) );
		$this->assertEquals( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, $amp_post_type->name );
		$this->assertEquals( 'Invalid AMP Pages (URLs)', $amp_post_type->label );
		$this->assertEquals( false, $amp_post_type->public );
		$this->assertTrue( $amp_post_type->show_ui );
		$this->assertEquals( AMP_Options_Manager::OPTION_NAME, $amp_post_type->show_in_menu );
		$this->assertTrue( $amp_post_type->show_in_admin_bar );
		$this->assertNotContains( AMP_Invalid_URL_Post_Type::REMAINING_ERRORS, wp_removable_query_args() );

		// Make sure that add_admin_hooks() gets called.
		set_current_screen( 'index.php' );
		AMP_Invalid_URL_Post_Type::register();
		$this->assertContains( AMP_Invalid_URL_Post_Type::REMAINING_ERRORS, wp_removable_query_args() );
	}

	/**
	 * Test should_show_in_menu.
	 *
	 * @covers AMP_Invalid_URL_Post_Type::should_show_in_menu()
	 */
	public function test_should_show_in_menu() {
		global $pagenow;
		add_theme_support( 'amp' );
		$this->assertTrue( AMP_Invalid_URL_Post_Type::should_show_in_menu() );

		remove_theme_support( 'amp' );
		$this->assertFalse( AMP_Invalid_URL_Post_Type::should_show_in_menu() );

		$pagenow           = 'edit.php'; // WPCS: override ok.
		$_GET['post_type'] = 'post';
		$this->assertFalse( AMP_Invalid_URL_Post_Type::should_show_in_menu() );

		$_GET['post_type'] = AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG;
		$this->assertTrue( AMP_Invalid_URL_Post_Type::should_show_in_menu() );
	}

	/**
	 * Test add_admin_hooks.
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::add_admin_hooks()
	 */
	public function test_add_admin_hooks() {
		AMP_Invalid_URL_Post_Type::add_admin_hooks();

		$this->assertEquals( 10, has_filter( 'dashboard_glance_items', array( self::TESTED_CLASS, 'filter_dashboard_glance_items' ) ) );
		$this->assertEquals( 10, has_action( 'rightnow_end', array( self::TESTED_CLASS, 'print_dashboard_glance_styles' ) ) );

		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( self::TESTED_CLASS, 'enqueue_edit_post_screen_scripts' ) ) );
		$this->assertEquals( 10, has_action( 'add_meta_boxes', array( self::TESTED_CLASS, 'add_meta_boxes' ) ) );
		$this->assertEquals( 10, has_action( 'edit_form_top', array( self::TESTED_CLASS, 'print_url_as_title' ) ) );

		$this->assertEquals( 10, has_filter( 'the_title', array( self::TESTED_CLASS, 'filter_the_title_in_post_list_table' ) ) );
		$this->assertEquals( 10, has_filter( 'restrict_manage_posts', array( self::TESTED_CLASS, 'render_post_filters' ) ) );
		$this->assertEquals( 10, has_filter( 'manage_' . AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG . '_posts_columns', array( self::TESTED_CLASS, 'add_post_columns' ) ) );
		$this->assertEquals( 10, has_action( 'manage_posts_custom_column', array( self::TESTED_CLASS, 'output_custom_column' ) ) );
		$this->assertEquals( 10, has_filter( 'post_row_actions', array( self::TESTED_CLASS, 'filter_row_actions' ) ) );
		$this->assertEquals( 10, has_filter( 'bulk_actions-edit-' . AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, array( self::TESTED_CLASS, 'filter_bulk_actions' ) ) );
		$this->assertEquals( 10, has_filter( 'handle_bulk_actions-edit-' . AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, array( self::TESTED_CLASS, 'handle_bulk_action' ) ) );
		$this->assertEquals( 10, has_action( 'admin_notices', array( self::TESTED_CLASS, 'print_admin_notice' ) ) );
		$this->assertEquals( 10, has_action( 'admin_action_' . AMP_Invalid_URL_Post_Type::VALIDATE_ACTION, array( self::TESTED_CLASS, 'handle_validate_request' ) ) );
		$this->assertEquals( 10, has_action( 'post_action_' . AMP_Invalid_URL_Post_Type::UPDATE_POST_TERM_STATUS_ACTION, array( self::TESTED_CLASS, 'handle_validation_error_status_update' ) ) );
		$this->assertEquals( 10, has_action( 'admin_menu', array( self::TESTED_CLASS, 'add_admin_menu_new_invalid_url_count' ) ) );

		$post = $this->factory()->post->create_and_get( array( 'post_type' => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ) );
		$this->assertEquals( '', apply_filters( 'post_date_column_status', 'publish', $post ) );
		$this->assertEquals( 'publish', apply_filters( 'post_date_column_status', 'publish', $this->factory()->post->create_and_get() ) );

		$this->assertContains( 'amp_actioned', wp_removable_query_args() );
		$this->assertContains( 'amp_taxonomy_terms_updated', wp_removable_query_args() );
		$this->assertContains( AMP_Invalid_URL_Post_Type::REMAINING_ERRORS, wp_removable_query_args() );
		$this->assertContains( 'amp_urls_tested', wp_removable_query_args() );
		$this->assertContains( 'amp_validate_error', wp_removable_query_args() );
	}

	/**
	 * Test add_admin_menu_new_invalid_url_count.
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::add_admin_menu_new_invalid_url_count()
	 */
	public function test_add_admin_menu_new_invalid_url_count() {
		global $submenu;
		AMP_Validation_Manager::init(); // Register the post type and taxonomy.

		unset( $submenu[ AMP_Options_Manager::OPTION_NAME ] );
		AMP_Invalid_URL_Post_Type::add_admin_menu_new_invalid_url_count();

		$submenu[ AMP_Options_Manager::OPTION_NAME ] = array( // WPCS: override ok.
			0 => array(
				0 => 'General',
				1 => 'manage_options',
				2 => 'amp-options',
				3 => 'AMP Settings',
			),
			1 => array(
				0 => 'Analytics',
				1 => 'manage_options',
				2 => 'amp-analytics-options',
				3 => 'AMP Analytics Options',
			),
			2 => array(
				0 => 'Invalid Pages',
				1 => 'edit_posts',
				2 => 'edit.php?post_type=amp_invalid_url',
				3 => 'Invalid AMP Pages (URLs)',
			),
		);

		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array(
					'code' => 'hello',
				),
			),
			get_permalink( $this->factory()->post->create() )
		);
		$this->assertNotInstanceOf( 'WP_Error', $invalid_url_post_id );

		AMP_Invalid_URL_Post_Type::add_admin_menu_new_invalid_url_count();

		$this->assertContains( '<span class="awaiting-mod"><span class="pending-count">1</span></span>', $submenu[ AMP_Options_Manager::OPTION_NAME ][2][0] );
	}

	/**
	 * Test get_invalid_url_validation_errors and display_invalid_url_validation_error_counts_summary.
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors()
	 * @covers \AMP_Invalid_URL_Post_Type::display_invalid_url_validation_error_counts_summary()
	 * @covers \AMP_Invalid_URL_Post_Type::store_validation_errors()
	 */
	public function test_get_invalid_url_validation_errors() {
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		add_theme_support( 'amp', array( 'paired' => true ) );
		AMP_Validation_Manager::init();
		$post = $this->factory()->post->create();
		$this->assertEmpty( AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors( get_permalink( $post ) ) );

		add_filter( 'amp_validation_error_sanitized', function( $sanitized, $error ) {
			if ( 'accepted' === $error['code'] ) {
				$sanitized = true;
			} elseif ( 'rejected' === $error['code'] ) {
				$sanitized = false;
			}
			return $sanitized;
		}, 10, 2 );

		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'accepted' ),
				array( 'code' => 'rejected' ),
				array( 'code' => 'new' ),
			),
			get_permalink( $post )
		);
		$this->assertNotInstanceOf( 'WP_Error', $invalid_url_post_id );

		$errors = AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors( get_permalink( $post ) );
		$this->assertCount( 3, $errors );

		$error = array_shift( $errors );
		$this->assertEquals( 'accepted', $error['data']['code'] );
		$this->assertEquals( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS, $error['term_status'] );
		$error = array_shift( $errors );
		$this->assertEquals( 'rejected', $error['data']['code'] );
		$this->assertEquals( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS, $error['term_status'] );
		$error = array_shift( $errors );
		$this->assertEquals( 'new', $error['data']['code'] );
		$this->assertEquals( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS, $error['term_status'] );

		$errors = AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors( get_permalink( $post ), array( 'ignore_accepted' => true ) );
		$this->assertCount( 2, $errors );
		$error = array_shift( $errors );
		$this->assertEquals( 'rejected', $error['data']['code'] );
		$this->assertEquals( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS, $error['term_status'] );
		$error = array_shift( $errors );
		$this->assertEquals( 'new', $error['data']['code'] );
		$this->assertEquals( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS, $error['term_status'] );

		ob_start();
		AMP_Invalid_URL_Post_Type::display_invalid_url_validation_error_counts_summary( $invalid_url_post_id );
		$summary = ob_get_clean();
		$this->assertContains( 'New: 1', $summary );
		$this->assertContains( 'Accepted: 1', $summary );
		$this->assertContains( 'Rejected: 1', $summary );
	}

	/**
	 * Test for get_invalid_url_post().
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::get_invalid_url_post()
	 */
	public function test_get_invalid_url_post() {
		add_theme_support( 'amp', array( 'paired' => true ) );
		AMP_Validation_Manager::init();
		$post = $this->factory()->post->create_and_get();
		$this->assertEquals( null, AMP_Invalid_URL_Post_Type::get_invalid_url_post( get_permalink( $post ) ) );

		$invalid_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			get_permalink( $post )
		);
		$this->assertNotInstanceOf( 'WP_Error', $invalid_post_id );

		$this->assertEquals(
			$invalid_post_id,
			AMP_Invalid_URL_Post_Type::get_invalid_url_post( get_permalink( $post ) )->ID
		);
	}

	/**
	 * Test get_url_from_post.
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::get_url_from_post()
	 */
	public function test_get_url_from_post() {
		add_theme_support( 'amp', array( 'paired' => true ) );
		AMP_Validation_Manager::init();
		$post = $this->factory()->post->create_and_get();

		$this->assertNull( AMP_Invalid_URL_Post_Type::get_url_from_post( 0 ) );
		$this->assertNull( AMP_Invalid_URL_Post_Type::get_url_from_post( $post ) );

		$invalid_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			get_permalink( $post )
		);
		$this->assertNotInstanceOf( 'WP_Error', $invalid_post_id );

		$this->assertEquals(
			add_query_arg( amp_get_slug(), '', get_permalink( $post ) ),
			AMP_Invalid_URL_Post_Type::get_url_from_post( $invalid_post_id )
		);

		add_theme_support( 'amp', array( 'paired' => false ) );
		$this->assertEquals(
			get_permalink( $post ),
			AMP_Invalid_URL_Post_Type::get_url_from_post( $invalid_post_id )
		);
	}

	/**
	 * Test for store_validation_errors()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::store_validation_errors()
	 */
	public function test_store_validation_errors() {
		add_theme_support( 'amp', array( 'paired' => true ) );
		AMP_Validation_Manager::init();
		$post = $this->factory()->post->create_and_get();

		add_filter( 'amp_validation_error_sanitized', function( $sanitized, $error ) {
			if ( 'accepted' === $error['code'] ) {
				$sanitized = true;
			} elseif ( 'rejected' === $error['code'] ) {
				$sanitized = false;
			}
			return $sanitized;
		}, 10, 2 );

		$errors = array(
			array(
				'code'    => 'accepted',
				'sources' => array(
					array(
						'type' => 'plugin',
						'name' => 'amp',
						'evil' => '<script>\o/</script>', // Test slash preservation and kses suspension.
					),
				),
			),
			array(
				'code'    => 'rejected',
				'evil'    => '<script>\o/</script>', // Test slash preservation and kses suspension.
				'sources' => array(
					array(
						'type' => 'theme',
						'name' => 'twentyseventeen',
					),
				),
			),
			array(
				'code'    => 'new',
				'sources' => array(
					array(
						'type' => 'core',
						'name' => 'wp-includes',
					),
				),
			),
		);

		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			$errors,
			get_permalink( $post )
		);
		$this->assertNotInstanceOf( 'WP_Error', $invalid_url_post_id );

		// Test resurrection from trash.
		wp_trash_post( $invalid_url_post_id );
		$this->assertEquals(
			$invalid_url_post_id,
			AMP_Invalid_URL_Post_Type::store_validation_errors(
				$errors,
				get_permalink( $post ),
				array(
					'queried_object' => array(
						'type' => 'post',
						'id'   => $post->ID,
					),
				)
			)
		);
		$this->assertEquals( 'publish', get_post_status( $invalid_url_post_id ) );
		$this->assertEquals(
			array(
				'type' => 'post',
				'id'   => $post->ID,
			),
			get_post_meta( $invalid_url_post_id, '_amp_queried_object', true )
		);

		// Test passing specific post to override the URL.
		$this->assertEquals(
			$invalid_url_post_id,
			AMP_Invalid_URL_Post_Type::store_validation_errors(
				$errors,
				home_url( '/something/else/' ),
				array(
					'invalid_url_post' => $invalid_url_post_id,
				)
			)
		);

		$this->assertEquals(
			home_url( '/something/else/' ),
			get_post( $invalid_url_post_id )->post_title
		);

		$stored_errors = AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors( $invalid_url_post_id );
		$this->assertEquals(
			$errors,
			array_map(
				function( $stored_error ) {
					return $stored_error['data'];
				},
				$stored_errors
			)
		);

		$error_groups = array(
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS,
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECTED_STATUS,
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
		);

		foreach ( $errors as $i => $error ) {
			$stored_error = $stored_errors[ $i ];

			$this->assertEquals( $error, $stored_error['data'] );

			$sourceless_error = $error;
			unset( $sourceless_error['sources'] );

			/**
			 * Term.
			 *
			 * @var WP_Term $term
			 */
			$term = $stored_error['term'];
			$this->assertEquals( $sourceless_error, json_decode( $term->description, true ) );

			$this->assertNotEmpty( get_term_meta( $term->term_id, 'created_date_gmt', true ) );
			$this->assertEquals( $error_groups[ $i ], $stored_error['term_status'] );
			$this->assertEquals( $error_groups[ $i ], $term->term_group );
		}
	}

	/**
	 * Test get_validated_environment().
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::get_validated_environment()
	 */
	public function test_get_validated_environment() {
		switch_theme( 'twentysixteen' );
		update_option( 'active_plugins', array( 'foo/foo.php', 'bar.php' ) );
		AMP_Options_Manager::update_option( 'accept_tree_shaking', true );
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		$old_env = AMP_Invalid_URL_Post_Type::get_validated_environment();
		$this->assertArrayHasKey( 'theme', $old_env );
		$this->assertArrayHasKey( 'plugins', $old_env );
		$this->assertArrayHasKey( 'options', $old_env );
		$this->assertArrayHasKey( 'accept_tree_shaking', $old_env['options'] );
		$this->assertTrue( $old_env['options']['accept_tree_shaking'] );
		$this->assertEquals( 'twentysixteen', $old_env['theme'] );

		switch_theme( 'twentyseventeen' );
		update_option( 'active_plugins', array( 'foo/foo.php', 'baz.php' ) );
		AMP_Options_Manager::update_option( 'accept_tree_shaking', false );
		$new_env = AMP_Invalid_URL_Post_Type::get_validated_environment();
		$this->assertNotEquals( $old_env, $new_env );
		$this->assertFalse( $new_env['options']['accept_tree_shaking'] );
		$this->assertEquals( 'twentyseventeen', $new_env['theme'] );
	}

	/**
	 * Test get_post_staleness method.
	 *
	 * @covers AMP_Invalid_URL_Post_Type::get_post_staleness()
	 * @covers AMP_Invalid_URL_Post_Type::get_validated_environment()
	 */
	public function test_get_post_staleness() {
		$error = array( 'code' => 'foo' );
		switch_theme( 'twentysixteen' );
		update_option( 'active_plugins', array( 'foo/foo.php', 'bar.php' ) );

		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors( array( $error ), home_url( '/' ) );
		$this->assertInternalType( 'int', $invalid_url_post_id );
		$this->assertEmpty( AMP_Invalid_URL_Post_Type::get_post_staleness( $invalid_url_post_id ) );

		update_option( 'active_plugins', array( 'foo/foo.php', 'baz.php' ) );
		$staleness = AMP_Invalid_URL_Post_Type::get_post_staleness( $invalid_url_post_id );
		$this->assertNotEmpty( $staleness );
		$this->assertArrayHasKey( 'plugins', $staleness );
		$this->assertArrayNotHasKey( 'theme', $staleness );

		$this->assertEqualSets( array( 'baz.php' ), $staleness['plugins']['new'] );
		$this->assertEqualSets( array( 'bar.php' ), $staleness['plugins']['old'] );

		switch_theme( 'twentyseventeen' );
		$next_staleness = AMP_Invalid_URL_Post_Type::get_post_staleness( $invalid_url_post_id );
		$this->assertArrayHasKey( 'theme', $next_staleness );
		$this->assertEquals( 'twentysixteen', $next_staleness['theme'] );
		$this->assertSame( $next_staleness['plugins'], $staleness['plugins'] );

		// Re-storing results updates freshness.
		AMP_Invalid_URL_Post_Type::store_validation_errors( array( $error ), home_url( '/' ), $invalid_url_post_id );
		$this->assertEmpty( AMP_Invalid_URL_Post_Type::get_post_staleness( $invalid_url_post_id ) );
	}

	/**
	 * Test for add_post_columns()
	 *
	 * @covers AMP_Invalid_URL_Post_Type::add_post_columns()
	 */
	public function test_add_post_columns() {
		$initial_columns = array(
			'cb' => '<input type="checkbox">',
		);
		$this->assertEquals(
			array_merge(
				$initial_columns,
				array(
					AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS => 'Removed Elements',
					AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES => 'Removed Attributes',
					AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT => 'Incompatible Sources',
					'error_status' => 'Error Status',
				)
			),
			AMP_Invalid_URL_Post_Type::add_post_columns( $initial_columns )
		);
	}

	/**
	 * Gets the test data for test_output_custom_column().
	 *
	 * @return array $columns
	 */
	public function get_custom_columns() {
		$source = array(
			'type' => 'plugin',
			'name' => 'amp',
		);
		$errors = array(
			array(
				'code'      => AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE,
				'node_name' => 'script',
				'sources'   => array( $source ),
			),
			array(
				'code'      => AMP_Validation_Error_Taxonomy::INVALID_ATTRIBUTE_CODE,
				'node_name' => 'onclick',
				'sources'   => array( $source ),
			),
		);

		return array(
			'invalid_element'       => array(
				AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS,
				'script',
				$errors,
			),
			'removed_attributes'    => array(
				AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES,
				'onclick',
				$errors,
			),
			'sources_invalid_input' => array(
				AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT,
				'amp',
				$errors,
			),
		);
	}

	/**
	 * Test for output_custom_column()
	 *
	 * @dataProvider get_custom_columns
	 * @covers       AMP_Invalid_URL_Post_Type::output_custom_column()
	 *
	 * @param string $column_name    The name of the column.
	 * @param string $expected_value The value that is expected to be present in the column markup.
	 * @param array  $errors         Errors.
	 */
	public function test_output_custom_column( $column_name, $expected_value, $errors ) {
		AMP_Validation_Manager::init();
		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors( $errors, home_url( '/' ) );

		ob_start();
		AMP_Invalid_URL_Post_Type::output_custom_column( $column_name, $invalid_url_post_id );
		$this->assertContains( $expected_value, ob_get_clean() );
	}

	/**
	 * Test for filter_row_actions()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::filter_row_actions()
	 */
	public function test_filter_row_actions() {
		add_theme_support( 'amp' );
		AMP_Validation_Manager::init();

		$initial_actions = array(
			'trash' => '<a href="https://example.com">Trash</a>',
		);

		$invalid_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			home_url( '/' )
		);

		$this->assertEquals( $initial_actions, AMP_Invalid_URL_Post_Type::filter_row_actions( $initial_actions, $this->factory()->post->create_and_get() ) );

		$actions = AMP_Invalid_URL_Post_Type::filter_row_actions( $initial_actions, get_post( $invalid_post_id ) );
		$this->assertArrayNotHasKey( 'inline hide-if-no-js', $actions );
		$this->assertArrayHasKey( 'view', $actions );
		$this->assertArrayHasKey( AMP_Invalid_URL_Post_Type::VALIDATE_ACTION, $actions );

		$this->assertEquals( $initial_actions['trash'], $actions['trash'] );
	}

	/**
	 * Test for filter_bulk_actions()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::filter_bulk_actions()
	 */
	public function test_filter_bulk_actions() {
		$initial_action = array(
			'edit'   => 'Edit',
			'trash'  => 'Trash',
			'delete' => 'Trash permanently',
		);
		$actions        = AMP_Invalid_URL_Post_Type::filter_bulk_actions( $initial_action );
		$this->assertFalse( isset( $action['edit'] ) );
		$this->assertEquals( 'Recheck', $actions[ AMP_Invalid_URL_Post_Type::BULK_VALIDATE_ACTION ] );
		$this->assertEquals( 'Forget', $actions['trash'] );
		$this->assertEquals( 'Forget permanently', $actions['delete'] );
	}

	/**
	 * Test for handle_bulk_action()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::handle_bulk_action()
	 */
	public function test_handle_bulk_action() {
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		add_theme_support( 'amp', array( 'paired' => true ) );
		AMP_Validation_Manager::init();

		$invalid_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			home_url( '/' )
		);

		$initial_redirect = admin_url( 'plugins.php' );
		$items            = array( $invalid_post_id );
		$urls_tested      = (string) count( $items );

		$_GET[ AMP_Invalid_URL_Post_Type::URLS_TESTED ] = $urls_tested;

		// The action isn't correct, so the callback should return the URL unchanged.
		$this->assertEquals( $initial_redirect, AMP_Invalid_URL_Post_Type::handle_bulk_action( $initial_redirect, 'trash', $items ) );

		$that   = $this;
		$filter = function() use ( $that ) {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION:' . wp_json_encode( array(
						'results' => array_map(
							function( $error ) {
								return array_merge(
									compact( 'error' ),
									array( 'sanitized' => false )
								);
							},
							$that->get_mock_errors()
						),
					) )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$this->assertEquals(
			add_query_arg(
				array(
					AMP_Invalid_URL_Post_Type::URLS_TESTED => $urls_tested,
					AMP_Invalid_URL_Post_Type::REMAINING_ERRORS => count( $items ),
				),
				$initial_redirect
			),
			AMP_Invalid_URL_Post_Type::handle_bulk_action( $initial_redirect, AMP_Invalid_URL_Post_Type::BULK_VALIDATE_ACTION, $items )
		);
		remove_filter( 'pre_http_request', $filter, 10 );

		// Test error scenario.
		add_filter( 'pre_http_request', function() {
			return array(
				'body' => '<html></html>',
			);
		} );
		$this->assertEquals(
			add_query_arg(
				array(
					AMP_Invalid_URL_Post_Type::URLS_TESTED => $urls_tested,
					'amp_validate_error'                   => array( 'response_comment_absent' ),
				),
				$initial_redirect
			),
			AMP_Invalid_URL_Post_Type::handle_bulk_action( $initial_redirect, AMP_Invalid_URL_Post_Type::BULK_VALIDATE_ACTION, $items )
		);
	}

	/**
	 * Test for print_admin_notice()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::print_admin_notice()
	 */
	public function test_print_admin_notice() {
		add_theme_support( 'amp' );
		AMP_Validation_Manager::init();

		ob_start();
		AMP_Invalid_URL_Post_Type::print_admin_notice();
		$this->assertEmpty( ob_get_clean() );

		$_GET['post_type'] = 'post';
		ob_start();
		AMP_Invalid_URL_Post_Type::print_admin_notice();
		$this->assertEmpty( ob_get_clean() );

		set_current_screen( 'edit.php' );
		get_current_screen()->post_type = AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG;

		$_GET[ AMP_Invalid_URL_Post_Type::REMAINING_ERRORS ] = '1';
		$_GET[ AMP_Invalid_URL_Post_Type::URLS_TESTED ]      = '1';
		ob_start();
		AMP_Invalid_URL_Post_Type::print_admin_notice();
		$this->assertContains( 'The rechecked URL still has unaccepted validation errors', ob_get_clean() );

		$_GET[ AMP_Invalid_URL_Post_Type::URLS_TESTED ] = '2';
		ob_start();
		AMP_Invalid_URL_Post_Type::print_admin_notice();
		$this->assertContains( 'The rechecked URLs still have unaccepted validation errors', ob_get_clean() );

		$_GET[ AMP_Invalid_URL_Post_Type::REMAINING_ERRORS ] = '0';
		ob_start();
		AMP_Invalid_URL_Post_Type::print_admin_notice();
		$this->assertContains( 'The rechecked URLs are free of unaccepted validation errors', ob_get_clean() );

		$_GET[ AMP_Invalid_URL_Post_Type::URLS_TESTED ] = '1';
		ob_start();
		AMP_Invalid_URL_Post_Type::print_admin_notice();
		$this->assertContains( 'The rechecked URL is free of unaccepted validation errors', ob_get_clean() );

		$_GET['amp_validate_error'] = array( 'http_request_failed' );
		ob_start();
		AMP_Invalid_URL_Post_Type::print_admin_notice();
		$this->assertContains( 'Failed to fetch URL(s) to validate', ob_get_clean() );

		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * Test for handle_validate_request()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::handle_validate_request()
	 */
	public function test_handle_validate_request() {
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		add_theme_support( 'amp', array( 'paired' => true ) );
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		AMP_Validation_Manager::init();

		$post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			home_url( '/' )
		);

		$_REQUEST['_wpnonce'] = wp_create_nonce( AMP_Invalid_URL_Post_Type::NONCE_ACTION );

		$exception = null;
		add_filter( 'wp_redirect', function( $url, $status ) {
			throw new Exception( $url, $status );
		}, 10, 2 );

		$that   = $this;
		$filter = function() use ( $that ) {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION:' . wp_json_encode( array(
						'results' => array_map(
							function( $error ) {
								return array_merge(
									compact( 'error' ),
									array( 'sanitized' => false )
								);
							},
							$that->get_mock_errors()
						),
					) )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );

		$handle_validate_request = function() {
			try {
				AMP_Invalid_URL_Post_Type::handle_validate_request();
			} catch ( Exception $exception ) {
				return $exception;
			}
			return null;
		};

		// Test validating with missing args.
		$exception = $handle_validate_request();
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith(
			'/edit.php?post_type=amp_invalid_url&amp_validate_error=missing_url&amp_urls_tested=0',
			$exception->getMessage()
		);
		unset( $_GET['post'] );

		// Test validating for a non-valid post.
		$_GET['post'] = 1234567890;
		$exception    = $handle_validate_request();
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith(
			'/edit.php?post_type=amp_invalid_url&amp_validate_error=invalid_post&amp_urls_tested=0',
			$exception->getMessage()
		);
		unset( $_GET['post'] );

		// Test validating for a non-valid post type.
		$_GET['post'] = $this->factory()->post->create();
		$exception    = $handle_validate_request();
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith(
			'/edit.php?post_type=amp_invalid_url&amp_validate_error=invalid_post&amp_urls_tested=0',
			$exception->getMessage()
		);
		unset( $_GET['post'] );

		// Verify that redirect is happening for a successful case.
		$_GET['post'] = $post_id;
		$exception    = $handle_validate_request();
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith(
			sprintf( 'post.php?post=%s&action=edit&amp_urls_tested=1&amp_remaining_errors=2', $post_id ),
			$exception->getMessage()
		);
		unset( $_GET['post'] );

		// Test validating by URL.
		$_GET['url'] = home_url( '/' );
		$exception   = $handle_validate_request();
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith(
			sprintf( 'post.php?post=%s&action=edit&amp_urls_tested=1&amp_remaining_errors=2', $post_id ),
			$exception->getMessage()
		);

		// Test validating by URL which doesn't have a post already.
		$_GET['url'] = home_url( '/new-URL/' );
		$exception   = $handle_validate_request();
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith(
			'&action=edit&amp_urls_tested=1&amp_remaining_errors=2',
			$exception->getMessage()
		);

		// Test validating a bad URL.
		$_GET['url'] = 'http://badurl.example.com/';
		$exception   = $handle_validate_request();
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith(
			'wp-admin/edit.php?post_type=amp_invalid_url&amp_validate_error=illegal_url&amp_urls_tested=0',
			$exception->getMessage()
		);
	}

	/**
	 * Test for recheck_post()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::recheck_post()
	 */
	public function test_recheck_post() {
		AMP_Validation_Manager::init();

		$r = AMP_Invalid_URL_Post_Type::recheck_post( 'nope' );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'missing_post', $r->get_error_code() );

		$r = AMP_Invalid_URL_Post_Type::recheck_post( $this->factory()->post->create() );
		$this->assertInstanceOf( 'WP_Error', $r );
		$this->assertEquals( 'missing_url', $r->get_error_code() );

		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			home_url( '/' )
		);
		add_filter( 'pre_http_request', function() {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION:' . wp_json_encode( array(
						'results' => array(
							array(
								'sanitized' => false,
								'error'     => array(
									'code' => 'bar',
								),
							),
							array(
								'sanitized' => false,
								'error'     => array(
									'code' => 'baz',
								),
							),
						),
					) )
				),
			);
		} );

		$r = AMP_Invalid_URL_Post_Type::recheck_post( $invalid_url_post_id );
		$this->assertInternalType( 'array', $r );
		$this->assertCount( 2, $r );
		$this->assertEquals( 'bar', $r[0]['error']['code'] );
		$this->assertEquals( 'baz', $r[1]['error']['code'] );

		$errors = AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors( $invalid_url_post_id );
		$this->assertCount( 2, $errors );
		foreach ( $errors as $i => $error ) {
			$this->assertEquals(
				$r[ $i ]['error'],
				$error['data']
			);
		}
	}

	/**
	 * Test for handle_validation_error_status_update()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::handle_validation_error_status_update()
	 */
	public function test_handle_validation_error_status_update() {
		global $post;
		AMP_Validation_Manager::init();

		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$_REQUEST[ AMP_Invalid_URL_Post_Type::UPDATE_POST_TERM_STATUS_ACTION . '_nonce' ] = wp_create_nonce( AMP_Invalid_URL_Post_Type::UPDATE_POST_TERM_STATUS_ACTION );
		AMP_Invalid_URL_Post_Type::handle_validation_error_status_update(); // No-op since no post.

		$error = array( 'code' => 'foo' );

		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array( $error ),
			home_url( '/' )
		);
		add_filter( 'pre_http_request', function() use ( $error ) {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION:' . wp_json_encode( array(
						'results' => array(
							array(
								'sanitized' => false,
								'error'     => $error,
							),
						),
					) )
				),
			);
		} );

		$post = get_post( $invalid_url_post_id ); // WPCS: override ok.

		$errors = AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors( $invalid_url_post_id );

		$_POST[ AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ] = array(
			$errors[0]['term']->slug => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS,
		);

		add_filter( 'wp_redirect', function( $url, $status ) {
			throw new Exception( $url, $status );
		}, 10, 2 );
		$exception = null;
		try {
			AMP_Invalid_URL_Post_Type::handle_validation_error_status_update();
		} catch ( Exception $e ) {
			$exception = $e;
		}
		$this->assertInstanceOf( 'Exception', $exception );
		$this->assertEquals( 302, $exception->getCode() );
		$this->assertStringEndsWith( 'action=edit&amp_taxonomy_terms_updated=1&amp_remaining_errors=0', $exception->getMessage() );
	}

	/**
	 * Test for enqueue_edit_post_screen_scripts()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::enqueue_edit_post_screen_scripts()
	 */
	public function test_enqueue_edit_post_screen_scripts() {
		wp_enqueue_script( 'autosave' );
		set_current_screen( 'index.php' );
		AMP_Invalid_URL_Post_Type::enqueue_edit_post_screen_scripts();
		$this->assertTrue( wp_script_is( 'autosave', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'amp-invalid-url-post-edit-screen', 'enqueued' ) );

		global $pagenow;
		$pagenow = 'post.php';
		set_current_screen( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG );
		AMP_Invalid_URL_Post_Type::enqueue_edit_post_screen_scripts();
		$this->assertFalse( wp_script_is( 'autosave', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'amp-invalid-url-post-edit-screen', 'enqueued' ) );
		$pagenow = null;
	}

	/**
	 * Test for add_meta_boxes()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::add_meta_boxes()
	 */
	public function test_add_meta_boxes() {
		global $wp_meta_boxes;
		AMP_Invalid_URL_Post_Type::add_meta_boxes();
		$side_meta_box = $wp_meta_boxes[ AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ]['side']['default'][ AMP_Invalid_URL_Post_Type::STATUS_META_BOX ];
		$this->assertEquals( AMP_Invalid_URL_Post_Type::STATUS_META_BOX, $side_meta_box['id'] );
		$this->assertEquals( 'Status', $side_meta_box['title'] );
		$this->assertEquals(
			array(
				self::TESTED_CLASS,
				'print_status_meta_box',
			),
			$side_meta_box['callback']
		);

		$full_meta_box = $wp_meta_boxes[ AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ]['normal']['default'][ AMP_Invalid_URL_Post_Type::VALIDATION_ERRORS_META_BOX ];
		$this->assertEquals( AMP_Invalid_URL_Post_Type::VALIDATION_ERRORS_META_BOX, $full_meta_box['id'] );
		$this->assertEquals( 'Validation Errors', $full_meta_box['title'] );
		$this->assertEquals(
			array(
				self::TESTED_CLASS,
				'print_validation_errors_meta_box',
			),
			$full_meta_box['callback']
		);

		$contexts = $wp_meta_boxes[ AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ]['side'];
		foreach ( $contexts as $context ) {
			$this->assertFalse( $context['submitdiv'] );
		}
	}

	/**
	 * Test for print_status_meta_box()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::print_status_meta_box()
	 */
	public function test_print_status_meta_box() {
		AMP_Validation_Manager::init();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );

		$invalid_url_post_id = AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'foo' ),
			),
			home_url( '/' )
		);

		$post_storing_error = get_post( $invalid_url_post_id );

		ob_start();
		AMP_Invalid_URL_Post_Type::print_status_meta_box( get_post( $invalid_url_post_id ) );
		$output = ob_get_clean();

		$this->assertContains( date_i18n( 'M j, Y @ H:i', strtotime( $post_storing_error->post_date ) ), $output );
		$this->assertContains( 'Last checked:', $output );
		$this->assertContains( 'Forget', $output );
		$this->assertContains( esc_url( get_delete_post_link( $post_storing_error->ID ) ), $output );
		$this->assertContains( 'misc-pub-section', $output );
	}

	/**
	 * Test for print_validation_errors_meta_box()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::print_validation_errors_meta_box()
	 */
	public function test_print_validation_errors_meta_box() {
		AMP_Validation_Manager::init();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$post_id = AMP_Invalid_URL_Post_Type::store_validation_errors( $this->get_mock_errors(), home_url( '/' ) );
		ob_start();
		AMP_Invalid_URL_Post_Type::print_validation_errors_meta_box( get_post( $post_id ) );
		$output = ob_get_clean();

		$this->assertContains( '<details', $output );
		$this->assertContains( 'script', $output );
		$this->assertContains( 'onclick', $output );
	}

	/**
	 * Test for print_url_as_title()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::print_url_as_title()
	 */
	public function test_print_url_as_title() {
		$post_wrong_post_type = $this->factory()->post->create_and_get();

		// The $post has the wrong post type, so the method should exit without echoing anything.
		ob_start();
		AMP_Invalid_URL_Post_Type::print_url_as_title( $post_wrong_post_type );
		$this->assertEmpty( ob_get_clean() );

		// The post type is correct, but it doesn't have a validation URL associated with it, so this shouldn't output anything.
		$post_correct_post_type = $this->factory()->post->create_and_get( array(
			'post-type' => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
		) );
		ob_start();
		AMP_Invalid_URL_Post_Type::print_url_as_title( $post_correct_post_type );
		$this->assertEmpty( ob_get_clean() );

		// The post has the correct type and a validation URL in the title, so this should output markup.
		$url                    = 'https://example.com';
		$post_correct_post_type = $this->factory()->post->create_and_get( array(
			'post_type'  => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
			'post_title' => $url,
		) );
		ob_start();
		AMP_Invalid_URL_Post_Type::print_url_as_title( $post_correct_post_type );
		$output = ob_get_clean();
		$this->assertContains( '<h2 class="amp-invalid-url">', $output );
		$this->assertContains( $url, $output );
	}

	/**
	 * Test for filter_the_title_in_post_list_table()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::filter_the_title_in_post_list_table()
	 */
	public function test_filter_the_title_in_post_list_table() {
		global $current_screen;
		$post  = $this->factory()->post->create_and_get();
		$title = 'https://example.com/baz';
		set_current_screen( 'front' );

		// The first conditional isn't true yet, so $title should be unchanged.
		$this->assertEquals( $title, AMP_Invalid_URL_Post_Type::filter_the_title_in_post_list_table( $title, $post ) );

		/*
		 * The first conditional still isn't true yet, as the $post->post_type isn't correct.
		 * So this should again return $ttile unchanged.
		 */
		set_current_screen( 'edit.php' );
		$current_screen->post_type = AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG;
		$this->assertEquals( $title, AMP_Invalid_URL_Post_Type::filter_the_title_in_post_list_table( $title, $post ) );

		// The conditional should be true, and this should return the filtered $title.
		$post_correct_post_type = $this->factory()->post->create_and_get( array(
			'post_type' => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
		) );
		$this->assertEquals( '/baz', AMP_Invalid_URL_Post_Type::filter_the_title_in_post_list_table( $title, $post_correct_post_type ) );
	}

	/**
	 * Test render_post_filters.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::render_post_filters()
	 */
	public function test_render_post_filters() {
		set_current_screen( 'edit.php' );
		$number_of_errors = 20;
		for ( $i = 0; $i < $number_of_errors; $i++ ) {
			$invalid_url_post      = $this->factory()->post->create( array( 'post_type' => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ) );
			$validation_error_term = $this->factory()->term->create( array(
				'taxonomy'    => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
				'description' => wp_json_encode( $this->get_mock_errors() ),
				'term_group'  => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
			) );

			// Associate the validation error term with a URL.
			wp_set_post_terms(
				$invalid_url_post,
				$validation_error_term,
				AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG
			);
		}

		$new_error_count               = sprintf(
			'With New Errors <span class="count">(%d)</span>',
			$number_of_errors
		);
		$correct_post_type             = AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG;
		$wrong_post_type               = 'page';
		$correct_which_second_argument = 'top';
		$wrong_which_second_argument   = 'bottom';

		// This has an incorrect post type as the first argument, so it should not output anything.
		ob_start();
		AMP_Invalid_URL_Post_Type::render_post_filters( $wrong_post_type, $correct_which_second_argument );
		$this->assertEmpty( ob_get_clean() );

		// This has an incorrect second argument, so again it should not output anything.
		ob_start();
		AMP_Invalid_URL_Post_Type::render_post_filters( $correct_post_type, $wrong_which_second_argument );
		$this->assertEmpty( ob_get_clean() );

		// This is now on the invalid URL post type edit.php screen, so it should output a <select> element.
		ob_start();
		AMP_Invalid_URL_Post_Type::render_post_filters( $correct_post_type, $correct_which_second_argument );
		$this->assertContains( $new_error_count, ob_get_clean() );
	}

	/**
	 * Test for get_recheck_url()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::get_recheck_url()
	 */
	public function test_get_recheck_url() {
		AMP_Validation_Manager::init();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$post_id = AMP_Invalid_URL_Post_Type::store_validation_errors( $this->get_mock_errors(), home_url( '/' ) );
		$link    = AMP_Invalid_URL_Post_Type::get_recheck_url( get_post( $post_id ) );
		$this->assertContains( AMP_Invalid_URL_Post_Type::VALIDATE_ACTION, $link );
		$this->assertContains( wp_create_nonce( AMP_Invalid_URL_Post_Type::NONCE_ACTION ), $link );
	}

	/**
	 * Test for filter_dashboard_glance_items()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::filter_dashboard_glance_items()
	 */
	public function test_filter_dashboard_glance_items() {

		// There are no validation errors, so this should return the argument unchanged.
		$this->assertEmpty( AMP_Invalid_URL_Post_Type::filter_dashboard_glance_items( array() ) );

		// Create validation errors, so that the method returns items.
		$post_id = $this->factory()->post->create();
		AMP_Invalid_URL_Post_Type::store_validation_errors(
			array(
				array( 'code' => 'accepted' ),
				array( 'code' => 'rejected' ),
				array( 'code' => 'new' ),
			),
			get_permalink( $post_id )
		);
		$items = AMP_Invalid_URL_Post_Type::filter_dashboard_glance_items( array() );
		$this->assertContains( '1 URL w/ new AMP errors', $items[0] );
		$this->assertContains( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, $items[0] );
		$this->assertContains( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR, $items[0] );
	}

	/**
	 * Test for filter_post_row_actions()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::filter_post_row_actions()
	 */
	public function test_filter_post_row_actions() {
		$this->assertEquals( array(), AMP_Invalid_URL_Post_Type::filter_post_row_actions( array(), null ) );

		$actions = array(
			'trash'  => '',
			'delete' => '',
		);

		$post = $this->factory()->post->create_and_get( array(
			'post_type' => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
			'title'     => 'My Post',
		) );

		$filtered_actions = AMP_Invalid_URL_Post_Type::filter_post_row_actions( $actions, $post );

		$this->assertContains( 'Forget</a>', $filtered_actions['trash'] );
		$this->assertContains( 'Forget Permanently</a>', $filtered_actions['delete'] );

	}

	/**
	 * Test for filter_table_views()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::filter_table_views()
	 */
	public function test_filter_table_views() {
		$this->assertEquals( array(), AMP_Invalid_URL_Post_Type::filter_table_views( array() ) );

		$views = array(
			'trash' => 'Trash',
		);

		$filtered_views = AMP_Invalid_URL_Post_Type::filter_table_views( $views );

		$this->assertEquals( 'Forgotten', $filtered_views['trash'] );
	}

	/**
	 * Test for filter_bulk_post_updated_messages()
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::filter_bulk_post_updated_messages()
	 */
	public function test_filter_bulk_post_updated_messages() {
		set_current_screen( 'index.php' );

		$this->assertEquals( array(), AMP_Invalid_URL_Post_Type::filter_bulk_post_updated_messages( array(), array() ) );

		set_current_screen( 'edit.php' );
		get_current_screen()->id = sprintf( 'edit-%s', AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG );

		$messages = array(
			'post' => array(),
		);

		$filtered_messages = AMP_Invalid_URL_Post_Type::filter_bulk_post_updated_messages( $messages, array(
			'deleted'   => 1,
			'trashed'   => 99,
			'untrashed' => 99,
		) );

		$this->assertEquals( '%s invalid AMP page permanently forgotten.', $filtered_messages['post']['deleted'] );
		$this->assertEquals( '%s invalid AMP pages forgotten.', $filtered_messages['post']['trashed'] );
		$this->assertEquals( '%s invalid AMP pages unforgotten.', $filtered_messages['post']['untrashed'] );
	}


	/**
	 * Gets mock errors for tests.
	 *
	 * @return array $errors[][] {
	 *     The data of the validation errors.
	 *
	 *     @type string    $code        Error code.
	 *     @type string    $node_name   Name of removed node.
	 *     @type string    $parent_name Name of parent node.
	 *     @type array[][] $sources     Source data, including plugins and themes.
	 * }
	 */
	public function get_mock_errors() {
		return array(
			array(
				'code'            => AMP_Validation_Error_Taxonomy::INVALID_ELEMENT_CODE,
				'node_name'       => 'script',
				'parent_name'     => 'div',
				'node_attributes' => array(),
				'sources'         => array(
					array(
						'type' => 'plugin',
						'name' => 'amp',
					),
				),
			),
			array(
				'code'               => AMP_Validation_Error_Taxonomy::INVALID_ATTRIBUTE_CODE,
				'node_name'          => 'onclick',
				'parent_name'        => 'div',
				'element_attributes' => array(
					'onclick' => '',
				),
				'sources'            => array(
					array(
						'type' => 'plugin',
						'name' => 'amp',
					),
				),
			),
		);
	}
}
