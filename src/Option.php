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
	 * The key of the option storing whether the setup wizard has been completed.
	 *
	 * @var boolean
	 */
	const PLUGIN_CONFIGURED = 'plugin_configured';

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
