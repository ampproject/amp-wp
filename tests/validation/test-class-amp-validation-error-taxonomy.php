<?php
/**
 * Tests for AMP_Validation_Error_Taxonomy class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Validation_Error_Taxonomy class.
 *
 * @covers AMP_Validation_Error_Taxonomy
 */
class Test_AMP_Validation_Error_Taxonomy extends \WP_UnitTestCase {

	/**
	 * The tested class.
	 *
	 * @var string
	 */
	const TESTED_CLASS = 'AMP_Validation_Error_Taxonomy';

	/**
	 * A mock acceptable error code.
	 *
	 * @var string
	 */
	const MOCK_ACCEPTABLE_ERROR = 'illegal_css_at_rule';

	/**
	 * Resets the state after each test method.
	 */
	public function tearDown() {
		remove_theme_support( 'amp' );
		remove_filter( 'amp_validation_error_sanitized', '__return_true' );
		remove_all_filters( 'amp_validation_error_sanitized' );
		remove_all_filters( 'terms_clauses' );
		parent::tearDown();
	}

	/**
	 * Test register.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::register()
	 */
	public function test_register() {
		global $wp_taxonomies;
		add_theme_support( 'amp' );

		AMP_Validation_Error_Taxonomy::register();
		$taxonomy_object = $wp_taxonomies[ AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ];

		$this->assertFalse( $taxonomy_object->public );
		$this->assertTrue( $taxonomy_object->show_ui );
		$this->assertFalse( $taxonomy_object->show_tagcloud );
		$this->assertFalse( $taxonomy_object->show_in_quick_edit );
		$this->assertFalse( $taxonomy_object->hierarchical );
		$this->assertTrue( $taxonomy_object->show_in_menu );
		$this->assertFalse( $taxonomy_object->meta_box_cb );
		$this->assertEquals( 'AMP Validation Error Index', $taxonomy_object->label );
		$this->assertEquals( 'do_not_allow', $taxonomy_object->cap->assign_terms );
		$this->assertEquals( array( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ), $taxonomy_object->object_type );

		$labels = $taxonomy_object->labels;
		$this->assertEquals( 'AMP Validation Error Index', $labels->name );
		$this->assertEquals( 'AMP Validation Error', $labels->singular_name );
		$this->assertEquals( 'Search AMP Validation Errors', $labels->search_items );
		$this->assertEquals( 'All AMP Validation Errors', $labels->all_items );
		$this->assertEquals( 'Edit AMP Validation Error', $labels->edit_item );
		$this->assertEquals( 'Update AMP Validation Error', $labels->update_item );
		$this->assertEquals( 'Error Index', $labels->menu_name );
		$this->assertEquals( 'Back to AMP Validation Errors', $labels->back_to_items );
		$this->assertEquals( 'Frequent Validation Errors', $labels->popular_items );
		$this->assertEquals( 'View Validation Error', $labels->view_item );
		$this->assertEquals( 'Add New Validation Error', $labels->add_new_item );
		$this->assertEquals( 'New Validation Error Hash', $labels->new_item_name );
		$this->assertEquals( 'No validation errors found.', $labels->not_found );
		$this->assertEquals( 'Validation Error', $labels->no_terms );
		$this->assertEquals( 'Validation errors navigation', $labels->items_list_navigation );
		$this->assertEquals( 'Validation errors list', $labels->items_list );
		$this->assertEquals( 'Most Used Validation Errors', $labels->most_used );
	}

	/**
	 * Test should_show_in_menu.
	 *
	 * @covers AMP_Validation_Error_Taxonomy::should_show_in_menu()
	 */
	public function test_should_show_in_menu() {
		global $pagenow;
		add_theme_support( 'amp' );
		$this->assertTrue( AMP_Validation_Error_Taxonomy::should_show_in_menu() );

		remove_theme_support( 'amp' );
		$this->assertFalse( AMP_Validation_Error_Taxonomy::should_show_in_menu() );

		$pagenow          = 'edit-tags.php'; // WPCS: override ok.
		$_GET['taxonomy'] = 'post_tag';
		$this->assertFalse( AMP_Validation_Error_Taxonomy::should_show_in_menu() );

		$_GET['taxonomy'] = AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG;
		$this->assertTrue( AMP_Validation_Error_Taxonomy::should_show_in_menu() );
	}

	/**
	 * Test prepare_validation_error_taxonomy_term.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term()
	 */
	public function test_prepare_validation_error_taxonomy_term() {
		$error              = $this->get_mock_error();
		$sources            = array(
			array(
				'type' => 'plugin',
				'name' => 'baz',
			),
		);
		$error_with_sources = array_merge( $error, compact( 'sources' ) );
		ksort( $error );

		$description = wp_json_encode( $error );
		$term_slug   = md5( $description );
		$this->assertEquals(
			AMP_Validation_Error_Taxonomy::prepare_validation_error_taxonomy_term( $error_with_sources ),
			array(
				'slug'        => $term_slug,
				'name'        => $term_slug,
				'description' => $description,
			)
		);
	}

	/**
	 * Test is_validation_error_sanitized.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::is_validation_error_sanitized()
	 */
	public function test_is_validation_error_sanitized() {
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		$this->assertFalse( AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $this->get_mock_error() ) );

		// Trigger Native AMP, which makes all errors accepted.
		add_theme_support( 'amp' );
		$this->assertTrue( AMP_Validation_Error_Taxonomy::is_validation_error_sanitized( $this->get_mock_error() ) );
	}

	/**
	 * Test get_validation_error_sanitization.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::get_validation_error_sanitization()
	 */
	public function test_get_validation_error_sanitization() {
		AMP_Options_Manager::update_option( 'force_sanitization', false );
		$this->assertEquals(
			array(
				'forced'      => false,
				'status'      => 0,
				'term_status' => 0,
			),
			AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $this->get_mock_error() )
		);

		// Trigger Native AMP, which should result in 'forced' => 'with_option'.
		add_theme_support( 'amp' );
		$this->assertEquals(
			array(
				'forced'      => 'with_option',
				'status'      => 1,
				'term_status' => 0,
			),
			AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $this->get_mock_error() )
		);

		add_filter( 'amp_validation_error_sanitized', '__return_true' );
		$this->assertEquals(
			array(
				'forced'      => 'with_filter',
				'status'      => 1,
				'term_status' => 0,
			),
			AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $this->get_mock_error() )
		);
	}

	/**
	 * Test accept_validation_errors.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::accept_validation_errors()
	 */
	public function test_accept_validation_errors() {
		$error = $this->get_mock_error();
		AMP_Validation_Error_Taxonomy::accept_validation_errors( array() );
		$this->assertNull( apply_filters( 'amp_validation_error_sanitized', null, $error ) );
		remove_all_filters( 'amp_validation_error_sanitized' );

		AMP_Validation_Error_Taxonomy::accept_validation_errors( array( self::MOCK_ACCEPTABLE_ERROR => true ) );
		$this->assertTrue( apply_filters( 'amp_validation_error_sanitized', null, $error ) );
		remove_all_filters( 'amp_validation_error_sanitized' );

		AMP_Validation_Error_Taxonomy::accept_validation_errors( true );
		$this->assertTrue( apply_filters( 'amp_validation_error_sanitized', null, $error ) );
		remove_all_filters( 'amp_validation_error_sanitized' );
	}

	/**
	 * Test is_array_subset.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::is_array_subset()
	 */
	public function test_is_array_subset() {
		$error = $this->get_mock_error();
		$this->assertTrue( AMP_Validation_Error_Taxonomy::is_array_subset( $error, $error ) );

		// The superset argument now has an extra key and value, but the superset still has all of the values of the subset.
		$this->assertTrue( AMP_Validation_Error_Taxonomy::is_array_subset( array_merge( $error, array( 'foo' => 'bar' ) ), $error ) );

		// The subset has a key and value that the superset doesn't have, so this should be false.
		$this->assertFalse( AMP_Validation_Error_Taxonomy::is_array_subset( $error, array_merge( $error, array( 'foo' => 'bar' ) ) ) );

		$sources = array(
			array(
				'type' => 'plugin',
				'name' => 'foo',
			),
			array(
				'type' => 'theme',
				'name' => 'baz',
			),
		);

		/**
		 * Add only the plugin sources to the superset, but all of the sources to the subset.
		 * This should make is_array_subset() false, as the superset does not have all of the values of the subset.
		 */
		$superset = array_merge( $error, array( 'sources' => array( $sources[0] ) ) );
		$subset   = array_merge( $error, compact( 'sources' ) );
		$this->assertFalse( AMP_Validation_Error_Taxonomy::is_array_subset( $superset, $subset ) );
	}

	/**
	 * Test get_validation_error_count.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::get_validation_error_count()
	 */
	public function test_get_validation_error_count() {
		AMP_Validation_Error_Taxonomy::register();
		$this->assertEquals( 0, AMP_Validation_Error_Taxonomy::get_validation_error_count() );

		$this->factory()->term->create( array(
			'taxonomy' => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
		) );
		$this->assertEquals( 1, AMP_Validation_Error_Taxonomy::get_validation_error_count() );

		$terms_to_add = 11;
		for ( $i = 0; $i < $terms_to_add; $i++ ) {
			$this->factory()->term->create( array(
				'taxonomy' => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
			) );
		}
		$this->assertEquals( 1 + $terms_to_add, AMP_Validation_Error_Taxonomy::get_validation_error_count() );
	}

	/**
	 * Test filter_posts_where_for_validation_error_status.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_posts_where_for_validation_error_status()
	 */
	public function test_filter_posts_where_for_validation_error_status() {
		global $wpdb;

		$initial_where = 'WHERE foo-condition';
		$wp_query      = new WP_Query();

		// The conditional isn't met, so this shouldn't filter the WHERE clause.
		$this->assertEquals( $initial_where, AMP_Validation_Error_Taxonomy::filter_posts_where_for_validation_error_status( $initial_where, $wp_query ) );

		// Only the first part of the conditional is met, so this still shouldn't filter the WHERE clause.
		$wp_query->set( 'post_type', array( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ) );
		$this->assertEquals( $initial_where, AMP_Validation_Error_Taxonomy::filter_posts_where_for_validation_error_status( $initial_where, $wp_query ) );

		// The entire conditional should now be true, so this should filter the WHERE clause.
		$error_status = 1;
		$wp_query->set( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR, $error_status );
		$filtered_where = AMP_Validation_Error_Taxonomy::filter_posts_where_for_validation_error_status( $initial_where, $wp_query );
		$this->assertContains( 'SELECT 1', $filtered_where );
		$this->assertContains( 'INNER JOIN', $filtered_where );
		$this->assertContains( $wpdb->term_relationships, $filtered_where );
		$this->assertContains( $wpdb->term_taxonomy, $filtered_where );
		$this->assertContains( strval( $error_status ), $filtered_where );

		// Now that there is a query var for error type, that should also appear in the filtered WHERE clause.
		$error_type         = 'js_error';
		$escaped_error_type = 'js\\\\_error';
		$wp_query->set( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR, $error_type );
		$filtered_where = AMP_Validation_Error_Taxonomy::filter_posts_where_for_validation_error_status( $initial_where, $wp_query );
		$this->assertContains( 'SELECT 1', $filtered_where );
		$this->assertContains( strval( $error_status ), $filtered_where );
		$this->assertContains( $escaped_error_type, $filtered_where );
	}

	/**
	 * Test summarize_validation_errors.
	 *
	 * @covers AMP_Validation_Manager::summarize_validation_errors()
	 */
	public function test_summarize_validation_errors() {
		$attribute_node_name = 'button';
		$element_node_name   = 'nonexistent-element';
		$validation_errors   = array(
			array(
				'code'      => 'invalid_attribute',
				'node_name' => $attribute_node_name,
				'sources'   => array(
					array(
						'type' => 'plugin',
						'name' => 'foo',
					),
				),
			),
			array(
				'code'      => 'invalid_element',
				'node_name' => $element_node_name,
				'sources'   => array(
					array(
						'type' => 'theme',
						'name' => 'bar',
					),
				),
			),
		);

		$results          = AMP_Validation_Error_Taxonomy::summarize_validation_errors( $validation_errors );
		$expected_results = array(
			AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES => array(
				$attribute_node_name => 1,
			),
			AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS => array(
				$element_node_name => 1,
			),
			'sources_with_invalid_output' => array(
				'plugin' => array( 'foo' ),
				'theme'  => array( 'bar' ),
			),
		);
		$this->assertEquals( $expected_results, $results );
	}

	/**
	 * Test add_admin_hooks.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_admin_hooks()
	 */
	public function test_add_admin_hooks() {
		add_theme_support( 'amp' );
		AMP_Validation_Error_Taxonomy::register();

		// add_group_terms_clauses_filter() needs the screen to be set.
		set_current_screen( 'front' );
		AMP_Validation_Error_Taxonomy::add_admin_hooks();
		do_action( 'load-edit-tags.php' ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		$this->assertEquals( 10, has_action( 'redirect_term_location', array( self::TESTED_CLASS, 'add_term_filter_query_var' ) ) );
		$this->assertEquals( 10, has_action( 'load-edit-tags.php', array( self::TESTED_CLASS, 'add_group_terms_clauses_filter' ) ) );
		$this->assertEquals( 10, has_action( 'load-edit-tags.php', array( self::TESTED_CLASS, 'add_error_type_clauses_filter' ) ) );
		$this->assertEquals( 10, has_action( sprintf( 'after-%s-table', AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, array( self::TESTED_CLASS, 'render_taxonomy_filters' ) ) ) );
		$this->assertEquals( 10, has_action( sprintf( 'after-%s-table', AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, array( self::TESTED_CLASS, 'render_link_to_errors_by_url' ) ) ) );
		$this->assertEquals( 10, has_filter( 'user_has_cap', array( self::TESTED_CLASS, 'filter_user_has_cap_for_hiding_term_list_table_checkbox' ) ) );
		$this->assertEquals( 10, has_filter( 'terms_clauses', array( self::TESTED_CLASS, 'filter_terms_clauses_for_description_search' ) ) );
		$this->assertEquals( 10, has_action( 'admin_notices', array( self::TESTED_CLASS, 'add_admin_notices' ) ) );
		$this->assertEquals( 10, has_filter( 'tag_row_actions', array( self::TESTED_CLASS, 'filter_tag_row_actions' ) ) );
		$this->assertEquals( 10, has_action( 'admin_menu', array( self::TESTED_CLASS, 'add_admin_menu_validation_error_item' ) ) );
		$this->assertEquals( 10, has_filter( 'manage_' . AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG . '_custom_column', array( self::TESTED_CLASS, 'filter_manage_custom_columns' ) ) );
		$this->assertEquals( 10, has_filter( 'posts_where', array( self::TESTED_CLASS, 'filter_posts_where_for_validation_error_status' ) ) );
		$this->assertEquals( 10, has_filter( 'handle_bulk_actions-edit-' . AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, array( self::TESTED_CLASS, 'handle_validation_error_update' ) ) );
		$this->assertEquals( 10, has_action( 'load-edit-tags.php', array( self::TESTED_CLASS, 'handle_inline_edit_request' ) ) );

		$cb              = '<input type="checkbox" />';
		$initial_columns = array( 'cb' => $cb );
		$this->assertEquals(
			array(
				'cb'               => $cb,
				'error'            => 'Error',
				'status'           => 'Status',
				'details'          => 'Details',
				'created_date_gmt' => 'Last Seen',
				'posts'            => 'Found URLs',
				'error_type'       => 'Type',
			),
			apply_filters( 'manage_edit-' . AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG . '_columns', $initial_columns ) // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
		);

		// Assert that the 'query_vars' callback adds these query vars.
		$this->assertEmpty( array_diff(
			array( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR, AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR ),
			apply_filters( 'query_vars', array() )
		) );
	}

	/**
	 * Test add_group_terms_clauses_filter.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_group_terms_clauses_filter()
	 */
	public function test_add_group_terms_clauses_filter() {
		global $current_screen;

		set_current_screen( 'front' );
		$tested_filter = 'terms_clauses';
		remove_all_filters( $tested_filter );
		AMP_Validation_Error_Taxonomy::add_group_terms_clauses_filter();
		$this->assertFalse( has_filter( $tested_filter ) );

		// Only the first part of the conditional will be true, so this shouldn't add the filter.
		$current_screen->taxonomy = AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG;
		AMP_Validation_Error_Taxonomy::add_group_terms_clauses_filter();
		$this->assertFalse( has_filter( $tested_filter ) );

		// The entire conditional should be true, and this should add the filter.
		$_GET[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR ] = 1;
		AMP_Validation_Error_Taxonomy::add_group_terms_clauses_filter();
		$this->assertTrue( has_filter( $tested_filter ) );
	}

	/**
	 * Test add_term_filter_query_var.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_term_filter_query_var()
	 */
	public function test_add_term_filter_query_var() {
		$initial_url      = admin_url( 'edit-tags.php' );
		$correct_taxonomy = new WP_Taxonomy( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG );
		$wrong_taxonomy   = new WP_Taxonomy( 'category', 'post' );

		// Because the VALIDATION_ERROR_TYPE_QUERY_VAR isn't present in the POST request, this should return the $initial_url unchanged, without adding the query var.
		$this->assertEquals( $initial_url, AMP_Validation_Error_Taxonomy::add_term_filter_query_var( $initial_url, $wrong_taxonomy ) );

		// The $_POST does not have a value for VALIDATION_ERROR_TYPE_QUERY_VAR, so this should again return $initial_url unchanged.
		$wrong_query_var           = 'amp_incorrect_var';
		$_POST[ $wrong_query_var ] = '1';
		$this->assertEquals( $initial_url, AMP_Validation_Error_Taxonomy::add_term_filter_query_var( $initial_url, $wrong_taxonomy ) );

		// The $_POST has the VALIDATION_ERROR_TYPE_QUERY_VAR, but does not have a $taxonomy of AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG.
		$type_query_var_value = AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE;
		$_POST[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR ] = $type_query_var_value;
		$this->assertEquals( $initial_url, AMP_Validation_Error_Taxonomy::add_term_filter_query_var( $initial_url, $wrong_taxonomy ) );

		// The $_POST has the taxonomy, but does not have the right 'post_type'.
		$_POST['taxonomy']  = AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG;
		$_POST['post_type'] = 'post';
		$this->assertEquals( $initial_url, AMP_Validation_Error_Taxonomy::add_term_filter_query_var( $initial_url, $correct_taxonomy ) );

		// The $_POST has correct 'post_type', so this should add the VALIDATION_ERROR_TYPE_QUERY_VAR to the $initial_url.
		$_POST['post_type'] = AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG;
		$expected_url       = add_query_arg(
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR,
			$type_query_var_value,
			$initial_url
		);
		$this->assertEquals( $expected_url, AMP_Validation_Error_Taxonomy::add_term_filter_query_var( $initial_url, $correct_taxonomy ) );

		// The $_POST has a value for the accepted status, so this method should pass that to the redirect URL as a query var.
		$status_query_var_value = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPTED_STATUS;
		$_POST[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR ] = $status_query_var_value;
		$_POST[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR ]   = null;
		$expected_url = add_query_arg(
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR,
			$status_query_var_value,
			$initial_url
		);
		$this->assertEquals( $expected_url, AMP_Validation_Error_Taxonomy::add_term_filter_query_var( $initial_url, $correct_taxonomy ) );
	}

	/**
	 * Test add_error_type_clauses_filter.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_error_type_clauses_filter()
	 */
	public function test_add_error_type_clauses_filter() {
		global $current_screen;

		$initial_where   = 'foo';
		$initial_clauses = array( 'where' => $initial_where );
		$type            = AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE;
		$taxonomies      = array( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );

		set_current_screen( 'front' );
		$tested_filter = 'terms_clauses';
		remove_all_filters( $tested_filter );
		AMP_Validation_Error_Taxonomy::add_error_type_clauses_filter();
		$this->assertFalse( has_filter( $tested_filter ) );

		// The VALIDATION_ERROR_TYPE_QUERY_VAR isn't present, so this should not add the filter.
		$current_screen->taxonomy = AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG;
		AMP_Validation_Error_Taxonomy::add_error_type_clauses_filter();
		$this->assertFalse( has_filter( $tested_filter ) );

		// Both parts of the conditional should be true, and this should add the filter.
		$_GET[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR ] = $type;
		AMP_Validation_Error_Taxonomy::add_error_type_clauses_filter();
		$this->assertTrue( has_filter( $tested_filter ) );

		// Assert that the filter works as expected.
		$filtered_clauses = apply_filters( $tested_filter, $initial_clauses, $taxonomies );
		$this->assertContains( $initial_where, $filtered_clauses['where'] );
		$this->assertContains( 'AND tt.description LIKE', $filtered_clauses['where'] );

		// If $taxonomies does not have the AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG, the filter should return the clauses unchanged.
		$taxonomies = array( 'post_tag' );
		$this->assertEquals( $initial_clauses, apply_filters( $tested_filter, $initial_clauses, $taxonomies ) );
	}

	/**
	 * Test render_taxonomy_filters.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::render_taxonomy_filters()
	 */
	public function test_render_taxonomy_filters() {
		AMP_Validation_Error_Taxonomy::register();
		set_current_screen( 'edit-tags.php' );
		// Create one new error.
		$this->factory()->term->create( array(
			'taxonomy'    => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
			'description' => wp_json_encode( $this->get_mock_error() ),
			'term_group'  => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
		) );

		// When passing the wrong $taxonomy_name to the method, it should not output anything.
		ob_start();
		AMP_Validation_Error_Taxonomy::render_taxonomy_filters( 'category' );
		$this->assertEmpty( ob_get_clean() );

		// When there are two new errors, the <option> text should be plural, and have a count of (2).
		$this->factory()->term->create( array(
			'taxonomy'    => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
			'description' => wp_json_encode( $this->get_mock_error() ),
			'term_group'  => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
		) );
		ob_start();
		AMP_Validation_Error_Taxonomy::render_taxonomy_filters( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
		$this->assertContains( 'New Errors <span class="count">(2)</span>', ob_get_clean() );
	}

	/**
	 * Test render_link_to_errors_by_url.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::render_link_to_errors_by_url()
	 */
	public function test_render_link_to_errors_by_url() {
		// When passing the wrong $taxomomy argument, this should not render anything.
		ob_start();
		AMP_Validation_Error_Taxonomy::render_link_to_errors_by_url( 'category' );
		$this->assertEmpty( ob_get_clean() );

		// When passing the correct taxonomy, this should render the link.
		ob_start();
		AMP_Validation_Error_Taxonomy::render_link_to_errors_by_url( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
		$output = ob_get_clean();
		$this->assertContains( 'View errors by URL', $output );
		$this->assertContains(
			add_query_arg(
				'post_type',
				AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
				admin_url( 'edit.php' )
			),
			$output
		);
	}

	/**
	 * Test render_error_status_filter.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::render_error_status_filter()
	 */
	public function test_render_error_status_filter() {
		AMP_Validation_Error_Taxonomy::register();
		set_current_screen( 'post.php' );

		// When this is not on the correct screen, this should not render anything.
		ob_start();
		AMP_Validation_Error_Taxonomy::render_error_status_filter();
		$this->assertEmpty( ob_get_clean() );

		set_current_screen( 'edit.php' );
		$number_of_errors = 10;
		for ( $i = 0; $i < $number_of_errors; $i++ ) {
			$invalid_url_post      = $this->factory()->post->create( array( 'post_type' => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ) );
			$validation_error_term = $this->factory()->term->create( array(
				'taxonomy'    => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
				'description' => wp_json_encode( $this->get_mock_error() ),
				'term_group'  => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
			) );

			// Associate the validation error term with a URL so that it appears in a query.
			wp_set_post_terms(
				$invalid_url_post,
				$validation_error_term,
				AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG
			);
		}

		/*
		 * This is on another accepted screen, so this should again render markup.
		 * When there are 10 accepted errors, the <option> element for it should end with (10).
		 */
		set_current_screen( 'edit-tags.php' );
		ob_start();
		AMP_Validation_Error_Taxonomy::render_error_status_filter();
		$this->assertContains(
			sprintf(
				'New Errors <span class="count">(%d)</span>',
				$number_of_errors
			),
			ob_get_clean()
		);
	}

	/**
	 * Test get_error_types.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::get_error_types()
	 */
	public function test_get_error_types() {
		$this->assertEquals(
			array( 'html_element_error', 'html_attribute_error', 'js_error', 'css_error' ),
			AMP_Validation_Error_Taxonomy::get_error_types()
		);
	}

	/**
	 * Test render_error_type_filter.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::render_error_type_filter()
	 */
	public function test_render_error_type_filter() {
		set_current_screen( 'edit-tags.php' );
		$number_of_errors = 10;
		for ( $i = 0; $i < $number_of_errors; $i++ ) {
			$this->factory()->term->create( array(
				'taxonomy'    => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
				'description' => wp_json_encode( $this->get_mock_error() ),
				'term_group'  => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_STATUS,
			) );
		}

		// The strings below should be present.
		ob_start();
		AMP_Validation_Error_Taxonomy::render_taxonomy_filters( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
		$markup = ob_get_clean();

		$expected_to_contain = array(
			AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_TYPE_QUERY_VAR,
			AMP_Validation_Error_Taxonomy::HTML_ELEMENT_ERROR_TYPE,
			AMP_Validation_Error_Taxonomy::HTML_ATTRIBUTE_ERROR_TYPE,
			AMP_Validation_Error_Taxonomy::JS_ERROR_TYPE,
			AMP_Validation_Error_Taxonomy::CSS_ERROR_TYPE,
			'<script>',
		);

		foreach ( $expected_to_contain as $expected ) {
			$this->assertContains( $expected, $markup );
		}

		// When there are 10 errors with this status, its <option> element should have (10).
		$this->assertContains(
			sprintf(
				'New Errors <span class="count">(%d)</span>',
				$number_of_errors
			),
			$markup
		);

		// On the edit-tags.php page, the <option> text should not have 'With', like 'With JS Errors'.
		$this->assertNotContains( 'With', $markup );

		// On the edit.php page (Errors by URL), the <option> text should have 'With', like 'With JS Errors'.
		set_current_screen( 'edit.php' );
		ob_start();
		AMP_Validation_Error_Taxonomy::render_taxonomy_filters( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );
		$this->assertContains( 'With', ob_get_clean() );
	}

	/**
	 * Test filter_user_has_cap_for_hiding_term_list_table_checkbox.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_user_has_cap_for_hiding_term_list_table_checkbox()
	 */
	public function test_filter_user_has_cap_for_hiding_term_list_table_checkbox() {
		$initial_caps = array( 'manage_options' );
		$this->assertEquals( $initial_caps, AMP_Validation_Error_Taxonomy::filter_user_has_cap_for_hiding_term_list_table_checkbox( $initial_caps, array(), array() ) );

		$term_id_with_description = $this->factory()->term->create( array(
			'description' => wp_json_encode( array( 'foo' => 'bar' ) ),
		) );
		$args                     = array( 'delete_term', null, $term_id_with_description );
		$this->assertEquals( $initial_caps, AMP_Validation_Error_Taxonomy::filter_user_has_cap_for_hiding_term_list_table_checkbox( $initial_caps, array(), $args ) );

		$term_id_no_description = $this->factory()->term->create( array(
			'description' => wp_json_encode( array( 'foo' => 'bar' ) ),
		) );
		$args                   = array( 'delete_term', null, $term_id_no_description );
		$this->assertEquals( $initial_caps, AMP_Validation_Error_Taxonomy::filter_user_has_cap_for_hiding_term_list_table_checkbox( $initial_caps, array(), $args ) );
	}

	/**
	 * Test filter_terms_clauses_for_description_search.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_terms_clauses_for_description_search()
	 */
	public function test_filter_terms_clauses_for_description_search() {
		global $wpdb;

		$initial_where = '((t.name LIKE \'foo\'))';
		$clauses       = array( 'where' => $initial_where );
		$args          = array( 'search' => 'baz' );

		// The conditional shouldn't be true, so it shouldn't alter the 'where' clause.
		$this->assertEquals( $clauses, AMP_Validation_Error_Taxonomy::filter_terms_clauses_for_description_search( $clauses, array(), $args ) );

		// The conditional should be true, so test the preg_replace() call for $clauses['where'].
		$clauses = AMP_Validation_Error_Taxonomy::filter_terms_clauses_for_description_search( $clauses, array( AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ), $args );
		$this->assertContains( '(tt.description LIKE ', $clauses['where'] );
		$this->assertContains( $wpdb->esc_like( $args['search'] ), $clauses['where'] );
	}

	/**
	 * Test add_admin_notices.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_admin_notices()
	 */
	public function test_add_admin_notices() {
		global $current_screen;
		set_current_screen( 'edit.php' );

		// Test that the method exits when the first conditional isn't true.
		ob_start();
		AMP_Validation_Error_Taxonomy::add_admin_notices();
		$this->assertEmpty( ob_get_clean() );

		// Test the first conditional, where the error is accepted.
		$_GET['amp_actioned']       = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPT_ACTION;
		$count                      = 5;
		$_GET['amp_actioned_count'] = $count;
		$current_screen->taxonomy   = AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG;
		ob_start();
		AMP_Validation_Error_Taxonomy::add_admin_notices();
		$message = ob_get_clean();
		$this->assertEquals(
			sprintf( '<div class="notice notice-success is-dismissible"><p>Accepted %s errors. They will no longer block related URLs from being served as AMP.</p></div>', $count ),
			$message
		);

		// Test the second conditional, where the error is rejected.
		$_GET['amp_actioned'] = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECT_ACTION;
		ob_start();
		AMP_Validation_Error_Taxonomy::add_admin_notices();
		$message = ob_get_clean();
		$this->assertEquals(
			sprintf( '<div class="notice notice-success is-dismissible"><p>Rejected %s errors. They will continue to block related URLs from being served as AMP.</p></div>', $count ),
			$message
		);
	}

	/**
	 * Test filter_tag_row_actions.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_tag_row_actions()
	 */
	public function test_filter_tag_row_actions() {

		// Prevent an error in add_query_arg().
		$_SERVER['REQUEST_URI'] = 'https://example.com';
		AMP_Validation_Error_Taxonomy::register();
		$initial_actions = array(
			'delete' => '<a href="#">Delete</a>',
		);

		// When the term isn't for the invalid post type taxonomy, the actions shouldn't be altered.
		$term_other_taxonomy = $this->factory()->term->create_and_get();
		$this->assertEquals( $initial_actions, AMP_Validation_Error_Taxonomy::filter_tag_row_actions( $initial_actions, $term_other_taxonomy ) );

		// The term is for this taxonomy, so this should filter the actions.
		$term_this_taxonomy = $this->factory()->term->create_and_get( array(
			'taxonomy'    => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
			'description' => wp_json_encode( $this->get_mock_error() ),
		) );
		$filtered_actions   = AMP_Validation_Error_Taxonomy::filter_tag_row_actions( $initial_actions, $term_this_taxonomy );
		$accept_action      = $filtered_actions[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPT_ACTION ];
		$reject_action      = $filtered_actions[ AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_REJECT_ACTION ];
		$this->assertContains( strval( $term_this_taxonomy->term_id ), $accept_action );
		$this->assertContains( 'Accepting an error means it will get sanitized and not block a URL from being served as AMP.', $accept_action );
		$this->assertContains( 'Rejecting an error acknowledges that it should block a URL from being served as AMP.', $reject_action );

	}

	/**
	 * Test add_admin_menu_validation_error_item.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_admin_menu_validation_error_item()
	 */
	public function test_add_admin_menu_validation_error_item() {
		global $submenu;

		$submenu = array(); // WPCS: global override OK.
		AMP_Validation_Error_Taxonomy::register();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		AMP_Validation_Error_Taxonomy::add_admin_menu_validation_error_item();
		$expected_submenu = array(
			'Error Index',
			'manage_categories',
			'edit-tags.php?taxonomy=amp_validation_error&amp;post_type=amp_invalid_url',
			'Error Index',
		);
		$amp_options      = $submenu[ AMP_Options_Manager::OPTION_NAME ];
		$this->assertEquals( $expected_submenu, end( $amp_options ) );
	}

	/**
	 * Test get_reader_friendly_error_type_text.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::get_reader_friendly_error_type_text()
	 */
	public function test_get_reader_friendly_error_type_text() {
		$this->assertEquals( 'JavaScript', AMP_Validation_Error_Taxonomy::get_reader_friendly_error_type_text( 'js_error' ) );
		$this->assertEquals( 'HTML (Element)', AMP_Validation_Error_Taxonomy::get_reader_friendly_error_type_text( 'html_element_error' ) );
		$this->assertEquals( 'HTML (Attribute)', AMP_Validation_Error_Taxonomy::get_reader_friendly_error_type_text( 'html_attribute_error' ) );
		$this->assertEquals( 'CSS', AMP_Validation_Error_Taxonomy::get_reader_friendly_error_type_text( 'css_error' ) );
		$this->assertEquals( 'some_other_error', AMP_Validation_Error_Taxonomy::get_reader_friendly_error_type_text( 'some_other_error' ) );
	}

	/**
	 * Test filter_manage_custom_columns.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_manage_custom_columns()
	 */
	public function test_filter_manage_custom_columns() {
		AMP_Validation_Error_Taxonomy::register();
		$validation_error = $this->get_mock_error();
		$initial_content  = 'example initial content';
		$term_id          = $this->factory()->term->create( array(
			'taxonomy'    => AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG,
			'description' => wp_json_encode( $validation_error ),
		) );

		$term = get_term( $term_id, AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG );

		$url = admin_url( add_query_arg( array(
			AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG => $term->name,
			'post_type'                                  => AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG,
		), 'edit.php' ) );
		// Test the 'error' block in the switch.
		$filtered_content = AMP_Validation_Error_Taxonomy::filter_manage_custom_columns( $initial_content, 'error', $term_id );
		$this->assertEquals( $initial_content . '<p><a href="' . $url . '"><code>illegal_css_at_rule</code></a>: <code>@-ms-viewport</code></p>', $filtered_content );

		// Test the 'status' block in the switch.
		$filtered_content = AMP_Validation_Error_Taxonomy::filter_manage_custom_columns( $initial_content, 'status', $term_id );
		$this->assertEquals( $initial_content . '<span class="status-text new">New</span>', $filtered_content );

		// Test the 'created_date_gmt' block in the switch.
		$date = current_time( 'mysql', true );
		update_term_meta( $term_id, 'created_date_gmt', $date );
		$filtered_content = AMP_Validation_Error_Taxonomy::filter_manage_custom_columns( $initial_content, 'created_date_gmt', $term_id );
		$this->assertContains( '<time datetime=', $filtered_content );
		$this->assertContains( '<abbr title=', $filtered_content );

		// Test the 'details' block in the switch.
		$filtered_content = AMP_Validation_Error_Taxonomy::filter_manage_custom_columns( $initial_content, 'details', $term_id );
		$this->assertContains( $validation_error['node_attributes']['id'], $filtered_content );
		$this->assertContains( $validation_error['node_name'], $filtered_content );
	}

	/**
	 * Test handle_validation_error_update.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::handle_validation_error_update()
	 */
	public function test_handle_validation_error_update() {
		$initial_redirect_to = 'https://example.com';

		// The action argument isn't either an accepted or rejected status, so the redirect shouldn't change.
		$this->assertEquals( $initial_redirect_to, AMP_Validation_Error_Taxonomy::handle_validation_error_update( $initial_redirect_to, 'unexpected-action', array() ) );

		$action = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACCEPT_ACTION;
		$this->assertEquals(
			add_query_arg(
				array(
					'amp_actioned'       => $action,
					'amp_actioned_count' => 0,
				),
				$initial_redirect_to
			),
			AMP_Validation_Error_Taxonomy::handle_validation_error_update( $initial_redirect_to, $action, array() )
		);
		$this->assertNotFalse( has_filter( 'pre_term_description', 'wp_filter_kses' ) );
	}

	/**
	 * Gets a mock validation error for testing.
	 *
	 * @return array $error Mock validation error.
	 */
	public function get_mock_error() {
		return array(
			'at_rule'         => '-ms-viewport',
			'code'            => self::MOCK_ACCEPTABLE_ERROR,
			'node_attributes' => array(
				'href'  => 'https://example.com',
				'id'    => 'twentysixteen-style-css',
				'media' => 'all',
				'rel'   => 'stylesheet',
				'type'  => 'text/css',
			),
			'node_name'       => 'link',
			'parent_name'     => 'head',
		);
	}
}
