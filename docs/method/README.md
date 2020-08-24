## Methods

* [`AMP_Autoloader::register()`](AMP_Autoloader/register.md) - Registers this autoloader to PHP.
* ~~[`AMP_Autoloader::register_autoload_class()`](AMP_Autoloader/register_autoload_class.md) - Allows an extensions plugin to register a class and its file for autoloading~~
* [`AMP_Base_Embed_Handler::__construct()`](AMP_Base_Embed_Handler/__construct.md) - Constructor.
* [`AMP_Base_Embed_Handler::get_scripts()`](AMP_Base_Embed_Handler/get_scripts.md) - Get mapping of AMP component names to AMP script URLs.
* [`AMP_Base_Embed_Handler::match_element_attributes()`](AMP_Base_Embed_Handler/match_element_attributes.md) - Get regex pattern for matching HTML attributes from a given tag name.
* [`AMP_Base_Embed_Handler::register_embed()`](AMP_Base_Embed_Handler/register_embed.md) - Registers embed.
* [`AMP_Base_Embed_Handler::unregister_embed()`](AMP_Base_Embed_Handler/unregister_embed.md) - Unregisters embed.
* [`AMP_Base_Sanitizer::__construct()`](AMP_Base_Sanitizer/__construct.md) - AMP_Base_Sanitizer constructor.
* [`AMP_Base_Sanitizer::add_buffering_hooks()`](AMP_Base_Sanitizer/add_buffering_hooks.md) - Add filters to manipulate output during output buffering before the DOM is constructed.
* [`AMP_Base_Sanitizer::add_or_append_attribute()`](AMP_Base_Sanitizer/add_or_append_attribute.md) - Adds or appends key and value to list of attributes
* [`AMP_Base_Sanitizer::clean_up_after_attribute_removal()`](AMP_Base_Sanitizer/clean_up_after_attribute_removal.md) - Cleans up artifacts after the removal of an attribute node.
* [`AMP_Base_Sanitizer::filter_attachment_layout_attributes()`](AMP_Base_Sanitizer/filter_attachment_layout_attributes.md) - Set attributes to node&#039;s parent element according to layout.
* [`AMP_Base_Sanitizer::filter_data_amp_attributes()`](AMP_Base_Sanitizer/filter_data_amp_attributes.md) - Set AMP attributes.
* ~~[`AMP_Base_Sanitizer::get_body_node()`](AMP_Base_Sanitizer/get_body_node.md) - Get HTML body as DOMElement from Dom\Document received by the constructor.~~
* [`AMP_Base_Sanitizer::get_data_amp_attributes()`](AMP_Base_Sanitizer/get_data_amp_attributes.md) - Get data-amp-* values from the parent node &#039;figure&#039; added by editor block.
* [`AMP_Base_Sanitizer::get_scripts()`](AMP_Base_Sanitizer/get_scripts.md) - Return array of values that would be valid as an HTML `script` element.
* [`AMP_Base_Sanitizer::get_selector_conversion_mapping()`](AMP_Base_Sanitizer/get_selector_conversion_mapping.md) - Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
* ~~[`AMP_Base_Sanitizer::get_styles()`](AMP_Base_Sanitizer/get_styles.md) - Return array of values that would be valid as an HTML `style` attribute.~~
* [`AMP_Base_Sanitizer::get_stylesheets()`](AMP_Base_Sanitizer/get_stylesheets.md) - Get stylesheets.
* [`AMP_Base_Sanitizer::get_validate_response_data()`](AMP_Base_Sanitizer/get_validate_response_data.md) - Get data that is returned in validate responses.
* ~~[`AMP_Base_Sanitizer::has_dev_mode_exemption()`](AMP_Base_Sanitizer/has_dev_mode_exemption.md) - Check whether a node is exempt from validation during dev mode.~~
* [`AMP_Base_Sanitizer::init()`](AMP_Base_Sanitizer/init.md) - Run logic before any sanitizers are run.
* ~~[`AMP_Base_Sanitizer::is_document_in_dev_mode()`](AMP_Base_Sanitizer/is_document_in_dev_mode.md) - Check whether the document of a given node is in dev mode.~~
* [`AMP_Base_Sanitizer::is_empty_attribute_value()`](AMP_Base_Sanitizer/is_empty_attribute_value.md) - Determine if an attribute value is empty.
* ~~[`AMP_Base_Sanitizer::is_exempt_from_validation()`](AMP_Base_Sanitizer/is_exempt_from_validation.md) - Check whether a certain node should be exempt from validation.~~
* [`AMP_Base_Sanitizer::maybe_enforce_https_src()`](AMP_Base_Sanitizer/maybe_enforce_https_src.md) - Decide if we should remove a src attribute if https is required.
* [`AMP_Base_Sanitizer::parse_style_string()`](AMP_Base_Sanitizer/parse_style_string.md) - Parse a style string into an associative array of style attributes.
* [`AMP_Base_Sanitizer::prepare_validation_error()`](AMP_Base_Sanitizer/prepare_validation_error.md) - Prepare validation error.
* [`AMP_Base_Sanitizer::reassemble_style_string()`](AMP_Base_Sanitizer/reassemble_style_string.md) - Reassemble a style string that can be used in a &#039;style&#039; attribute.
* [`AMP_Base_Sanitizer::remove_invalid_attribute()`](AMP_Base_Sanitizer/remove_invalid_attribute.md) - Removes an invalid attribute of a node.
* [`AMP_Base_Sanitizer::remove_invalid_child()`](AMP_Base_Sanitizer/remove_invalid_child.md) - Removes an invalid child of a node.
* [`AMP_Base_Sanitizer::sanitize()`](AMP_Base_Sanitizer/sanitize.md) - Sanitize the HTML contained in the DOMDocument received by the constructor
* [`AMP_Base_Sanitizer::sanitize_dimension()`](AMP_Base_Sanitizer/sanitize_dimension.md) - Sanitizes a CSS dimension specifier while being sensitive to dimension context.
* [`AMP_Base_Sanitizer::set_layout()`](AMP_Base_Sanitizer/set_layout.md) - Sets the layout, and possibly the &#039;height&#039; and &#039;width&#039; attributes.
* [`AMP_Base_Sanitizer::should_sanitize_validation_error()`](AMP_Base_Sanitizer/should_sanitize_validation_error.md) - Check whether or not sanitization should occur in response to validation error.
* [`AMP_Comment_Walker::build_thread_latest_date()`](AMP_Comment_Walker/build_thread_latest_date.md) - Find the timestamp of the latest child comment of a thread to set the updated time.
* [`AMP_Comment_Walker::paged_walk()`](AMP_Comment_Walker/paged_walk.md) - Output amp-list template code and place holder for comments.
* [`AMP_Comment_Walker::start_el()`](AMP_Comment_Walker/start_el.md) - Starts the element output.
* [`AMP_Content::__construct()`](AMP_Content/__construct.md) - AMP_Content constructor.
* [`AMP_Content::add_scripts()`](AMP_Content/add_scripts.md) - Add scripts.
* [`AMP_Content::add_stylesheets()`](AMP_Content/add_stylesheets.md) - Add stylesheets.
* [`AMP_Content::get_amp_content()`](AMP_Content/get_amp_content.md) - Get AMP content.
* [`AMP_Content::get_amp_scripts()`](AMP_Content/get_amp_scripts.md) - Get AMP scripts.
* ~~[`AMP_Content::get_amp_styles()`](AMP_Content/get_amp_styles.md) - Get AMP styles.~~
* [`AMP_Content::get_amp_stylesheets()`](AMP_Content/get_amp_stylesheets.md) - Get AMP styles.
* [`AMP_Content::register_embed_handlers()`](AMP_Content/register_embed_handlers.md) - Register embed handlers.
* [`AMP_Content::sanitize()`](AMP_Content/sanitize.md) - Sanitize.
* [`AMP_Content::transform()`](AMP_Content/transform.md) - Transform.
* [`AMP_Content::unregister_embed_handlers()`](AMP_Content/unregister_embed_handlers.md) - Unregister embed handlers.
* [`AMP_Widget_Archives::widget()`](AMP_Widget_Archives/widget.md) - Echoes the markup of the widget.
* [`AMP_Widget_Categories::widget()`](AMP_Widget_Categories/widget.md) - Echoes the markup of the widget.
* [`AMP_Widget_Text::inject_video_max_width_style()`](AMP_Widget_Text/inject_video_max_width_style.md) - Overrides the parent callback that strips width and height attributes.
* [`AnalyticsOptionsSubmenu::__construct()`](Admin/AnalyticsOptionsSubmenu/__construct.md) - Class constructor.
* [`AnalyticsOptionsSubmenu::add_submenu_link()`](Admin/AnalyticsOptionsSubmenu/add_submenu_link.md) - Adds a submenu link to the AMP options submenu.
* [`AnalyticsOptionsSubmenu::get_registration_action()`](Admin/AnalyticsOptionsSubmenu/get_registration_action.md) - Get the action to use for registering the service.
* [`AnalyticsOptionsSubmenu::register()`](Admin/AnalyticsOptionsSubmenu/register.md) - Adds hooks.
