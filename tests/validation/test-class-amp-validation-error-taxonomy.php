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
		$this->markTestIncomplete();
	}

	/**
	 * Test is_validation_error_sanitized.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::is_validation_error_sanitized()
	 */
	public function test_is_validation_error_sanitized() {
		$this->markTestIncomplete();
	}

	/**
	 * Test get_validation_error_sanitization.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::get_validation_error_sanitization()
	 */
	public function test_get_validation_error_sanitization() {
		$this->markTestIncomplete();
		$this->markTestIncomplete( 'Test amp_validation_error_sanitized filter' );
	}

	/**
	 * Test accept_validation_errors.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::accept_validation_errors()
	 */
	public function test_accept_validation_errors() {
		$this->markTestIncomplete();
	}

	/**
	 * Test is_array_subset.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::is_array_subset()
	 */
	public function test_is_array_subset() {
		$this->markTestIncomplete();
	}

	/**
	 * Test get_validation_error_count.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::get_validation_error_count()
	 */
	public function test_get_validation_error_count() {
		$this->markTestIncomplete();
	}

	/**
	 * Test filter_posts_where_for_validation_error_status.
	 *
	 * @covers \AMP_Validation_Error_Taxonomy::filter_posts_where_for_validation_error_status()
	 */
	public function test_filter_posts_where_for_validation_error_status() {
		$this->markTestIncomplete();
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
}
