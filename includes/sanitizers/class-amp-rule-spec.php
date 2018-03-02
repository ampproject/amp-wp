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

	/**
	 * AMP rule_spec types
	 */
	const ATTR_SPEC_LIST = 'attr_spec_list';
	const TAG_SPEC       = 'tag_spec';
	const CDATA          = 'cdata';

	/**
	 * AMP attr_spec value check results.
	 *
	 * In 0.7 these changed from strings to integers to speed up comparisons.
	 */
	const PASS           = 1;
	const FAIL           = 0;
	const NOT_APPLICABLE = -1;

	/**
	 * HTML Element Tag rule names
	 */
	const DISALLOWED_ANCESTOR = 'disallowed_ancestor';
	const MANDATORY_ANCESTOR  = 'mandatory_ancestor';
	const MANDATORY_PARENT    = 'mandatory_parent';

	/**
	 * HTML Element Attribute rule names
	 */
	const ALLOW_EMPTY             = 'allow_empty';
	const ALLOW_RELATIVE          = 'allow_relative';
	const ALLOWED_PROTOCOL        = 'allowed_protocol';
	const ALTERNATIVE_NAMES       = 'alternative_names';
	const BLACKLISTED_VALUE_REGEX = 'blacklisted_value_regex';
	const DISALLOWED_DOMAIN       = 'disallowed_domain';
	const MANDATORY               = 'mandatory';
	const VALUE                   = 'value';
	const VALUE_CASEI             = 'value_casei';
	const VALUE_REGEX             = 'value_regex';
	const VALUE_REGEX_CASEI       = 'value_regex_casei';
	const VALUE_PROPERTIES        = 'value_properties';
	const VALUE_URL               = 'value_url';

	/**
	 * If a node type listed here is invalid, it and it's subtree will be
	 * removed if it is invalid. This is mainly  because any children will be
	 * non-functional without this parent.
	 *
	 * If a tag is not listed here, it will be replaced by its children if it
	 * is invalid.
	 *
	 * @todo There are other nodes that should probably be listed here as well.
	 *
	 * @var array
	 */
	public static $node_types_to_remove_if_invalid = array(
		'form',
		'input',
		'link',
		'meta',
		'style',
		// Include 'script' here?
	);

	/**
	 *  It is mentioned in the documentation in several places that data-*
	 *  is generally allowed, but there is no specific rule for it in the
	 *  protoascii file, so we include it here.
	 *
	 * @var array
	 */
	public static $whitelisted_attr_regex = array(
		'@^data-[a-zA-Z][\\w:.-]*$@uis',
		'(update|item|pagination|option|selected|disabled)', // Allowed for live reference points.
	);

	/**
	 * List of boolean attributes.
	 *
	 * @since 0.7
	 * @var array
	 */
	public static $boolean_attributes = array(
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
	);

	/**
	 * Additional allowed tags.
	 *
	 * @var array
	 */
	public static $additional_allowed_tags = array(

		// An experimental tag with no protoascii.
		'amp-share-tracking' => array(
			'attr_spec_list' => array(),
			'tag_spec'       => array(),
		),
	);
}
