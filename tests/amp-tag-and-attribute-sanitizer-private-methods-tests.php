<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php' );

class AMP_Tag_And_Attribute_Sanitizer_Attr_Spec_Rules_Test extends WP_UnitTestCase {

	protected $allowed_tags;
	protected $globally_allowed_attrs;
	protected $layout_allowed_attrs;

	public function setUp() {
		$this->allowed_tags = apply_filters( 'amp_allowed_tags', AMP_Allowed_Tags_Generated::get_allowed_tags() );
		$this->globally_allowed_attributes = apply_filters( 'amp_globally_allowed_attributes', AMP_Allowed_Tags_Generated::get_allowed_attributes() );
		$this->layout_allowed_attributes = apply_filters( 'amp_globally_allowed_attributes', AMP_Allowed_Tags_Generated::get_allowed_attributes() );
	}
	
	public function get_attr_spec_rule_data() {
		return array(
			'test_attr_spec_rule_mandatory_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'src',
					'attribute_value' => '/path/to/resource',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_mandatory',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_mandatory_alternate_attr_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'src',
					'use_alternate_name' => 'srcset',
					'attribute_value' => '/path/to/resource',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_mandatory',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_mandatory_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'src',
					'attribute_value' => '/path/to/resource',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_mandatory',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_mandatory_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'alt',
					'attribute_value' => 'alternate',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_mandatory',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_value_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'amp-mustache',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_not_applicable' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_value',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_value_casei_lower_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'type',
					'attribute_value' => 'text/html',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_casei',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_casei_upper_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'type',
					'attribute_value' => 'TEXT/HTML',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_casei',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_casei_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_casei',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_casei_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'template',
					'attribute_name' => 'type',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_value_casei',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_value_regex_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'target',
					'attribute_value' => '_blank',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_regex',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_regex_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'target',
					'attribute_value' => '_blankzzz',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_regex',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_regex_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'target',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_value_regex',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_value_regex_casei_lower_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'false',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_regex_casei',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_regex_casei_upper_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'FALSE',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_regex_casei',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_regex_casei_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'invalid',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_value_regex_casei',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_regex_casei_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-playbuzz',
					'attribute_name' => 'data-comments',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_value_regex_casei',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_allowed_protocol_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'href',
					'attribute_value' => 'http://example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_allowed_protocol_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'href',
					'attribute_value' => 'evil://example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'href',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),


			'test_attr_spec_rule_allowed_protocol_srcset_single_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'srcset',
					'attribute_value' => 'http://veryunique.com/img.jpg',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'srcset',
					'attribute_value' => 'http://example.com/img.jpg, https://example.com/whatever.jpg, image.jpg',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_single_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'srcset',
					'attribute_value' => 'bad://example.com/img.jpg',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'srcset',
					'attribute_value' => 'bad://example.com/img.jpg, evil://example.com/whatever.jpg',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_fail_good_first' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'srcset',
					'attribute_value' => 'https://example.com/img.jpg, evil://example.com/whatever.jpg',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_fail_bad_first' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'srcset',
					'attribute_value' => 'evil://example.com/img.jpg, https://example.com/whatever.jpg',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-img',
					'attribute_name' => 'srcset',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_allowed_protocol',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_disallowed_relative_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-share-endpoint',
					'attribute_value' => 'http://example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_disallowed_relative',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_disallowed_relative_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-share-endpoint',
					'attribute_value' => '//example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_disallowed_relative',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_relative_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-share-endpoint',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_disallowed_relative',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_disallowed_empty_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-user-notification',
					'attribute_name' => 'data-dismiss-href',
					'attribute_value' => 'https://example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_disallowed_empty',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_disallowed_empty_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-user-notification',
					'attribute_name' => 'data-dismiss-href',
					'attribute_value' => '',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_disallowed_empty',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_empty_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-user-notification',
					'attribute_name' => 'data-dismiss-href',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_disallowed_empty',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_disallowed_domain_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'form',
					'attribute_name' => 'action',
					'attribute_value' => 'https://example.com',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_disallowed_domain',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_disallowed_domain_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'form',
					'attribute_name' => 'action',
					'attribute_value' => '//cdn.ampproject.org',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_disallowed_domain',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_domain_fail_2' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'form',
					'attribute_name' => 'action',
					'attribute_value' => 'https://cdn.ampproject.org',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_disallowed_domain',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_domain_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'form',
					'attribute_name' => 'action',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_disallowed_domain',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_blacklisted_value_regex_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				),
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_blacklisted_value_regex_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'components',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_blacklisted_value_regex_fail_2' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'import',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				),
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_blacklisted_value_regex_na' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'a',
					'attribute_name' => 'rel',
					'attribute_value' => 'invalid',
					'include_attr' => false,
					'include_attr_value' => false,
					'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				),
				'expected' => AMP_Rule_Spec::not_applicable,
			),
		);
	}

	/**
	 * @dataProvider get_attr_spec_rule_data
	 * @group allowed-attrs-private-methods
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

		$attr_spec_list = $this->allowed_tags[ $data['tag_name'] ][$data['rule_spec_index']]['attr_spec_list'];
		foreach( $attr_spec_list as $attr_name => $attr_val ) {
			if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] ) ) {
				foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		$attr_spec_rule = $attr_spec_list[ $data['attribute_name'] ];

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$this->invoke_method( $sanitizer, 'get_whitelist_data' );
		$node = $dom->getElementsByTagName( $data['tag_name'] )->item( 0 );

		$got = $this->invoke_method( $sanitizer, $data['func_name'], array( $node, $data['attribute_name'], $attr_spec_rule ) );

		if ( $expected != $got ) {
			printf( 'using source: %s' . PHP_EOL, $source );
			var_dump( $data );
		}

		$this->assertEquals( $expected, $got );
	}

	public function get_is_allowed_attribute_data() {
		return array(
			'test_is_amp_allowed_attribute_whitelisted_regex_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'data-whatever-else-you-want',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_global_attribute_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'itemid',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_tag_spec_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'media',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_disallowed_attr_fail' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-social-share',
					'attribute_name' => 'bad-attr',
					'attribute_value' => 'whatever',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => false,
			),

			'test_is_amp_allowed_attribute_layout_height_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'height',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_heights_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'heights',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_width_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'width',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_sizes_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'sizes',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_layout_pass' => array(
				array(
					'rule_spec_index' => 0,
					'tag_name' => 'amp-ad',
					'attribute_name' => 'layout',
					'attribute_value' => 'not_tested',
					'include_attr' => true,
					'include_attr_value' => true,
					'func_name' => 'is_amp_allowed_attribute',
				),
				'expected' => true,
			),
		);
	}

	/**
	 * @dataProvider get_is_allowed_attribute_data
	 * @group allowed-attrs-private-methods
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

		$attr_spec_list = $this->allowed_tags[ $data['tag_name'] ][$data['rule_spec_index']]['attr_spec_list'];
		foreach( $attr_spec_list as $attr_name => $attr_val ) {
			if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] ) ) {
				foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$this->invoke_method( $sanitizer, 'get_whitelist_data' );
		$node = $dom->getElementsByTagName( $data['tag_name'] )->item( 0 );

		$got = $this->invoke_method( $sanitizer, $data['func_name'], array( $data['attribute_name'], $attr_spec_list ) );

		if ( $expected != $got ) {
			printf( 'using source: %s' . PHP_EOL, $source );
			var_dump( $data );
		}

		$this->assertEquals( $expected, $got );
	}

	// Use this to call private methods
	public function invoke_method(&$object, $methodName, array $parameters = array()) {
	    $reflection = new \ReflectionClass(get_class($object));
	    $method = $reflection->getMethod($methodName);
	    $method->setAccessible(true);

	    return $method->invokeArgs($object, $parameters);
	}
}

?>