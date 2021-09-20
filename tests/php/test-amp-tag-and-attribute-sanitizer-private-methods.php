<?php
// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_dump
// phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped

use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\TestCase;

class AMP_Tag_And_Attribute_Sanitizer_Attr_Spec_Rules_Test extends TestCase {

	use PrivateAccess;

	protected $allowed_tags;
	protected $globally_allowed_attrs;
	protected $layout_allowed_attrs;

	public function set_up() {
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
			'test_attr_spec_rule_disallowed_value_regex_pass' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_disallowed_value_regex',
				],
				'expected' => AMP_Rule_Spec::PASS,
			],
			'test_attr_spec_rule_disallowed_value_regex_fail' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'components',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_disallowed_value_regex',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_disallowed_value_regex_fail_2' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'import',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'check_attr_spec_rule_disallowed_value_regex',
				],
				'expected' => AMP_Rule_Spec::FAIL,
			],
			'test_attr_spec_rule_disallowed_value_regex_na' => [
				[
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'check_attr_spec_rule_disallowed_value_regex',
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

		$got = $this->call_private_method( $sanitizer, $data['func_name'], [ $node, $data['attribute_name'], $attr_spec_rule ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $source, wp_json_encode( $data ) ) );
	}

	public function get_is_allowed_attribute_data() {
		return [
			'test_is_amp_allowed_attribute_allowlisted_regex_pass' => [
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

		$got = $this->call_private_method( $sanitizer, $data['func_name'], [ $attr, $attr_spec_list ] );

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

		$this->call_private_method( $sanitizer, 'remove_node', [ $node ] );

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
			'nested_invalid_elements' => [
				[
					'source' => '<div><bad-details><summary><p>Example Summary</p></summary><p>Example expanded text</p></bad-details></div>',
					'tag_name' => 'bad-details',
				],
				'<div><summary><p>Example Summary</p></summary><p>Example expanded text</p></div>',
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

		$this->call_private_method( $sanitizer, 'replace_node_with_children', [ $node ] );

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

		$got = $this->call_private_method( $sanitizer, 'get_ancestor_with_matching_spec_name', [ $node, $data['ancestor_tag_name'] ] );

		$this->assertEquals( $ancestor_node, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	/**
	 * Get test data for test_get_ancestor_with_matching_spec_name.
	 *
	 * @return array Test data.
	 */
	public function get_validate_attr_spec_list_for_node_data() {
		return [
			'no_attributes' => [
				[
					'source' => '<div></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [],
				],
				1, // Because there are no mandatory attributes.
			],
			'attributes_no_spec' => [
				[
					'source' => '<div attribute1 attribute2 attribute3></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [],
				],
				1, // Because there are no mandatory attributes.
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
				2, // Because there are no mandatory attributes.
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
				4,
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
				2,
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
				4,
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
				2,
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
				4,
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
				2,
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
				2, // Still passes because relative URL is not checked until sanitization.
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
				2, // Allow empty is not used until sanitization.
			],
			'attributes_disallowed_regex' => [
				[
					'source' => '<div attribute1="disallowed_value"></div>',
					'node_tag_name' => 'div',
					'attr_spec_list' => [
						'attribute1' => [
							'disallowed_value_regex' => 'disallowed_value',
							'alternative_names' => [
								'attribute1_alternative1',
								'attribute1_alternative2',
								'attribute1_alternative3',
							],
						],
					],
				],
				2,
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

		$got = $this->call_private_method( $sanitizer, 'validate_attr_spec_list_for_node', [ $node, $data['attr_spec_list'] ] );

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

		$got = $this->call_private_method( $sanitizer, 'check_attr_spec_rule_value', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

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

		$got = $this->call_private_method( $sanitizer, 'check_attr_spec_rule_value_casei', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_check_attr_spec_rule_disallowed_value_regex() {
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
						'disallowed_value_regex' => '(not_this|or_this)',
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
						'disallowed_value_regex' => '(not_this|or_this)',
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
						'disallowed_value_regex' => '(not_this|or_this)',
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
						'disallowed_value_regex' => '(not_this|or_this)',
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
						'disallowed_value_regex' => '(not_this|or_this)',
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
	 * Gets the test data for test_check_attr_spec_rule_valid_url().
	 *
	 * @return array The test data.
	 */
	public function get_check_attr_spec_rule_valid_url() {
		return [
			'no_attribute'              => [
				'<a></a>',
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'correct_url'               => [
				'<a baz="https://wp.org"></a>',
				AMP_Rule_Spec::PASS,
			],
			'correct_url_leading_space' => [
				'<a baz=" https://wp.org"></a>',
				AMP_Rule_Spec::PASS,
			],
			'non_parseable_url'         => [
				'<a baz="//"></a>',
				AMP_Rule_Spec::FAIL,
			],
			'wrong_protocol'            => [
				'<a baz="@:wp.org"></a>',
				AMP_Rule_Spec::FAIL,
			],
			'wrong_host'                => [
				'<a baz="https://wp$camp.org"></a>',
				AMP_Rule_Spec::FAIL,
			],
		];
	}

	/**
	 * Tests check_attr_spec_rule_valid_url.
	 *
	 * @dataProvider get_check_attr_spec_rule_valid_url
	 * @group allowed-tags-private-methods
	 * @covers AMP_Tag_And_Attribute_Sanitizer::check_attr_spec_rule_valid_url()
	 *
	 * @param array  $source   The HTML source to test.
	 * @param string $expected The expected return value.
	 * @throws ReflectionException If it's not possible to create a reflection to call the private method.
	 */
	public function test_check_attr_spec_rule_valid_url( $source, $expected ) {
		$node_tag_name  = 'a';
		$dom            = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer      = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$node           = $dom->getElementsByTagName( $node_tag_name )->item( 0 );
		$attr_name      = 'baz';
		$attr_spec_rule = [ 'value_url' => [] ];

		$this->assertEquals(
			$expected,
			$this->call_private_method( $sanitizer, 'check_attr_spec_rule_valid_url', [ $node, $attr_name, $attr_spec_rule ] ),
			sprintf( 'using source: %s', $source )
		);
	}

	/**
	 * @dataProvider get_check_attr_spec_rule_disallowed_value_regex
	 * @group allowed-tags-private-methods
	 */
	public function test_check_attr_spec_rule_disallowed_value_regex( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$got = $this->call_private_method( $sanitizer, 'check_attr_spec_rule_disallowed_value_regex', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}

	public function get_check_attr_spec_rule_allowed_protocol() {
		return [
			'no_attributes'               => [
				[
					'source'         => '<div></div>',
					'node_tag_name'  => 'div',
					'attr_name'      => 'attribute1',
					'attr_spec_rule' => [],
				],
				AMP_Rule_Spec::NOT_APPLICABLE,
			],
			'protocol_pass'               => [
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
			'protocol_pass_leading_space' => [
				[
					'source'         => '<div attribute1=" http://example.com"></div>',
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
			'protocol_multiple_pass'      => [
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
			'protocol_fail'               => [
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
			'protocol_multiple_fail'      => [
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
			'protocol_alternative_pass'   => [
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
			'protocol_alternative_fail'   => [
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
			'protocol_mailto_pass'        => [
				[
					'source'         => '<a href="mailto:foo@example.com?&#038;subject=Example&#038;body=https://example.com/"></a>',
					'node_tag_name'  => 'a',
					'attr_name'      => 'href',
					'attr_spec_rule' => [
						'value_url' => [
							'protocol' => [
								'mailto',
								'https',
							],
						],
					],
				],
				AMP_Rule_Spec::PASS,
			],
		];
	}

	/**
	 * Gets the test data for test_parse_protocol().
	 *
	 * @return array The test data.
	 */
	public function get_parse_protocol_data() {
		return [
			'empty_string'      => [
				'',
				false,
			],
			'only_space'        => [
				'  ',
				false,
			],
			'traditional_https' => [
				'https://example.com',
				'https',
			],
			'trailing_space'    => [
				'https://foo.com ',
				'https',
			],
			'no_colon'          => [
				'//image.png ',
				false,
			],
			'mailto'            => [
				'mailto:?&#038;subject=Foo&#038;body=https://example.com/',
				'mailto',
			],
		];
	}

	/**
	 * Tests parse_protocol.
	 *
	 * @dataProvider get_parse_protocol_data
	 * @group allowed-tags-private-methods
	 * @covers AMP_Tag_And_Attribute_Sanitizer::parse_protocol()
	 *
	 * @param array  $url      The URL to parse.
	 * @param string $expected The expected return value.
	 * @throws ReflectionException If it's not possible to create a reflection to call the private method.
	 */
	public function test_parse_protocol( $url, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( '' );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$this->assertEquals(
			$expected,
			$this->call_private_method( $sanitizer, 'parse_protocol', [ $url ] )
		);
	}

	/**
	 * Gets the test data for test_normalize_url_from_attribute_value().
	 *
	 * @return array The test data.
	 */
	public function get_normalize_url_data() {
		$normalized_url = 'https://example.com';

		return [
			'nothing_to_remove'             => [
				'https://example.com',
			],
			'empty_string'                  => [
				'',
			],
			'only_space'                    => [
				'  ',
				'',
			],
			'leading_space'                 => [
				'  https://example.com',
				$normalized_url,
			],
			'leading_tab'                   => [
				"\thttps://example.com",
				$normalized_url,
			],
			'trailing_linefeed'             => [
				"https://example.com \n",
				$normalized_url,
			],
			'trailing_space'                => [
				'https://example.com  ',
				$normalized_url,
			],
			'enclosed_in_spaces'            => [
				' https://example.com ',
				$normalized_url,
			],
			'space_inside'                  => [
				' https: //exam ple.com ',
				'https: //exam ple.com',
			],
			'tabs_inside'                   => [
				"https:\t//exam\tple.com ",
				$normalized_url,
			],
			'leading_slashes'               => [
				'//example.com',
			],
			'url_encoded_space_not_removed' => [
				'https://example.com?foo=++baz',
			],
		];
	}

	/**
	 * Tests normalize_url_from_attribute_value.
	 *
	 * @dataProvider get_normalize_url_data
	 * @group allowed-tags-private-methods
	 * @covers AMP_Tag_And_Attribute_Sanitizer::normalize_url_from_attribute_value()
	 *
	 * @param array       $url      The URL to normalize.
	 * @param string|null $expected The expected return value.
	 * @throws ReflectionException If it's not possible to create a reflection to call the private method.
	 */
	public function test_normalize_url_from_attribute_value( $url, $expected = null ) {
		if ( null === $expected ) {
			$expected = $url;
		}

		$dom       = AMP_DOM_Utils::get_dom_from_content( '' );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$this->assertEquals(
			$expected,
			$this->call_private_method( $sanitizer, 'normalize_url_from_attribute_value', [ $url ] )
		);
	}

	/**
	 * @dataProvider get_check_attr_spec_rule_allowed_protocol
	 * @group allowed-tags-private-methods
	 */
	public function test_check_attr_spec_rule_allowed_protocol( $data, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $data['source'] );
		$node      = $dom->getElementsByTagName( $data['node_tag_name'] )->item( 0 );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );

		$got = $this->call_private_method( $sanitizer, 'check_attr_spec_rule_allowed_protocol', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

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

		$got = $this->call_private_method( $sanitizer, 'check_attr_spec_rule_disallowed_relative', [ $node, $data['attr_name'], $data['attr_spec_rule'] ] );

		$this->assertEquals( $expected, $got, sprintf( "using source: %s\n%s", $data['source'], wp_json_encode( $data ) ) );
	}
}
