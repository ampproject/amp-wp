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
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
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
	 * Indicate the structure for paired AMP URLs.
	 *
	 * Default value: 'query_var'
	 *
	 * @var string
	 */
	const PAIRED_URL_STRUCTURE = 'paired_url_structure';

	/**
	 * Query var paired URL structure.
	 *
	 * This is the default, where all AMP URLs end in `?amp=1`.
	 *
	 * @var string
	 */
	const PAIRED_URL_STRUCTURE_QUERY_VAR = 'query_var';

	/**
	 * Path suffix paired URL structure.
	 *
	 * This adds `/amp/` to all URLs, even pages and archives. This is a popular option for those who feel query params
	 * are bad for SEO.
	 *
	 * @var string
	 */
	const PAIRED_URL_STRUCTURE_PATH_SUFFIX = 'path_suffix';

	/**
	 * Legacy transitional paired URL structure.
	 *
	 * This involves using `?amp` for all paired AMP URLs.
	 *
	 * @var string
	 */
	const PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL = 'legacy_transitional';

	/**
	 * Legacy transitional paired URL structure.
	 *
	 * This involves using `/amp/` for all non-hierarchical post URLs which lack endpoints or query vars, or else using
	 * the same `?amp` as used by legacy transitional.
	 *
	 * @var string
	 */
	const PAIRED_URL_STRUCTURE_LEGACY_READER = 'legacy_reader';

	/**
	 * Redirect mobile visitors to the AMP version of a page when the site is in Transitional or Reader mode.
	 *
	 * Default value: false
	 *
	 * @var string
	 */
	const MOBILE_REDIRECT = 'mobile_redirect';

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
	 * List of WordPress template conditionals to define what templates are supported by AMP.
	 *
	 * Default value: [ 'is_singular' ]
	 *
	 * @var string
	 */
	const SUPPORTED_TEMPLATES = 'supported_templates';

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

	/**
	 * The slug of the theme selected to be used on AMP pages in reader mode.
	 *
	 * Default value: legacy
	 *
	 * @var string
	 */
	const READER_THEME = 'reader_theme';

	/**
	 * Theme support features from the primary theme.
	 *
	 * When using a Reader theme, the theme support features from the primary theme are stored in this option so that
	 * they will be available when the Reader theme is active.
	 *
	 * @var string
	 */
	const PRIMARY_THEME_SUPPORT = 'primary_theme_support';

	/**
	 * The key of the option storing whether the setup wizard has been completed.
	 *
	 * @var string
	 */
	const PLUGIN_CONFIGURED = 'plugin_configured';

	/**
	 * Cached slug when it is defined late.
	 *
	 * @var string
	 */
	const LATE_DEFINED_SLUG = 'late_defined_slug';

	/**
	 * Suppressed plugins
	 *
	 * @var string
	 */
	const SUPPRESSED_PLUGINS = 'suppressed_plugins';

	/**
	 * Suppressed plugins, last version.
	 *
	 * @var string
	 */
	const SUPPRESSED_PLUGINS_LAST_VERSION = 'last_version';

	/**
	 * Suppressed plugins, timestamp.
	 *
	 * @var string
	 */
	const SUPPRESSED_PLUGINS_TIMESTAMP = 'timestamp';

	/**
	 * Suppressed plugins, username.
	 *
	 * @var string
	 */
	const SUPPRESSED_PLUGINS_USERNAME = 'username';

	/**
	 * Suppressed plugins, erroring URLs.
	 *
	 * @var string
	 */
	const SUPPRESSED_PLUGINS_ERRORING_URLS = 'erroring_urls';

	/**
	 * Version of the AMP plugin for which the options were last saved.
	 *
	 * This allows for recognizing updates and triggering update-specific logic.
	 *
	 * @var string
	 */
	const VERSION = 'version';

	// ---------------------- Deprecated options down below ---------------------- //

	/**
	 * Whether to accept or reject sanitization results by default.
	 *
	 * @deprecated Removed with version 1.4.0
	 *
	 * @var string
	 */
	const AUTO_ACCEPT_SANITIZATION = 'auto_accept_sanitization';

	/**
	 * Whether the AMP stories experience is enabled.
	 *
	 * @deprecated Removed with version 1.5.0
	 *
	 * @var string
	 */
	const ENABLE_AMP_STORIES = 'enable_amp_stories';

	/**
	 * Whether responses should be statically cached.
	 *
	 * @deprecated Removed with version 1.5.0
	 *
	 * @var string
	 */
	const ENABLE_RESPONSE_CACHING = 'enable_response_caching';

	/**
	 * List of AMP experiences that are currently active.
	 *
	 * @deprecated Removed with version 1.5.0
	 *
	 * @var string
	 */
	const EXPERIENCES = 'experiences';

	/**
	 * Base URL to use when exporting a story to the file system.
	 *
	 * @deprecated Removed with version 1.5.0
	 *
	 * @var string
	 */
	const STORY_EXPORT_BASE_URL = 'story_export_base_url';

	/**
	 * Settings for the AMP stories experience.
	 *
	 * @deprecated Removed with version 1.5.0
	 *
	 * @var string
	 */
	const STORY_SETTINGS = 'story_settings';

	/**
	 * Version string at which the story templates were generated and persisted.
	 *
	 * This allows for recognizing story template updates and triggering update-specific logic.
	 *
	 * @deprecated Removed with version 1.5.0
	 *
	 * @var string
	 */
	const STORY_TEMPLATES_VERSION = 'story_templates_version';
}
