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
		parent::tearDown();
	}

	/**
	 * Test register.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::register()
	 */
	public function test_register() {
		global $wp_taxonomies;

		AMP_Validation_Error_Taxonomy::register();
		$taxonomy_object = $wp_taxonomies[ AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG ];

		$this->assertFalse( $taxonomy_object->public );
		$this->assertTrue( $taxonomy_object->show_ui );
		$this->assertFalse( $taxonomy_object->show_tagcloud );
		$this->assertFalse( $taxonomy_object->show_in_quick_edit );
		$this->assertFalse( $taxonomy_object->hierarchical );
		$this->assertTrue( $taxonomy_object->show_in_menu );
		$this->assertFalse( $taxonomy_object->meta_box_cb );
		$this->assertEquals( 'AMP Validation Errors', $taxonomy_object->label );
		$this->assertEquals( 'do_not_allow', $taxonomy_object->cap->assign_terms );
		$this->assertEquals( array( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ), $taxonomy_object->object_type );

		$labels = $taxonomy_object->labels;
		$this->assertEquals( 'AMP Validation Errors', $labels->name );
		$this->assertEquals( 'AMP Validation Error', $labels->singular_name );
		$this->assertEquals( 'Search AMP Validation Errors', $labels->search_items );
		$this->assertEquals( 'All AMP Validation Errors', $labels->all_items );
		$this->assertEquals( 'Edit AMP Validation Error', $labels->edit_item );
		$this->assertEquals( 'Update AMP Validation Error', $labels->update_item );
		$this->assertEquals( 'Validation Errors', $labels->menu_name );
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
		$wp_query->set( AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_STATUS_QUERY_VAR, 1 );
		$filtered_where = AMP_Validation_Error_Taxonomy::filter_posts_where_for_validation_error_status( $initial_where, $wp_query );
		$this->assertContains( 'SELECT 1', $filtered_where );
		$this->assertContains( 'INNER JOIN', $filtered_where );
		$this->assertContains( $wpdb->term_relationships, $filtered_where );
		$this->assertContains( $wpdb->term_taxonomy, $filtered_where );
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
		$this->markTestIncomplete();
	}

	/**
	 * Test add_group_terms_clauses_filter.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_group_terms_clauses_filter()
	 */
	public function test_add_group_terms_clauses_filter() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_user_has_cap_for_hiding_term_list_table_checkbox.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_user_has_cap_for_hiding_term_list_table_checkbox()
	 */
	public function test_filter_user_has_cap_for_hiding_term_list_table_checkbox() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_terms_clauses_for_description_search.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_terms_clauses_for_description_search()
	 */
	public function test_filter_terms_clauses_for_description_search() {
		$this->markTestIncomplete();
	}

	/**
	 * Test add_admin_notices.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_admin_notices()
	 */
	public function test_add_admin_notices() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_tag_row_actions.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_tag_row_actions()
	 */
	public function test_filter_tag_row_actions() {
		$this->markTestIncomplete();
	}

	/**
	 * Test add_admin_menu_validation_error_item.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::add_admin_menu_validation_error_item()
	 */
	public function test_add_admin_menu_validation_error_item() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_views_edit.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_views_edit()
	 */
	public function test_filter_views_edit() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_manage_custom_columns.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_manage_custom_columns()
	 */
	public function test_filter_manage_custom_columns() {
		$this->markTestIncomplete();
	}

	/**
	 * Test handle_validation_error_update.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::handle_validation_error_update()
	 */
	public function test_handle_validation_error_update() {
		$this->markTestIncomplete();
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
