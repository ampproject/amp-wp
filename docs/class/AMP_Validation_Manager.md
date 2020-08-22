## Class `AMP_Validation_Manager`

Class AMP_Validation_Manager

### Methods
<details>
<summary>`get_dev_tools_user_access`</summary>

```php
static private get_dev_tools_user_access()
```

Get dev tools user access service.


</details>
<details>
<summary>`init`</summary>

```php
static public init()
```

Initialize.


</details>
<details>
<summary>`post_supports_validation`</summary>

```php
static public post_supports_validation( $post )
```

Determine if a post supports AMP validation.


</details>
<details>
<summary>`is_theme_support_forced`</summary>

```php
static public is_theme_support_forced()
```

Determine whether AMP theme support is forced via the amp_validate query param.


</details>
<details>
<summary>`is_sanitization_auto_accepted`</summary>

```php
static public is_sanitization_auto_accepted( $error = null )
```

Return whether sanitization is initially accepted (by default) for newly encountered validation errors.

To reject all new validation errors by default, a filter can be used like so:
     add_filter( &#039;amp_validation_error_default_sanitized&#039;, &#039;__return_false&#039; );
 Whether or not a validation error is then actually sanitized is the ultimately determined by the `amp_validation_error_sanitized` filter.


</details>
<details>
<summary>`add_admin_bar_menu_items`</summary>

```php
static public add_admin_bar_menu_items( $wp_admin_bar )
```

Add menu items to admin bar for AMP.

When on a non-AMP response (transitional mode), then the admin bar item should include: - Icon: LINK SYMBOL when AMP not known to be invalid and sanitization is not forced, or CROSS MARK when AMP is known to be valid. - Parent admin item and first submenu item: link to AMP version. - Second submenu item: link to validate the URL.
 When on transitional AMP response: - Icon: CHECK MARK if no unaccepted validation errors on page, or WARNING SIGN if there are unaccepted validation errors which are being forcibly sanitized.         Otherwise, if there are unsanitized validation errors then a redirect to the non-AMP version will be done. - Parent admin item and first submenu item: link to non-AMP version. - Second submenu item: link to validate the URL.
 When on AMP-first response: - Icon: CHECK MARK if no unaccepted validation errors on page, or WARNING SIGN if there are unaccepted validation errors. - Parent admin and first submenu item: link to validate the URL.


</details>
<details>
<summary>`override_validation_error_statuses`</summary>

```php
static public override_validation_error_statuses()
```

Override validation error statuses (when requested).

When a query var is present along with the required nonce, override the status of the status of the invalid markup as requested.


</details>
<details>
<summary>`init_validate_request`</summary>

```php
static public init_validate_request()
```

Initialize a validate request.

This function is called as early as possible, at the plugins_loaded action, to see if the current request is to validate the response. If the validate query arg is absent, then this does nothing. If the query arg is present, but the value is not a valid auth key, then wp_send_json() is invoked to short-circuit with a failure. Otherwise, the static $is_validate_request variable is set to true.


</details>
<details>
<summary>`add_validation_error_sourcing`</summary>

```php
static public add_validation_error_sourcing()
```

Add hooks for doing determining sources for validation errors during preprocessing/sanitizing.


</details>
<details>
<summary>`set_theme_variables`</summary>

```php
static public set_theme_variables()
```

Set theme variables.


</details>
<details>
<summary>`handle_save_post_prompting_validation`</summary>

```php
static public handle_save_post_prompting_validation( $post_id )
```

Handle save_post action to queue re-validation of the post on the frontend.

This is intended to only apply to post edits made in the classic editor.


</details>
<details>
<summary>`validate_queued_posts_on_frontend`</summary>

```php
static public validate_queued_posts_on_frontend()
```

Validate the posts pending frontend validation.


</details>
<details>
<summary>`add_rest_api_fields`</summary>

```php
static public add_rest_api_fields()
```

Adds fields to the REST API responses, in order to display validation errors.


</details>
<details>
<summary>`get_amp_validity_rest_field`</summary>

```php
static public get_amp_validity_rest_field( $post_data, $field_name, $request )
```

Adds a field to the REST API responses to display the validation status.

First, get existing errors for the post. If there are none, validate the post and return any errors.


</details>
<details>
<summary>`map_meta_cap`</summary>

```php
static public map_meta_cap( $caps, $cap )
```

Map the amp_validate meta capability to the primitive manage_options capability.

Using a meta capability allows a site to customize which users get access to perform validation.


</details>
<details>
<summary>`has_cap`</summary>

```php
static public has_cap( $user = null )
```

Whether the user has the required capability to validate.

Checks for permissions before validating.


</details>
<details>
<summary>`add_validation_error`</summary>

```php
static public add_validation_error( array $error, array $data = array() )
```

Add validation error.


</details>
<details>
<summary>`reset_validation_results`</summary>

```php
static public reset_validation_results()
```

Reset the stored removed nodes and attributes.

After testing if the markup is valid, these static values will remain. So reset them in case another test is needed.


</details>
<details>
<summary>`print_edit_form_validation_status`</summary>

```php
static public print_edit_form_validation_status( $post )
```

Checks the AMP validity of the post content.

If it&#039;s not valid AMP, it displays an error message above the &#039;Classic&#039; editor.
 This is essentially a PHP implementation of ampBlockValidation.handleValidationErrorsStateChange() in JS.


</details>
<details>
<summary>`get_source_comment`</summary>

```php
static public get_source_comment( array $source, $is_start = true )
```

Get source start comment.


</details>
<details>
<summary>`parse_source_comment`</summary>

```php
static public parse_source_comment( \DOMComment $comment )
```

Parse source comment.


</details>
<details>
<summary>`has_dependency`</summary>

```php
static protected has_dependency( \WP_Dependencies $dependencies, $current_handle, $dependency_handle )
```

Recursively determine if a given dependency depends on another.


</details>
<details>
<summary>`is_matching_script`</summary>

```php
static protected is_matching_script( \DOMElement $element, $script_handle )
```

Determine if a script element matches a given script handle.


</details>
<details>
<summary>`locate_sources`</summary>

```php
static public locate_sources( \DOMNode $node )
```

Walk back tree to find the open sources.


</details>
<details>
<summary>`add_block_source_comments`</summary>

```php
static public add_block_source_comments( $content )
```

Add block source comments.


</details>
<details>
<summary>`handle_block_source_comment_replacement`</summary>

```php
static protected handle_block_source_comment_replacement( $matches )
```

Handle block source comment replacement.


</details>
<details>
<summary>`wrap_widget_callbacks`</summary>

```php
static public wrap_widget_callbacks()
```

Wrap callbacks for registered widgets to keep track of queued assets and the source for anything printed for validation.


</details>
<details>
<summary>`wrap_hook_callbacks`</summary>

```php
static public wrap_hook_callbacks( $hook )
```

Wrap filter/action callback functions for a given hook.

Wrapped callback functions are reset to their original functions after invocation. This runs at the &#039;all&#039; action. The shutdown hook is excluded.


</details>
<details>
<summary>`has_parameters_passed_by_reference`</summary>

```php
static protected has_parameters_passed_by_reference( $reflection )
```

Determine whether the given reflection method/function has params passed by reference.


</details>
<details>
<summary>`decorate_shortcode_source`</summary>

```php
static public decorate_shortcode_source( $output, $tag )
```

Filters the output created by a shortcode callback.


</details>
<details>
<summary>`decorate_embed_source`</summary>

```php
static public decorate_embed_source( $output, $url, $attr )
```

Filters the output created by embeds.


</details>
<details>
<summary>`decorate_filter_source`</summary>

```php
static public decorate_filter_source( $value )
```

Wraps output of a filter to add source stack comments.


</details>
<details>
<summary>`get_source`</summary>

```php
static public get_source( $callback )
```

Gets the plugin or theme of the callback, if one exists.


</details>
<details>
<summary>`can_output_buffer`</summary>

```php
static public can_output_buffer()
```

Check whether or not output buffering is currently possible.

This is to guard against a fatal error: &quot;ob_start(): Cannot use output buffering in output buffering display handlers&quot;.


</details>
<details>
<summary>`wrapped_callback`</summary>

```php
static public wrapped_callback( $callback )
```

Wraps a callback in comments if it outputs markup.

If the sanitizer removes markup, this indicates which plugin it was from. The call_user_func_array() logic is mainly copied from WP_Hook:apply_filters().


</details>
<details>
<summary>`wrap_buffer_with_source_comments`</summary>

```php
static public wrap_buffer_with_source_comments( $output )
```

Wrap output buffer with source comments.

A key reason for why this is a method and not a closure is so that the can_output_buffer method will be able to identify it by name.


</details>
<details>
<summary>`get_amp_validate_nonce`</summary>

```php
static public get_amp_validate_nonce()
```

Get nonce for performing amp_validate request.

The returned nonce is irrespective of the authenticated user.


</details>
<details>
<summary>`should_validate_response`</summary>

```php
static public should_validate_response()
```

Whether the request is to validate URL for validation errors.

All AMP responses get validated, but when the amp_validate query parameter is present, then the source information for each validation error is captured and the validation results are returned as JSON instead of the AMP HTML page.


</details>
<details>
<summary>`get_validate_response_data`</summary>

```php
static public get_validate_response_data( $sanitization_results )
```

Get response data for a validate request.


</details>
<details>
<summary>`remove_illegal_source_stack_comments`</summary>

```php
static public remove_illegal_source_stack_comments( Document $dom )
```

Remove source stack comments which appear inside of script and style tags.

HTML comments that appear inside of script and style elements get parsed as text content. AMP does not allow such HTML comments to appear inside of CDATA, resulting in validation errors to be emitted when validating a page that happens to have source stack comments output when generating JSON data (e.g. All in One SEO). Additionally, when source stack comments are output inside of style elements the result can either be CSS parse errors or incorrect stylesheet sizes being reported due to the presence of the source stack comments. So to prevent these issues from occurring, the source stack comments need to be removed from the document prior to sanitizing.


</details>
<details>
<summary>`finalize_validation`</summary>

```php
static public finalize_validation( Document $dom )
```

Finalize validation.


</details>
<details>
<summary>`update_admin_bar_item`</summary>

```php
static private update_admin_bar_item( Document $dom, $total_count, $kept_count, $unreviewed_count )
```

Override AMP status in admin bar set in \AMP_Validation_Manager::add_admin_bar_menu_items() when there are validation errors which have not been explicitly accepted.


</details>
<details>
<summary>`filter_sanitizer_args`</summary>

```php
static public filter_sanitizer_args( $sanitizers )
```

Adds the validation callback if front-end validation is needed.


</details>
<details>
<summary>`validate_after_plugin_activation`</summary>

```php
static public validate_after_plugin_activation()
```

Validates the latest published post.


</details>
<details>
<summary>`validate_url`</summary>

```php
static public validate_url( $url )
```

Validates a given URL.

The validation errors will be stored in the validation status custom post type, as well as in a transient.


</details>
<details>
<summary>`validate_url_and_store`</summary>

```php
static public validate_url_and_store( $url, $post = null )
```

Validate URL and store result.


</details>
<details>
<summary>`serialize_validation_error_messages`</summary>

```php
static public serialize_validation_error_messages( $messages )
```

Serialize validation error messages.

In order to safely pass validation error messages through redirects with query parameters, they must be serialized with a HMAC for security. The messages contain markup so the HMAC prevents tampering.


</details>
<details>
<summary>`unserialize_validation_error_messages`</summary>

```php
static public unserialize_validation_error_messages( $serialized )
```

Unserialize validation error messages.


</details>
<details>
<summary>`get_validate_url_error_message`</summary>

```php
static public get_validate_url_error_message( $error_code, $error_message = '' )
```

Get error message for a validate URL failure.


</details>
<details>
<summary>`print_plugin_notice`</summary>

```php
static public print_plugin_notice()
```

On activating a plugin, display a notice if a plugin causes an AMP validation error.


</details>
<details>
<summary>`enqueue_block_validation`</summary>

```php
static public enqueue_block_validation()
```

Enqueues the block validation script.


</details>
