<?php
/**
 * Interface Option.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * An interface to share knowledge about options stored in the AMP Options Manager.
 *
 * @todo Other options used throughout the plugin should use constants in this interface as well.
 *
 * @package AmpProject\AmpWP
 */
interface Option {

	/**
	 * Serve all templates as AMP regardless of what is being queried.
	 *
	 * Default value: true
	 *
	 * @var string
	 */
	const ALL_TEMPLATES_SUPPORTED = 'all_templates_supported';

	/**
	 * List of JSON objects that should be injected into the <amp-analytics> component.
	 *
	 * @see https://developers.google.com/analytics/devguides/collection/amp-analytics/
	 *
	 * Default value: []
	 *
	 * @var string
	 */
	const ANALYTICS = 'analytics';

	/**
	 * Persist the fact that the transient caching of stylesheets needs to be disabled.
	 *
	 * @var string
	 */
	const DISABLE_CSS_TRANSIENT_CACHING = 'amp_css_transient_monitor_disable_caching';

	/**
	 * The list of post types that have support for AMP.
	 *
	 * The provided value should be an array of WordPress post-type slugs.
	 *
	 * Default value: [ 'post' ]
	 *
	 * @var string
	 */
	const SUPPORTED_POST_TYPES = 'supported_post_types';

	/**
	 * The template mode that is being used for AMP support.
	 *
	 * Currently valid values are:
	 * - AMP_Theme_Support::STANDARD_MODE_SLUG
	 * - AMP_Theme_Support::TRANSITIONAL_MODE_SLUG
	 * - AMP_Theme_Support::READER_MODE_SLUG
	 *
	 * Default value: AMP_Theme_Support::READER_MODE_SLUG
	 *
	 * @var string
	 */
	const THEME_SUPPORT = 'theme_support';
}
