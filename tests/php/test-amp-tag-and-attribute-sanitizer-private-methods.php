<?php
// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_dump
// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

class AMP_Tag_And_Attribute_Sanitizer_Attr_Spec_Rules_Test extends WP_UnitTestCase {

	protected $allowed_tags;
	protected $globally_allowed_attrs;
	protected $layout_allowed_attrs;

	public function setUp() {
		$this->allowed_tags           = AMP_Allowed_Tags_Generated::get_allowed_tags();
		$this->globally_allowed_attrs = AMP_Allowed_Tags_Generated::get_allowed_attributes();
		$this->layout_allowed_attrs   = AMP_Allowed_Tags_Generated::get_layout_attributes();
	}

	public function get_attr_spec_rule_data() {
		return [
			'test_attr_spec_rule_mandatory_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'src',
					'attribute_value' => '/path/to/resource',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_mandatory',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_mandatory_alternate_attr_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'src',
					'use_alternate_name' => 'srcset',
					'attribute_value' => '/path/to/resource',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_mandatory',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_mandatory_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'src',
					'attribute_value' => '/path/to/resource',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_mandatory',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_mandatory_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'alt',
					'attribute_value' => 'alternate',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_mandatory',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],

			'test_attr_spec_rule_value_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'amp-mustache',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_value_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_value_not_applicable' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_value',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],

			'test_attr_spec_rule_value_casei_lower_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'type',
					'attribute_value' => 'text/html',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value_casei',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_value_casei_upper_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'type',
					'attribute_value' => 'TEXT/HTML',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value_casei',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_value_casei_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value_casei',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_value_casei_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_value_casei',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],

			'test_attr_spec_rule_value_regex_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'target',
					'attribute_value' => '_blank',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_value_regex_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'target',
					'attribute_value' => '_blankzzz',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_value_regex_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'target',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_value_regex',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],

			'test_attr_spec_rule_value_regex_casei_lower_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'false',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value_casei',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_value_regex_casei_upper_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'FALSE',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value_casei',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_value_regex_casei_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'invalid',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_value_casei',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_value_regex_casei_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_value_regex_casei',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'test_attr_spec_rule_disallowed_relative_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-share-endpoint',
					'attribute_value' => 'http://example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_disallowed_relative',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_disallowed_relative_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-share-endpoint',
					'attribute_value' => '//example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_disallowed_relative',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_disallowed_relative_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-share-endpoint',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_disallowed_relative',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],

			'test_attr_spec_rule_disallowed_empty_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-user-notification',
					'attribute_name' => 'data-dismiss-href',
					'attribute_value' => 'https://example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_disallowed_empty',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_disallowed_empty_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-user-notification',
					'attribute_name' => 'data-dismiss-href',
					'attribute_value' => '',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_disallowed_empty',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_disallowed_empty_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-user-notification',
					'attribute_name' => 'data-dismiss-href',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_disallowed_empty',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'test_attr_spec_rule_blacklisted_value_regex_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_blacklisted_value_regex',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_blacklisted_value_regex_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'components',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_blacklisted_value_regex',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_blacklisted_value_regex_fail_2' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'import',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_blacklisted_value_regex',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_blacklisted_value_regex_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_blacklisted_value_regex',
				],
				'expected' => AMP_Rule_Spec::NOT_APPLICABLE,
			],
		];
	}

	/**
	 * @dataProvider get_attr_spec_rule_data
	 * @group allowed-tags-private-methods
	 */
	public function test_validate_attr_spec_rules( $data, $expected ) {

		if ( isset( $data['include_attr_value'] ) && $data['include_attr_value'] ) {
			$attr_value = '="' . $data['attribute_value'] . '"';
		} else {
			$attr_value = '';
		}
		if ( isset( $data['use_alternate_name'] ) && $data['use_alternate_name'] && $data['include_attr'] ) {
			$attribute = $data['use_alternate_name'] . $attr_value;
		} elseif ( isset( $data['include_attr'] ) && $data['include_attr'] ) {
			$attribute = $data['attribute_name'] . $attr_value;
		} else {
			$attribute = '';
		}

		$source = '<' . $data['tag_name'] . ' ' . $attribute . '>Some test content</' . $data['tag_name'] . '>';

		$attr_spec_list = $this->allowed_tags[ $data['tag_name'] ][ $data['rule_spec_index'] ]['attr_spec_list'];
		foreach ( $attr_spec_list as $attr_name => $attr_val ) {
			if ( isset( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		$attr_spec_rule = $attr_spec_list[ $data['attribute_name'] ];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$node      = $dom->getElementsByTagName( $data['tag_name'] )->item( 0 );

		$got = $this->invoke_method( $sanitizer, $data['func_name'], [ $node, $data['attribute_name'], $attr_spec_rule ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $source, wp_json_encode( $data ) ) );
	}

	public function get_is_allowed_attribute_data() {
		return [
			'test_is_amp_allowed_attribute_whitelisted_regex_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-whatever-else-you-want',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
			'test_is_amp_allowed_attribute_global_attribute_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'itemid',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
			'test_is_amp_allowed_attribute_tag_spec_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'media',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
			'test_is_amp_allowed_attribute_disallowed_attr_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'bad-attr',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => false,
			],

			'test_is_amp_allowed_attribute_layout_height_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'height',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
			'test_is_amp_allowed_attribute_layout_heights_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'heights',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
			'test_is_amp_allowed_attribute_layout_width_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'width',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
			'test_is_amp_allowed_attribute_layout_sizes_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'sizes',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
			'test_is_amp_allowed_attribute_layout_layout_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'layout',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				],
				'expected' => true,
			],
		];
	}

	/**
	 * @dataProvider get_is_allowed_attribute_data
	 * @group allowed-tags-private-methods
	 */
	public function test_is_allowed_attribute( $data, $expected ) {

		if ( isset( $data['include_attr_value'] ) && $data['include_attr_value'] ) {
			$attr_value = '="' . $data['attribute_value'] . '"';
		} else {
			$attr_value = '';
		}
		if ( isset( $data['use_alternate_name'] ) && $data['use_alternate_name'] && $data['include_attr'] ) {
			$attribute = $data['use_alternate_name'] . $attr_value;
		} elseif ( isset( $data['include_attr'] ) && $data['include_attr'] ) {
			$attribute = $data['attribute_name'] . $attr_value;
		} else {
			$attribute = '';
		}
		$source = '<' . $data['tag_name'] . ' ' . $attribute . '>Some test content</' . $data['tag_name'] . '>';

		$attr_spec_list = array_merge( $this->globally_allowed_attrs, $this->allowed_tags[ $data['tag_name'] ][ $data['rule_spec_index'] ]['attr_spec_list'] );
		if ( isset( $this->allowed_tags[ $data['tag_name'] ][ $data['rule_spec_index'] ]['tag_spec']['amp_layout'] ) ) {
			$attr_spec_list = array_merge( $attr_spec_list, $this->layout_allowed_attrs );
		}
		foreach ( $attr_spec_list as $attr_name => $attr_val ) {
			if ( isset( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$node      = $dom->getElementsByTagName( $data['tag_name'] )->item( 0 );
		$attr      = $node->getAttributeNode( $data['attribute_name'] );

		$got = $this->invoke_method( $sanitizer, $data['func_name'], [ $attr, $attr_spec_list ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $source, wp_json_encode( $data ) ) );
	}

	public function get_remove_node_data() {
		return [
			'remove_single_bad_tag' => [
				[
					'source' => '<bad-tag></bad-tag>',
					'tag_name' => 'bad-tag',
				],
				'',
			],
			'remove_bad_tag_with_single_empty_parent' => [
				[
					'source' => '<div><bad-tag></bad-tag></div>',
					'tag_name' => 'bad-tag',
				],
				'',
			],
			'remove_bad_tag_with_multiple_empty_parents' => [
				[
					'source' => '<div><p><bad-tag></bad-tag></p></div>',
					'tag_name' => 'bad-tag',
				],
				'',
			],
			'remove_bad_tag_leave_siblings' => [
				[
					'source' => '<bad-tag></bad-tag><p>Good Data</p>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p>',
			],
			'remove_bad_tag_and_empty_parent_leave_parent_siblings' => [
				[
					'source' => '<div><bad-tag></bad-tag></div><p>Good Data</p>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p>',
			],
			'remove_bad_tag_and_multiple_empty_parent_leave_parent_siblings' => [
				[
					'source' => '<div><div><bad-tag></bad-tag></div></div><p>Good Data</p>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p>',
			],
			'remove_bad_tag_leave_empty_siblings_and_parent' => [
				[
					'source'   => '<div><br><bad-tag></bad-tag></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><br></div>',
			],
			'remove_single_bad_tag_with_non-empty_parent' => [
				[
					'source' => '<div><bad-tag></bad-tag><p>Good Data</p></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><p>Good Data</p></div>',
			],
			'remove_bad_tag_and_empty_parent_leave_non-empty_grandparent' => [
				[
					'source' => '<div><div><bad-tag></bad-tag></div><p>Good Data</p></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><p>Good Data</p></div>',
			],
			'remove_bad_tag_and_empty_grandparent_leave_non-empty_greatgrandparent' => [
				[
					'source' => '<div><div><div><bad-tag></bad-tag></div></div><p>Good Data</p></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><p>Good Data</p></div>',
			],
		];
	}

	/**
	 * @dataProvider get_remove_node_data
	 * @group allowed-tags-private-methods
	 */
	public function test_remove_node( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$node      = $dom->getElementsByTagName( $data['tag_name'] )->item( 0 );

		$this->invoke_method( $sanitizer, 'remove_node', [ $node ] );

		$got = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}


	public function get_replace_node_with_children_data() {
		return [
			'text_child' => [
				[
					'source' => '<bad-tag>Good Data</bad-tag>',
					'tag_name' => 'bad-tag',
				],
				'Good Data',
			],
			'comment_child' => [
				[
					'source' => '<bad-tag><!-- Good Data --></bad-tag>',
					'tag_name' => 'bad-tag',
				],
				'<!-- Good Data -->',
			],
			'single_child' => [
				[
					'source' => '<bad-tag><p>Good Data</p></bad-tag>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p>',
			],
			'multiple_children' => [
				[
					'source' => '<bad-tag><p>Good Data</p><p>More Good Data</p></bad-tag>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p><p>More Good Data</p>',
			],
			'no_children' => [
				[
					'source' => '<bad-tag></bad-tag>',
					'tag_name' => 'bad-tag',
				],
				'',
			],
			'children_with_empty_parent' => [
				[
					'source' => '<div><bad-tag>Good Data</bad-tag></div>',
					'tag_name' => 'bad-tag',
				],
				'<div>Good Data</div>',
			],
			'no_children_empty_parent' => [
				[
					'source' => '<div><bad-tag></bad-tag></div>',
					'tag_name' => 'bad-tag',
				],
				'',
			],
			'children_multiple_empty_parents' => [
				[
					'source' => '<div><p><bad-tag>Good Data</bad-tag></p></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><p>Good Data</p></div>',
			],
			'no_children_multiple_empty_parents' => [
				[
					'source' => '<div><p><bad-tag></bad-tag></p></div>',
					'tag_name' => 'bad-tag',
				],
				'',
			],
			'no_children_leave_siblings' => [
				[
					'source' => '<bad-tag></bad-tag><p>Good Data</p>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p>',
			],
			'no_children_and_empty_parent_leave_parent_siblings' => [
				[
					'source' => '<div><bad-tag></bad-tag></div><p>Good Data</p>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p>',
			],
			'no_children_and_multiple_empty_parent_leave_parent_siblings' => [
				[
					'source' => '<div><div><bad-tag></bad-tag></div></div><p>Good Data</p>',
					'tag_name' => 'bad-tag',
				],
				'<p>Good Data</p>',
			],
			'no_children_leave_empty_siblings_and_parent' => [
				[
					'source'   => '<div><br><bad-tag></bad-tag></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><br></div>',
			],
			'no_childreng_with_non-empty_parent' => [
				[
					'source' => '<div><bad-tag></bad-tag><p>Good Data</p></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><p>Good Data</p></div>',
			],
			'no_children_and_empty_parent_leave_non-empty_grandparent' => [
				[
					'source' => '<div><div><bad-tag></bad-tag></div><p>Good Data</p></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><p>Good Data</p></div>',
			],
			'no_children_and_empty_grandparent_leave_non-empty_greatgrandparent' => [
				[
					'source'   => '<div><div><div><bad-tag></bad-tag></div></div><p>Good Data</p></div>',
					'tag_name' => 'bad-tag',
				],
				'<div><p>Good Data</p></div>',
			],
		];
	}

	/**
	 * @dataProvider get_replace_node_with_children_data
	 * @group allowed-tags-private-methods
	 */
	public function test_replace_node_with_children( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$node      = $dom->getElementsByTagName( $data['tag_name'] )->item( 0 );

		$this->invoke_method( $sanitizer, 'replace_node_with_children', [ $node ] );

		$got = AMP_DOM_Utils::get_content_from_dom( $dom );
		$got = preg_replace( '/(?<=>)\s+(?=<)/', '', $got );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	/**
	 * Get data for testing get_ancestor_with_matching_spec_name.
	 *
	 * @return array Data.
	 */
	public function get_ancestor_with_matching_spec_name_data() {
		return [
			'empty' => [
				[
					'source' => '',
					'node_tag_name' => 'p',
					'ancestor_tag_name' => 'article',
				],
				null,
			],
			'ancestor_is_immediate_parent' => [
				[
					'source' => '<article><p>Good Data</p><article>',
					'node_tag_name' => 'p',
					'ancestor_tag_name' => 'article',
				],
				'article',
			],
			'ancestor_is_distant_parent' => [
				[
					'source' => '<article><div><div><div><p>Good Data</p></div></div></div><article>',
					'node_tag_name' => 'p',
					'ancestor_tag_name' => 'article',
				],
				'article',
			],
			'ancestor_has_attributes' => [
				[
					'source' => '<form method="post"><div><div><div><p>Good Data</p></div></div></div><article>',
					'node_tag_name' => 'p',
					'ancestor_tag_name' => 'form [method=post]',
				],
				'form',
			],
			'ancestor_does_not_exist' => [
				[
					'source' => '<div><div><div><p>Good Data</p></div></div></div>',
					'node_tag_name' => 'p',
					'ancestor_tag_name' => 'article',
				],
				null,
			],
		];
	}

	/**
	 * Test get_ancestor_with_matching_spec_name.
	 *
	 * @dataProvider get_ancestor_with_matching_spec_name_data
	 * @group allowed-tags-private-methods
	 * @covers AMP_Tag_And_Attribute_Sanitizer::get_ancestor_with_matching_spec_name()
	 *
	 * @param array  $data     Data.
	 * @param string $expected Expected.
	 */
	public function test_get_ancestor_with_matching_spec_name( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		if ( $expected ) {
			$ancestor_node = $dom->getElementsByTagName( $expected )->item( 0 );
		} else {
			$ancestor_node = null;
		}

		$got = $this->invoke_method( $sanitizer, 'get_ancestor_with_matching_spec_name', [ $node, $data['ancestor_tag_name'] ] );

		$this->assertEquals( $ancestor_node, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_validate_attr_spec_list_for_node_data() {
		return [
			'no_attributes' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [],
				],
				0.5, // Because there are no mandatory attributes.
			],
			'attributes_no_spec' => [
				[
					'source' => '<div attribute1 attribute2 attribute3></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [],
				],
				0.5, // Because there are no mandatory attributes.
			],
			'attributes_alternative_names' => [
				[
					'source' => '<div attribute1 attribute2 attribute3></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'alternative_names' => [
								'attribute1_alternative1',
								'attribute1_alternative2',
								'attribute1_alternative3',
							],
						],
					],
				],
				0.5, // Because there are no mandatory attributes.
			],
			'attributes_mandatory' => [
				[
					'source' => '<div attribute1 attribute2 attribute3></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'mandatory' => true,
						],
					],
				],
				1,
			],
			'attributes_mandatory_alternative_name' => [
				[
					'source' => '<div attribute1_alternative1 attribute2 attribute3></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'mandatory' => true,
							'alternative_names' => [
								'attribute1_alternative1',
								'attribute1_alternative2',
								'attribute1_alternative3',
							],
						],
					],
				],
				1,
			],
			'attributes_value' => [
				[
					'source' => '<div attribute1="required_value"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value' => 'required_value',
						],
					],
				],
				1,
			],
			'attributes_value_regex' => [
				[
					'source' => '<div attribute1="this"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value_regex' => '(this|that)',
						],
					],
				],
				1,
			],
			'attributes_value_casei' => [
				[
					'source' => '<div attribute1="VALUE"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value_casei' => 'value',
						],
					],
				],
				1,
			],
			'attributes_value_regex_casei' => [
				[
					'source' => '<div attribute1="THIS"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value_regex_casei' => '(this|that)',
						],
					],
				],
				1,
			],
			'attributes_allow_relative_false_pass' => [
				[
					'source' => '<div attribute1="http://example.com/relative/path/to/resource"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value_url' => [
								'allow_relative' => false,
							],
						],
					],
				],
				2,
			],
			'attributes_allow_relative_false_fail' => [
				[
					'source' => '<div attribute1="/relative/path/to/resource"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value_url' => [
								'allow_relative' => false,
							],
						],
					],
				],
				0,
			],
			'attributes_allow_empty_false_pass' => [
				[
					'source' => '<div attribute1="http://example.com/relative/path/to/resource"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value_url' => [
								'allow_empty' => false,
							],
						],
					],
				],
				2,
			],
			'attributes_allow_empty_false_fail' => [
				[
					'source' => '<div attribute1></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'value_url' => [
								'allow_empty' => false,
							],
						],
					],
				],
				0,
			],
			'attributes_blacklisted_regex' => [
				[
					'source' => '<div attribute1="blacklisted_value"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'blacklisted_value_regex' => 'blacklisted_value',
							'alternative_names' => [
								'attribute1_alternative1',
								'attribute1_alternative2',
								'attribute1_alternative3',
							],
						],
					],
				],
				0,
			],
		];
	}

	/**
	 * @dataProvider get_validate_attr_spec_list_for_node_data
	 * @group allowed-tags-private-methods
	 */
	public function test_validate_attr_spec_list_for_node( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );

		$got = $this->invoke_method( $sanitizer, 'validate_attr_spec_list_for_node', [ $node, $data['attr_spec_list'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_check_attr_spec_rule_value_data() {
		return [
			'no_attributes' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'value_pass' => [
				[
					'source' => '<div attribute1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => 'value1',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_fail' => [
				[
					'source' => '<div attribute1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => 'valuex',
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'value_no_attr' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => 'value1',
					],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'value_empty_pass1' => [
				[
					'source' => '<div attribute1=""></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => '',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_empty_pass2' => [
				[
					'source' => '<div attribute1></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => '',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_empty_fail' => [
				[
					'source' => '<div attribute1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => '',
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'value_alternative_attr_name_pass' => [
				[
					'source' => '<div attribute1_alternative1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => 'value1',
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_alternative_attr_name_fail' => [
				[
					'source' => '<div attribute1_alternative1="value2"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value' => 'value1',
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
		];
	}

	/**
	 * @dataProvider get_check_attr_spec_rule_value_data
	 * @group allowed-tags-private-methods
	 */
	public function test_check_attr_spec_rule_value( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$got = $this->invoke_method( $sanitizer, 'check_attr_spec_rule_value', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_check_attr_spec_rule_value_casei_data() {
		return [
			'no_attributes' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'value_pass' => [
				[
					'source' => '<div attribute1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => 'value1',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_upper_pass' => [
				[
					'source' => '<div attribute1="VALUE1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => 'value1',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_fail' => [
				[
					'source' => '<div attribute1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => 'valuex',
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'value_no_attr' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => 'value1',
					],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'value_empty_pass1' => [
				[
					'source' => '<div attribute1=""></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => '',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_empty_pass2' => [
				[
					'source' => '<div attribute1></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => '',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_empty_fail' => [
				[
					'source' => '<div attribute1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => '',
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'value_alternative_attr_name_pass' => [
				[
					'source' => '<div attribute1_alternative1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => 'value1',
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_alternative_attr_name__upper_pass' => [
				[
					'source' => '<div attribute1_alternative1="VALUE1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => 'value1',
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_alternative_attr_name_fail' => [
				[
					'source' => '<div attribute1_alternative1="value2"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'value_casei' => 'value1',
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
		];
	}

	/**
	 * @dataProvider get_check_attr_spec_rule_value_casei_data
	 * @group allowed-tags-private-methods
	 */
	public function test_check_attr_spec_rule_value_casei( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$got = $this->invoke_method( $sanitizer, 'check_attr_spec_rule_value_casei', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_check_attr_spec_rule_blacklisted_value_regex() {
		return [
			'no_attributes' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'value_pass' => [
				[
					'source' => '<div attribute1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'blacklisted_value_regex' => '(not_this|or_this)',
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_fail' => [
				[
					'source' => '<div attribute1="not_this"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'blacklisted_value_regex' => '(not_this|or_this)',
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'value_no_attr' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'blacklisted_value_regex' => '(not_this|or_this)',
					],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'value_alternative_attr_name_pass' => [
				[
					'source' => '<div attribute1_alternative1="value1"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'blacklisted_value_regex' => '(not_this|or_this)',
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'value_alternative_attr_name_fail' => [
				[
					'source' => '<div attribute1_alternative1="not_this"></div>',
					'node_tag_name' => 'div',
					'attr_name' => 'attribute1',
					'attr_spec_rule' => [
						'blacklisted_value_regex' => '(not_this|or_this)',
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
		];
	}

	/**
	 * @dataProvider get_check_attr_spec_rule_blacklisted_value_regex
	 * @group allowed-tags-private-methods
	 */
	public function test_check_attr_spec_rule_blacklisted_value_regex( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$got = $this->invoke_method( $sanitizer, 'check_attr_spec_rule_blacklisted_value_regex', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_check_attr_spec_rule_allowed_protocol() {
		return [
			'no_attributes'             => [
				[
					'source'         => '<div></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'protocol_pass'             => [
				[
					'source'         => '<div attribute1="http://example.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url' => [
							'protocol' => [
								'http',
								'https',
							],
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'protocol_multiple_pass'    => [
				[
					'source'         => '<div attribute1="http://example.com, https://domain.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url' => [
							'protocol' => [
								'http',
								'https',
							],
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'protocol_fail'             => [
				[
					'source'         => '<div attribute1="data://example.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url' => [
							'protocol' => [
								'http',
								'https',
							],
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'protocol_multiple_fail'    => [
				[
					'source'         => '<img srcset="http://example.com, data://domain.com">',
					'node_tag_name'  => 'img',
					'attr_name'      => 'srcset',
					'attr_spec_rule' => [
						'value_url' => [
							'protocol' => [
								'http',
								'https',
							],
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'protocol_alternative_pass' => [
				[
					'source'         => '<div attribute1_alternative1="http://example.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'alternative_names' => [
							'attribute1_alternative1',
						],
						'value_url'         => [
							'protocol' => [
								'http',
								'https',
							],
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'protocol_alternative_fail' => [
				[
					'source'         => '<div attribute1_alternative1="data://example.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'alternative_names' => [
							'attribute1_alternative1',
						],
						'value_url'         => [
							'protocol' => [
								'http',
								'https',
							],
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
		];
	}

	/**
	 * @dataProvider get_check_attr_spec_rule_allowed_protocol
	 * @group allowed-tags-private-methods
	 */
	public function test_check_attr_spec_rule_allowed_protocol( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$got = $this->invoke_method( $sanitizer, 'check_attr_spec_rule_allowed_protocol', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_check_attr_spec_rule_disallowed_relative() {
		return [
			'no_attributes'                                 => [
				[
					'source'         => '<div></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'disallowed_relative_pass'                      => [
				[
					'source'         => '<div attribute1="http://example.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url' => [
							'allow_relative' => false,
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'disallowed_relative_multiple_pass'             => [
				[
					'source'         => '<img srcset="http://example.com, http://domain.com/path/to/resource">',
					'node_tag_name'  => 'img',
					'attr_name'      => 'srcset',
					'attr_spec_rule' => [
						'value_url' => [
							'allow_relative' => false,
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'disallowed_relative_alternative_pass'          => [
				[
					'source'         => '<div attribute1_alternative1="http://example.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url'         => [
							'allow_relative' => false,
						],
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'disallowed_relative_alternative_multiple_pass' => [
				[
					'source'         => '<div attribute1_alternative1="http://example.com, http://domain.com"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url'         => [
							'allow_relative' => false,
						],
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
			'disallowed_relative_fail'                      => [
				[
					'source'         => '<div attribute1="/relative/path/to/resource"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url' => [
							'allow_relative' => false,
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'disallowed_relative_multiple_fail'             => [
				[
					'source'         => '<div attribute1="//domain.com, /relative/path/to/resource"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url' => [
							'allow_relative' => false,
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'disallowed_relative_alternative_fail'          => [
				[
					'source'         => '<div attribute1_alternative1="/relative/path/to/resource"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [
						'value_url'         => [
							'allow_relative' => false,
						],
						'alternative_names' => [
							'attribute1_alternative1',
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
			'disallowed_relative_alternative_multiple_fail' => [
				[
					'source'         => '<div source_set="http://domain.com,  /relative/path/to/resource"></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'srcset',
					'attr_spec_rule' => [
						'value_url'         => [
							'allow_relative' => false,
						],
						'alternative_names' => [
							'source_set',
						],
					],
				],
				AMP_Rule_Spec::FAIL,
			],
		];
	}

	/**
	 * @dataProvider get_check_attr_spec_rule_disallowed_relative
	 * @group allowed-tags-private-methods
	 */
	public function test_check_attr_spec_rule_disallowed_relative( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$got = $this->invoke_method( $sanitizer, 'check_attr_spec_rule_disallowed_relative', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	/**
	 * Use this to call private methods.
	 *
	 * @param object $object      Object.
	 * @param string $method_name Method name.
	 * @param array  $parameters  Parameters.
	 * @return mixed Result.
	 */
	public function invoke_method( &$object, $method_name, array $parameters = [] ) {
		$reflection = new ReflectionClass( get_class( $object ) );

		$method = $reflection->getMethod( $method_name );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $parameters );
	}

}
