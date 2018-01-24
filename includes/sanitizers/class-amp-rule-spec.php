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

	/**
	 * AMP attr_spec value check results
	 */
	const PASS           = 'pass';
	const FAIL           = 'fail';
	const NOT_APPLICABLE = 'not_applicable';

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
		'(update|item|pagination)', // Allowed for live reference points.
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

		// Needed for some tags such as analytics.
		'script'             => array(
			'attr_spec_list' => array(
				'type' => array(
					'mandatory'   => true,
					'value_casei' => 'application/json',
				),
			),
			'tag_spec'       => array(),
		),
	);
}
