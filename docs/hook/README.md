## Hooks

### Actions

* [`amp_customizer_enqueue_preview_scripts`](amp_customizer_enqueue_preview_scripts.md) - Fires when plugins should enqueue their own scripts for the AMP Customizer preview.
* [`amp_customizer_enqueue_scripts`](amp_customizer_enqueue_scripts.md) - Fires when plugins should register settings for AMP.
* [`amp_customizer_init`](amp_customizer_init.md) - Fires when the AMP Template Customizer initializes.
* [`amp_customizer_register_settings`](amp_customizer_register_settings.md) - Fires when plugins should register settings for AMP.
* [`amp_customizer_register_ui`](amp_customizer_register_ui.md) - Fires after the AMP panel has been registered for plugins to add additional controls.
* [`amp_extract_image_dimensions_batch_callbacks_registered`](amp_extract_image_dimensions_batch_callbacks_registered.md) - Fires after the amp_extract_image_dimensions_batch filter has been added to extract by downloading images.
* [`amp_init`](amp_init.md) - Triggers on init when AMP plugin is active.
* [`amp_plugin_update`](amp_plugin_update.md) - Triggers when after amp_init when the plugin version has updated.
* [`amp_post_template_include_{$template_type}`](amp_post_template_include_{$template_type}.md) - Fires before including a template.
* [`amp_print_analytics`](amp_print_analytics.md) - Triggers before analytics entries are printed as amp-analytics tags.
* ~~[`pre_amp_render_post`](pre_amp_render_post.md) - Fires before rendering a post in AMP.~~

### Filters

* [`amp_analytics_entries`](amp_analytics_entries.md) - Add amp-analytics tags.
* [`amp_comment_posted_message`](amp_comment_posted_message.md) - Filters the message when comment submitted success message when
* [`amp_content_embed_handlers`](amp_content_embed_handlers.md) - Filters the content embed handlers.
* [`amp_content_max_width`](amp_content_max_width.md) - Filters the content max width for Reader templates.
* [`amp_content_sanitizers`](amp_content_sanitizers.md) - Filters the content sanitizers.
* [`amp_css_transient_monitoring_sampling_range`](amp_css_transient_monitoring_sampling_range.md) - Filters the sampling range to use for monitoring the transient caching of stylesheets.
* [`amp_css_transient_monitoring_threshold`](amp_css_transient_monitoring_threshold.md) - Filters the threshold to use for disabling transient caching of stylesheets.
* [`amp_customizer_get_settings`](amp_customizer_get_settings.md) - Filters the AMP Customizer settings.
* [`amp_customizer_is_enabled`](amp_customizer_is_enabled.md) - Filter whether to enable the AMP default template design settings.
* [`amp_customizer_post_type`](amp_customizer_post_type.md) - Filter the post type to retrieve the latest for use in the AMP template customizer.
* [`amp_dev_mode_element_xpaths`](amp_dev_mode_element_xpaths.md) - Filters the XPath queries for elements that should be enabled for dev mode.
* [`amp_dev_mode_enabled`](amp_dev_mode_enabled.md) - Filters whether AMP mode is enabled.
* [`amp_dev_tools_user_default_enabled`](amp_dev_tools_user_default_enabled.md) - Filters whether Developer Tools is enabled by default for a user.
* [`amp_enable_optimizer`](amp_enable_optimizer.md) - Filter whether the generated HTML output should be run through the AMP Optimizer or not.
* [`amp_enable_ssr`](amp_enable_ssr.md) - Filter whether the AMP Optimizer should use server-side rendering or not.
* [`amp_enable_ssr`](amp_enable_ssr.md) - Filter whether the AMP Optimizer should use server-side rendering or not.
* [`amp_extract_image_dimensions_batch`](amp_extract_image_dimensions_batch.md) - Filters the dimensions extracted from image URLs.
* [`amp_extract_image_dimensions_get_user_agent`](amp_extract_image_dimensions_get_user_agent.md) - Filters the user agent for onbtaining the image dimensions.
* [`amp_featured_image_minimum_height`](amp_featured_image_minimum_height.md) - Filters the minimum height required for a featured image.
* [`amp_featured_image_minimum_width`](amp_featured_image_minimum_width.md) - Filters the minimum width required for a featured image.
* ~~[`amp_frontend_show_canonical`](amp_frontend_show_canonical.md) - Filters whether to show the amphtml link on the frontend.~~
* [`amp_get_permalink`](amp_get_permalink.md) - Filters AMP permalink.
* [`amp_is_enabled`](amp_is_enabled.md) - Filters whether AMP is enabled on the current site.
* [`amp_mobile_client_side_redirection`](amp_mobile_client_side_redirection.md) - Filters whether mobile redirection should be done client-side (via JavaScript).
* [`amp_mobile_user_agents`](amp_mobile_user_agents.md) - Filters the list of user agents used to determine if the user agent from the current request is a mobile one.
* [`amp_mobile_version_switcher_link_text`](amp_mobile_version_switcher_link_text.md) - Filters the text to be used in the mobile switcher link.
* [`amp_mobile_version_switcher_styles_used`](amp_mobile_version_switcher_styles_used.md) - Filters whether the default mobile version switcher styles are printed.
* [`amp_normalized_dimension_extractor_image_url`](amp_normalized_dimension_extractor_image_url.md) - Apply filters on the normalized image URL for dimension extraction.
* [`amp_optimizer_config`](amp_optimizer_config.md) - Filter the configuration to be used for the AMP Optimizer.
* [`amp_optimizer_config`](amp_optimizer_config.md) - Filter the configuration to be used for the AMP Optimizer.
* [`amp_options_menu_is_enabled`](amp_options_menu_is_enabled.md) - Filter whether to enable the AMP settings.
* [`amp_parsed_css_transient_caching_allowed`](amp_parsed_css_transient_caching_allowed.md) - Filters whether parsed CSS is allowed to be cached in transients.
* [`amp_post_status_default_enabled`](amp_post_status_default_enabled.md) - Filters whether default AMP status should be enabled or not.
* [`amp_post_template_analytics`](amp_post_template_analytics.md) - Add amp-analytics tags.
* [`amp_post_template_customizer_settings`](amp_post_template_customizer_settings.md) - Filter AMP Customizer settings.
* [`amp_post_template_data`](amp_post_template_data.md) - Filters AMP template data.
* [`amp_post_template_dir`](amp_post_template_dir.md) - Filters the Reader template directory.
* [`amp_post_template_file`](amp_post_template_file.md) - Filters the template file being loaded for a given template type.
* [`amp_post_template_metadata`](amp_post_template_metadata.md) - Filters Schema.org metadata for a post.
* [`amp_pre_get_permalink`](amp_pre_get_permalink.md) - Filters the AMP permalink to short-circuit normal generation.
* [`amp_pre_is_mobile`](amp_pre_is_mobile.md) - Filters whether the current request is from a mobile device. This is provided as a means to short-circuit the normal determination of a mobile request below.
* [`amp_query_var`](amp_query_var.md) - Filter the AMP query variable.
* [`amp_reader_themes`](amp_reader_themes.md) - Filters supported reader themes.
* [`amp_schemaorg_metadata`](amp_schemaorg_metadata.md) - Filters Schema.org metadata for a query.
* [`amp_site_icon_url`](amp_site_icon_url.md) - Filters the publisher logo URL in the schema.org data.
* [`amp_skip_post`](amp_skip_post.md) - Filters whether to skip the post from AMP.
* [`amp_supportable_post_types`](amp_supportable_post_types.md) - Filters the list of post types which may be supported for AMP.
* [`amp_supportable_templates`](amp_supportable_templates.md) - Filters list of supportable templates.
* [`amp_to_amp_excluded_urls`](amp_to_amp_excluded_urls.md) - Filters the list of URLs which are excluded from being included in AMP-to-AMP linking.
* [`amp_to_amp_linking_element_excluded`](amp_to_amp_linking_element_excluded.md) - Filters whether AMP-to-AMP is excluded for an element.
* [`amp_to_amp_linking_enabled`](amp_to_amp_linking_enabled.md) - Filters whether AMP-to-AMP linking should be enabled.
* [`amp_validation_error`](amp_validation_error.md) - Filters the validation error array.
* [`amp_validation_error_default_sanitized`](amp_validation_error_default_sanitized.md) - Filters whether sanitization is accepted for a newly-encountered validation error .
* [`amp_validation_error_sanitized`](amp_validation_error_sanitized.md) - Filters whether the validation error should be sanitized.
* [`amp_validation_error_source_file_editor_url_template`](amp_validation_error_source_file_editor_url_template.md) - Filters the template for the URL for linking to an external editor to open a file for editing.
* [`amp_validation_error_source_file_path`](amp_validation_error_source_file_path.md) - Filters the file path to be opened in an external editor for a given AMP validation error source.
