<?php
/**
 * Class AMP_Rule_Spec
 *
 * @package AMP
 */

/**
 * Class AMP_Rule_Spec
 *
 * Set of constants used throughout the sanitizer.
 */
abstract class AMP_Rule_Spec {
	/*
	 * AMP rule_spec types
	 */
	const ATTR_SPEC_LIST = 'attr_spec_list';
	const TAG_SPEC       = 'tag_spec';
	const CDATA          = 'cdata';

	/*
	 * AMP attr_spec value check results.
	 *
	 * In 0.7 these changed from strings to integers to speed up comparisons.
	 */
	const PASS           = 1;
	const FAIL           = 0;
	const NOT_APPLICABLE = -1;

	/*
	 * HTML Element Tag rule names
	 */
	const DISALLOWED_ANCESTOR = 'disallowed_ancestor';
	const MANDATORY_ANCESTOR  = 'mandatory_ancestor';
	const MANDATORY_PARENT    = 'mandatory_parent';
	const DESCENDANT_TAG_LIST = 'descendant_tag_list';
	const CHILD_TAGS          = 'child_tags';

	/*
	 * HTML Element Attribute rule names
	 */
	const ALLOW_EMPTY             = 'allow_empty';
	const ALLOW_RELATIVE          = 'allow_relative';
	const ALLOWED_PROTOCOL        = 'protocol';
	const ALTERNATIVE_NAMES       = 'alternative_names';
	const BLACKLISTED_VALUE_REGEX = 'blacklisted_value_regex';
	const MANDATORY               = 'mandatory';
	const MANDATORY_ANYOF         = 'mandatory_anyof';
	const MANDATORY_ONEOF         = 'mandatory_oneof';
	const VALUE                   = 'value';
	const VALUE_CASEI             = 'value_casei';
	const VALUE_REGEX             = 'value_regex';
	const VALUE_REGEX_CASEI       = 'value_regex_casei';
	const VALUE_PROPERTIES        = 'value_properties';
	const VALUE_URL               = 'value_url';

	/*
	 * AMP layout types
	 */
	const LAYOUT_NODISPLAY    = 'nodisplay';
	const LAYOUT_FIXED        = 'fixed';
	const LAYOUT_FIXED_HEIGHT = 'fixed-height';
	const LAYOUT_RESPONSIVE   = 'responsive';
	const LAYOUT_CONTAINER    = 'container';
	const LAYOUT_FILL         = 'fill';
	const LAYOUT_FLEX_ITEM    = 'flex-item';
	const LAYOUT_FLUID        = 'fluid';
	const LAYOUT_INTRINSIC    = 'intrinsic';

	/**
	 * Attribute name for AMP dev mode.
	 *
	 * @since 1.2.2
	 * @link https://github.com/ampproject/amphtml/issues/20974
	 * @var string
	 */
	const DEV_MODE_ATTRIBUTE = 'data-ampdevmode';

	/**
	 * Supported layout values.
	 *
	 * @since 1.0
	 * @var array
	 */
	public static $layout_enum = [
		1 => 'nodisplay',
		2 => 'fixed',
		3 => 'fixed-height',
		4 => 'responsive',
		5 => 'container',
		6 => 'fill',
		7 => 'flex-item',
		8 => 'fluid',
		9 => 'intrinsic',
	];

	/**
	 * List of boolean attributes.
	 *
	 * @since 0.7
	 * @var array
	 */
	public static $boolean_attributes = [
		'allowfullscreen',
		'async',
		'autofocus',
		'autoplay',
		'checked',
		'compact',
		'controls',
		'declare',
		'default',
		'defaultchecked',
		'defaultmuted',
		'defaultselected',
		'defer',
		'disabled',
		'draggable',
		'enabled',
		'formnovalidate',
		'hidden',
		'indeterminate',
		'inert',
		'ismap',
		'itemscope',
		'loop',
		'multiple',
		'muted',
		'nohref',
		'noresize',
		'noshade',
		'novalidate',
		'nowrap',
		'open',
		'pauseonexit',
		'readonly',
		'required',
		'reversed',
		'scoped',
		'seamless',
		'selected',
		'sortable',
		'spellcheck',
		'translate',
		'truespeed',
		'typemustmatch',
		'visible',
	];

	/**
	 * Additional allowed tags.
	 *
	 * @var array
	 */
	public static $additional_allowed_tags = [

		// An experimental tag with no protoascii.
		'amp-share-tracking' => [
			'attr_spec_list' => [],
			'tag_spec'       => [],
		],
	];
}
