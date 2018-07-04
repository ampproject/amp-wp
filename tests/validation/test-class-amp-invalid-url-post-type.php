<?php
/**
 * Tests for AMP_Invalid_URL_Post_Type class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Invalid_URL_Post_Type class.
 *
 * @covers AMP_Invalid_URL_Post_Type
 */
class Test_AMP_Invalid_URL_Post_Type extends \WP_UnitTestCase {

	/**
	 * Test register.
	 *
	 * @covers \AMP_Invalid_URL_Post_Type::register()
	 */
	public function test_register() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		AMP_Invalid_URL_Post_Type::register();
		$amp_post_type = get_post_type_object( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG );

		$this->assertTrue( in_array( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, get_post_types(), true ) );
		$this->assertEquals( array(), get_all_post_type_supports( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG ) );
		$this->assertEquals( AMP_Invalid_URL_Post_Type::POST_TYPE_SLUG, $amp_post_type->name );
		$this->assertEquals( 'Validation Status', $amp_post_type->label );
		$this->assertEquals( false, $amp_post_type->public );
		$this->assertTrue( $amp_post_type->show_ui );
		$this->assertEquals( AMP_Options_Manager::OPTION_NAME, $amp_post_type->show_in_menu );
		$this->assertTrue( $amp_post_type->show_in_admin_bar );

		$this->assertEquals( 10, has_filter( 'manage_' . AMP_Validation_Manager::POST_TYPE_SLUG . '_posts_columns', self::TESTED_CLASS . '::add_post_columns' ) );
		$this->assertEquals( 10, has_action( 'manage_posts_custom_column', self::TESTED_CLASS . '::output_custom_column' ) );
		$this->assertEquals( 10, has_filter( 'post_row_actions', self::TESTED_CLASS . '::filter_row_actions' ) );
		$this->assertEquals( 10, has_filter( 'bulk_actions-edit-' . AMP_Validation_Manager::POST_TYPE_SLUG, self::TESTED_CLASS . '::add_bulk_action' ) );
		$this->assertEquals( 10, has_filter( 'handle_bulk_actions-edit-' . AMP_Validation_Manager::POST_TYPE_SLUG, self::TESTED_CLASS . '::handle_bulk_action' ) );
		$this->assertEquals( 10, has_action( 'admin_notices', self::TESTED_CLASS . '::remaining_error_notice' ) );
		$this->assertEquals( 10, has_action( 'admin_notices', self::TESTED_CLASS . '::persistent_object_caching_notice' ) );
		$this->assertEquals( 10, has_action( 'admin_menu', self::TESTED_CLASS . '::remove_publish_meta_box' ) );
		$this->assertEquals( 10, has_action( 'add_meta_boxes', self::TESTED_CLASS . '::add_meta_boxes' ) );
	}

	/**
	 * Test for store_validation_errors()
	 *
	 * @covers AMP_Validation_Manager::store_validation_errors()
	 */
	public function test_store_validation_errors() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		global $post;
		$post = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		add_theme_support( 'amp' );
		$this->process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}-->' . $this->disallowed_tag . '<!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );

		$this->assertCount( 1, AMP_Validation_Manager::$validation_results );
		$this->assertEquals( 'script', AMP_Validation_Manager::$validation_results[0]['error']['node_name'] );
		$this->assertEquals(
			array(
				'type' => 'plugin',
				'name' => 'foo',
			),
			AMP_Validation_Manager::$validation_results[0]['error']['sources'][0]
		);

		$url     = home_url( '/' );
		$post_id = AMP_Validation_Manager::store_validation_errors( wp_list_pluck( AMP_Validation_Manager::$validation_results, 'error' ), $url );
		$this->assertNotEmpty( $post_id );
		$custom_post               = get_post( $post_id );
		$validation                = AMP_Validation_Manager::summarize_validation_errors( json_decode( $custom_post->post_content, true ) );
		$expected_removed_elements = array(
			'script' => 1,
		);
		AMP_Validation_Manager::reset_validation_results();

		// This should create a new post for the errors.
		$this->assertEquals( AMP_Validation_Manager::POST_TYPE_SLUG, $custom_post->post_type );
		$this->assertEquals( $expected_removed_elements, $validation[ AMP_Validation_Manager::REMOVED_ELEMENTS ] );
		$this->assertEquals( array(), $validation[ AMP_Validation_Manager::REMOVED_ATTRIBUTES ] );
		$this->assertEquals( array( 'foo' ), $validation[ AMP_Validation_Manager::SOURCES_INVALID_OUTPUT ]['plugin'] );
		$meta = get_post_meta( $post_id, AMP_Validation_Manager::AMP_URL_META, true );
		$this->assertEquals( $url, $meta );

		AMP_Validation_Manager::reset_validation_results();
		$url = home_url( '/?baz' );
		$this->process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}-->' . $this->disallowed_tag . '<!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );
		$custom_post_id = AMP_Validation_Manager::store_validation_errors( wp_list_pluck( AMP_Validation_Manager::$validation_results, 'error' ), $url );
		AMP_Validation_Manager::reset_validation_results();
		$meta = get_post_meta( $post_id, AMP_Validation_Manager::AMP_URL_META, false );
		// A post exists for these errors, so the URL should be stored in the 'additional URLs' meta data.
		$this->assertEquals( $post_id, $custom_post_id );
		$this->assertContains( $url, $meta );

		$url = home_url( '/?foo-bar' );
		$this->process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}-->' . $this->disallowed_tag . '<!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );
		$custom_post_id = AMP_Validation_Manager::store_validation_errors( wp_list_pluck( AMP_Validation_Manager::$validation_results, 'error' ), $url );
		AMP_Validation_Manager::reset_validation_results();
		$meta = get_post_meta( $post_id, AMP_Validation_Manager::AMP_URL_META, false );

		// The URL should again be stored in the 'additional URLs' meta data.
		$this->assertEquals( $post_id, $custom_post_id );
		$this->assertContains( $url, $meta );

		AMP_Validation_Manager::reset_validation_results();
		$this->process_markup( '<!--amp-source-stack {"type":"plugin","name":"foo"}--><nonexistent></nonexistent><!--/amp-source-stack {"type":"plugin","name":"foo"}-->' );
		$custom_post_id = AMP_Validation_Manager::store_validation_errors( wp_list_pluck( AMP_Validation_Manager::$validation_results, 'error' ), $url );
		AMP_Validation_Manager::reset_validation_results();
		$error_post                = get_post( $custom_post_id );
		$validation                = AMP_Validation_Manager::summarize_validation_errors( json_decode( $error_post->post_content, true ) );
		$expected_removed_elements = array(
			'nonexistent' => 1,
		);

		// A post already exists for this URL, so it should be updated.
		$this->assertEquals( $expected_removed_elements, $validation[ AMP_Validation_Manager::REMOVED_ELEMENTS ] );
		$this->assertEquals( array( 'foo' ), $validation[ AMP_Validation_Manager::SOURCES_INVALID_OUTPUT ]['plugin'] );
		$this->assertContains( $url, get_post_meta( $custom_post_id, AMP_Validation_Manager::AMP_URL_META, false ) );

		AMP_Validation_Manager::reset_validation_results();
		$this->process_markup( $this->valid_amp_img );

		// There are no errors, so the existing error post should be deleted.
		$custom_post_id = AMP_Validation_Manager::store_validation_errors( wp_list_pluck( AMP_Validation_Manager::$validation_results, 'error' ), $url );
		AMP_Validation_Manager::reset_validation_results();

		$this->assertNull( $custom_post_id );
		remove_theme_support( 'amp' );
	}

	/**
	 * Test for store_validation_errors() when existing post is trashed.
	 *
	 * @covers AMP_Validation_Manager::store_validation_errors()
	 */
	public function test_store_validation_errors_untrashing() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		$validation_errors = $this->get_mock_errors();

		$first_post_id = AMP_Validation_Manager::store_validation_errors( $validation_errors, home_url( '/foo/' ) );
		$this->assertInternalType( 'int', $first_post_id );

		$post_name = get_post( $first_post_id )->post_name;
		wp_trash_post( $first_post_id );
		$this->assertEquals( $post_name . '__trashed', get_post( $first_post_id )->post_name );

		$next_post_id = AMP_Validation_Manager::store_validation_errors( $validation_errors, home_url( '/bar/' ) );
		$this->assertInternalType( 'int', $next_post_id );
		$this->assertEquals( $post_name, get_post( $next_post_id )->post_name );
		$this->assertEquals( $next_post_id, $first_post_id );

		$this->assertEqualSets(
			array(
				home_url( '/foo/' ),
				home_url( '/bar/' ),
			),
			get_post_meta( $next_post_id, AMP_Validation_Manager::AMP_URL_META, false )
		);
	}

	/**
	 * Test for get_validation_status_post().
	 *
	 * @covers AMP_Validation_Manager::get_invalid_url_post()
	 */
	public function test_get_validation_status_post() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		global $post;
		$post           = $this->factory()->post->create_and_get(); // WPCS: global override ok.
		$custom_post_id = $this->factory()->post->create( array(
			'post_type' => AMP_Validation_Manager::POST_TYPE_SLUG,
		) );

		$url = get_permalink( $custom_post_id );
		$this->assertEquals( null, AMP_Validation_Manager::get_invalid_url_post( $url ) );

		update_post_meta( $custom_post_id, AMP_Validation_Manager::AMP_URL_META, $url );
		$this->assertEquals( $custom_post_id, AMP_Validation_Manager::get_invalid_url_post( $url )->ID );
	}

	/**
	 * Test for add_post_columns()
	 *
	 * @covers AMP_Validation_Manager::add_post_columns()
	 */
	public function test_add_post_columns() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		$initial_columns = array(
			'cb' => '<input type="checkbox">',
		);
		$this->assertEquals(
			array_merge(
				$initial_columns,
				array(
					'url_count' => 'Count',
					AMP_Validation_Manager::REMOVED_ELEMENTS => 'Removed Elements',
					AMP_Validation_Manager::REMOVED_ATTRIBUTES => 'Removed Attributes',
					AMP_Validation_Manager::SOURCES_INVALID_OUTPUT => 'Incompatible Sources',
				)
			),
			AMP_Validation_Manager::add_post_columns( $initial_columns )
		);
	}

	/**
	 * Gets the test data for test_output_custom_column().
	 *
	 * @return array $columns
	 */
	public function get_custom_columns() {
		return array(
			'url_count'             => array(
				'url_count',
				'1',
			),
			'invalid_element'       => array(
				AMP_Validation_Error_Taxonomy::REMOVED_ELEMENTS,
				$this->disallowed_tag_name,
			),
			'removed_attributes'    => array(
				AMP_Validation_Error_Taxonomy::REMOVED_ATTRIBUTES,
				$this->disallowed_attribute_name,
			),
			'sources_invalid_input' => array(
				AMP_Validation_Error_Taxonomy::SOURCES_INVALID_OUTPUT,
				$this->plugin_name,
			),
		);
	}

	/**
	 * Test for output_custom_column()
	 *
	 * @dataProvider get_custom_columns
	 * @covers       AMP_Validation_Manager::output_custom_column()
	 *
	 * @param string $column_name The name of the column.
	 * @param string $expected_value The value that is expected to be present in the column markup.
	 */
	public function test_output_custom_column( $column_name, $expected_value ) {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		ob_start();
		AMP_Validation_Manager::output_custom_column( $column_name, $this->create_custom_post() );
		$this->assertContains( $expected_value, ob_get_clean() );
	}

	/**
	 * Test for add_bulk_action()
	 *
	 * @covers AMP_Validation_Manager::add_bulk_action()
	 */
	public function test_add_bulk_action() {
		$this->markTestSkipped( 'Needs refactoring' );

		$initial_action = array(
			'edit' => 'Edit',
		);
		$actions        = AMP_Validation_Manager::add_bulk_action( $initial_action );
		$this->assertFalse( isset( $action['edit'] ) );
		$this->assertEquals( 'Recheck', $actions[ AMP_Validation_Manager::RECHECK_ACTION ] );
	}

	/**
	 * Test for filter_row_actions()
	 *
	 * @covers AMP_Validation_Manager::filter_row_actions()
	 */
	public function test_filter_row_actions() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		$this->set_capability();

		$initial_actions = array(
			'trash' => '<a href="https://example.com">Trash</a>',
		);
		$post            = $this->factory()->post->create_and_get();
		$this->assertEquals( $initial_actions, AMP_Validation_Manager::filter_row_actions( $initial_actions, $post ) );

		$custom_post_id = $this->create_custom_post();
		$actions        = AMP_Validation_Manager::filter_row_actions( $initial_actions, get_post( $custom_post_id ) );
		$url            = get_post_meta( $custom_post_id, AMP_Validation_Manager::AMP_URL_META, true );
		$this->assertContains( $url, $actions[ AMP_Validation_Manager::RECHECK_ACTION ] );
		$this->assertEquals( $initial_actions['trash'], $actions['trash'] );
	}


	/**
	 * Test for handle_bulk_action()
	 *
	 * @covers AMP_Validation_Manager::handle_bulk_action()
	 */
	public function test_handle_bulk_action() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		$initial_redirect                            = admin_url( 'plugins.php' );
		$items                                       = array( $this->create_custom_post() );
		$urls_tested                                 = '1';
		$_GET[ AMP_Validation_Manager::URLS_TESTED ] = $urls_tested;

		// The action isn't correct, so the callback should return the URL unchanged.
		$this->assertEquals( $initial_redirect, AMP_Validation_Manager::handle_bulk_action( $initial_redirect, 'trash', $items ) );

		$that   = $this;
		$filter = function() use ( $that ) {
			return array(
				'body' => sprintf(
					'<html amp><head></head><body></body><!--%s--></html>',
					'AMP_VALIDATION_ERRORS:' . wp_json_encode( $that->get_mock_errors() )
				),
			);
		};
		add_filter( 'pre_http_request', $filter, 10, 3 );
		$this->assertEquals(
			add_query_arg(
				array(
					AMP_Validation_Manager::URLS_TESTED => $urls_tested,
					AMP_Validation_Manager::REMAINING_ERRORS => count( $items ),
				),
				$initial_redirect
			),
			AMP_Validation_Manager::handle_bulk_action( $initial_redirect, AMP_Validation_Manager::RECHECK_ACTION, $items )
		);
		remove_filter( 'pre_http_request', $filter, 10, 3 );
	}


	/**
	 * Test for print_admin_notice()
	 *
	 * @covers AMP_Validation_Manager::print_admin_notice()
	 */
	public function test_remaining_error_notice() {
		$this->markTestSkipped( 'Needs refactoring' );

		ob_start();
		AMP_Validation_Manager::remaining_error_notice();
		$this->assertEmpty( ob_get_clean() );

		$_GET['post_type'] = 'post';
		ob_start();
		AMP_Validation_Manager::remaining_error_notice();
		$this->assertEmpty( ob_get_clean() );

		set_current_screen( 'edit.php' );
		get_current_screen()->post_type = AMP_Validation_Manager::POST_TYPE_SLUG;

		$_GET[ AMP_Validation_Manager::REMAINING_ERRORS ] = '1';
		$_GET[ AMP_Validation_Manager::URLS_TESTED ]      = '1';
		ob_start();
		AMP_Validation_Manager::remaining_error_notice();
		$this->assertContains( 'The rechecked URL still has validation errors', ob_get_clean() );

		$_GET[ AMP_Validation_Manager::URLS_TESTED ] = '2';
		ob_start();
		AMP_Validation_Manager::remaining_error_notice();
		$this->assertContains( 'The rechecked URLs still have validation errors', ob_get_clean() );

		$_GET[ AMP_Validation_Manager::REMAINING_ERRORS ] = '0';
		ob_start();
		AMP_Validation_Manager::remaining_error_notice();
		$this->assertContains( 'The rechecked URLs have no validation error', ob_get_clean() );

		$_GET[ AMP_Validation_Manager::URLS_TESTED ] = '1';
		ob_start();
		AMP_Validation_Manager::remaining_error_notice();
		$this->assertContains( 'The rechecked URL has no validation error', ob_get_clean() );

		unset( $GLOBALS['current_screen'] );
	}

	/**
	 * Test for handle_validate_request()
	 *
	 * @covers AMP_Validation_Manager::handle_validate_request()
	 */
	public function test_handle_validate_request() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		$post_id              = $this->create_custom_post();
		$_REQUEST['_wpnonce'] = wp_create_nonce( AMP_Validation_Manager::NONCE_ACTION . $post_id );
		wp_set_current_user( $this->factory()->user->create( array(
			'role' => 'administrator',
		) ) );

		try {
			AMP_Validation_Manager::handle_inline_recheck( $post_id );
		} catch ( WPDieException $e ) {
			$exception = $e;
		}

		// This calls wp_redirect(), which throws an exception.
		$this->assertTrue( isset( $exception ) );
	}


	/**
	 * Test for remove_publish_meta_box()
	 *
	 * @covers AMP_Validation_Manager::remove_publish_meta_box()
	 */
	public function test_remove_publish_meta_box() {
		$this->markTestSkipped( 'Needs refactoring' );

		global $wp_meta_boxes;
		AMP_Validation_Manager::remove_publish_meta_box();
		$contexts = $wp_meta_boxes[ AMP_Validation_Manager::POST_TYPE_SLUG ]['side'];
		foreach ( $contexts as $context ) {
			$this->assertFalse( $context['submitdiv'] );
		}
	}

	/**
	 * Test for add_meta_boxes()
	 *
	 * @covers AMP_Validation_Manager::add_meta_boxes()
	 */
	public function test_add_meta_boxes() {
		$this->markTestSkipped( 'Needs refactoring' );

		global $wp_meta_boxes;
		AMP_Validation_Manager::add_meta_boxes();
		$side_meta_box = $wp_meta_boxes[ AMP_Validation_Manager::POST_TYPE_SLUG ]['side']['default'][ AMP_Validation_Manager::STATUS_META_BOX ];
		$this->assertEquals( AMP_Validation_Manager::STATUS_META_BOX, $side_meta_box['id'] );
		$this->assertEquals( 'Status', $side_meta_box['title'] );
		$this->assertEquals(
			array(
				self::TESTED_CLASS,
				'print_status_meta_box',
			),
			$side_meta_box['callback']
		);

		$full_meta_box = $wp_meta_boxes[ AMP_Validation_Manager::POST_TYPE_SLUG ]['normal']['default'][ AMP_Validation_Manager::VALIDATION_ERRORS_META_BOX ];
		$this->assertEquals( AMP_Validation_Manager::VALIDATION_ERRORS_META_BOX, $full_meta_box['id'] );
		$this->assertEquals( 'Validation Errors', $full_meta_box['title'] );
		$this->assertEquals(
			array(
				self::TESTED_CLASS,
				'print_validation_errors_meta_box',
			),
			$full_meta_box['callback']
		);
	}

	/**
	 * Test for print_status_meta_box()
	 *
	 * @covers AMP_Validation_Manager::print_status_meta_box()
	 */
	public function test_print_status_meta_box() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		$this->set_capability();
		$post_storing_error = get_post( $this->create_custom_post() );
		$url                = get_post_meta( $post_storing_error->ID, AMP_Validation_Manager::AMP_URL_META, true );
		$post_with_error    = AMP_Validation_Manager::get_invalid_url_post( $url );
		ob_start();
		AMP_Validation_Manager::print_status_meta_box( $post_storing_error );
		$output = ob_get_clean();

		$this->assertContains( date_i18n( 'M j, Y @ H:i', strtotime( $post_with_error->post_date ) ), $output );
		$this->assertContains( 'Published on:', $output );
		$this->assertContains( 'Move to Trash', $output );
		$this->assertContains( esc_url( get_delete_post_link( $post_storing_error->ID ) ), $output );
		$this->assertContains( 'misc-pub-section', $output );
		$this->assertContains(
			AMP_Validation_Manager::get_recheck_link(
				$post_with_error,
				add_query_arg(
					'post',
					$post_with_error->ID,
					admin_url( 'post.php' )
				)
			),
			$output
		);
	}

	/**
	 * Test for print_status_meta_box()
	 *
	 * @covers AMP_Validation_Manager::print_status_meta_box()
	 */
	public function test_print_validation_errors_meta_box() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );
		$this->set_capability();
		$post_storing_error     = get_post( $this->create_custom_post() );
		$first_url              = get_post_meta( $post_storing_error->ID, AMP_Validation_Manager::AMP_URL_META, true );
		$second_url_same_errors = get_permalink( $this->factory()->post->create() );
		AMP_Validation_Manager::store_validation_errors( $this->get_mock_errors(), $second_url_same_errors );
		ob_start();
		AMP_Validation_Manager::print_validation_errors_meta_box( $post_storing_error );
		$output = ob_get_clean();

		$this->assertContains( '<details', $output );
		$this->assertContains( $this->disallowed_tag_name, $output );
		$this->assertContains( $this->disallowed_attribute_name, $output );
		$this->assertContains( 'URLs', $output );
		$this->assertContains( $first_url, $output );
		$this->assertContains( $second_url_same_errors, $output );
		AMP_Validation_Manager::reset_validation_results();
	}

	/**
	 * Test for get_recheck_link()
	 *
	 * @covers AMP_Validation_Manager::get_recheck_link()
	 */
	public function test_get_recheck_link() {
		$this->markTestSkipped( 'Needs rewrite for refactor' );

		$this->set_capability();
		$post_id = $this->create_custom_post();
		$url     = get_edit_post_link( $post_id, 'raw' );
		$link    = AMP_Validation_Manager::get_recheck_link( get_post( $post_id ), $url );
		$this->assertContains( AMP_Validation_Manager::RECHECK_ACTION, $link );
		$this->assertContains( wp_create_nonce( AMP_Validation_Manager::NONCE_ACTION . $post_id ), $link );
		$this->assertContains( 'Recheck the URL for AMP validity', $link );
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
				'code'            => AMP_Validation_Manager::INVALID_ELEMENT_CODE,
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
			array(
				'code'               => AMP_Validation_Manager::INVALID_ATTRIBUTE_CODE,
				'node_name'          => $this->disallowed_attribute_name,
				'parent_name'        => 'div',
				'element_attributes' => array(
					$this->disallowed_attribute_name => '',
				),
				'sources'            => array(
					array(
						'type' => 'plugin',
						'name' => $this->plugin_name,
					),
				),
			),
		);
	}
}
