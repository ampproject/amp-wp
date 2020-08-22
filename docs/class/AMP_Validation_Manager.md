## Class `AMP_Validation_Manager`

Class AMP_Validation_Manager

### Methods
<details>
<summary><code>get_dev_tools_user_access</code></summary>

```php
static private get_dev_tools_user_access()
```

Get dev tools user access service.


</details>
<details>
<summary><code>init</code></summary>

```php
static public init()
```

Initialize.


</details>
<details>
<summary><code>post_supports_validation</code></summary>

```php
static public post_supports_validation( $post )
```

Determine if a post supports AMP validation.


</details>
<details>
<summary><code>is_theme_support_forced</code></summary>

```php
static public is_theme_support_forced()
```

Determine whether AMP theme support is forced via the amp_validate query param.


</details>
<details>
<summary><code>is_sanitization_auto_accepted</code></summary>

```php
static public is_sanitization_auto_accepted( $error = null )
```

Return whether sanitization is initially accepted (by default) for newly encountered validation errors.

To reject all new validation errors by default, a filter can be used like so:
     add_filter( &#039;amp_validation_error_default_sanitized&#039;, &#039;__return_false&#039; );
 Whether or not a validation error is then actually sanitized is the ultimately determined by the `amp_validation_error_sanitized` filter.


</details>
<details>
<summary><code>add_admin_bar_menu_items</code></summary>

```php
static public add_admin_bar_menu_items( $wp_admin_bar )
```

Add menu items to admin bar for AMP.

When on a non-AMP response (transitional mode), then the admin bar item should include: - Icon: LINK SYMBOL when AMP not known to be invalid and sanitization is not forced, or CROSS MARK when AMP is known to be valid. - Parent admin item and first submenu item: link to AMP version. - Second submenu item: link to validate the URL.
 When on transitional AMP response: - Icon: CHECK MARK if no unaccepted validation errors on page, or WARNING SIGN if there are unaccepted validation errors which are being forcibly sanitized.         Otherwise, if there are unsanitized validation errors then a redirect to the non-AMP version will be done. - Parent admin item and first submenu item: link to non-AMP version. - Second submenu item: link to validate the URL.
 When on AMP-first response: - Icon: CHECK MARK if no unaccepted validation errors on page, or WARNING SIGN if there are unaccepted validation errors. - Parent admin and first submenu item: link to validate the URL.


</details>
<details>
<summary><code>override_validation_error_statuses</code></summary>

```php
static public override_validation_error_statuses()
```

Override validation error statuses (when requested).

When a query var is present along with the required nonce, override the status of the status of the invalid markup as requested.


</details>
<details>
<summary><code>init_validate_request</code></summary>

```php
static public init_validate_request()
```

Initialize a validate request.

This function is called as early as possible, at the plugins_loaded action, to see if the current request is to validate the response. If the validate query arg is absent, then this does nothing. If the query arg is present, but the value is not a valid auth key, then wp_send_json() is invoked to short-circuit with a failure. Otherwise, the static $is_validate_request variable is set to true.


</details>
<details>
<summary><code>add_validation_error_sourcing</code></summary>

```php
static public add_validation_error_sourcing()
```

Add hooks for doing determining sources for validation errors during preprocessing/sanitizing.


</details>
<details>
<summary><code>set_theme_variables</code></summary>

```php
static public set_theme_variables()
```

Set theme variables.


</details>
<details>
<summary><code>handle_save_post_prompting_validation</code></summary>

```php
static public handle_save_post_prompting_validation( $post_id )
```

Handle save_post action to queue re-validation of the post on the frontend.

This is intended to only apply to post edits made in the classic editor.


</details>
<details>
<summary><code>validate_queued_posts_on_frontend</code></summary>

```php
static public validate_queued_posts_on_frontend()
```

Validate the posts pending frontend validation.


</details>
<details>
<summary><code>add_rest_api_fields</code></summary>

```php
static public add_rest_api_fields()
```

Adds fields to the REST API responses, in order to display validation errors.


</details>
<details>
<summary><code>get_amp_validity_rest_field</code></summary>

```php
static public get_amp_validity_rest_field( $post_data, $field_name, $request )
```

Adds a field to the REST API responses to display the validation status.

First, get existing errors for the post. If there are none, validate the post and return any errors.


</details>
<details>
<summary><code>map_meta_cap</code></summary>

```php
static public map_meta_cap( $caps, $cap )
```

Map the amp_validate meta capability to the primitive manage_options capability.

Using a meta capability allows a site to customize which users get access to perform validation.


</details>
<details>
<summary><code>has_cap</code></summary>

```php
static public has_cap( $user = null )
```

Whether the user has the required capability to validate.

Checks for permissions before validating.


</details>
<details>
<summary><code>add_validation_error</code></summary>

```php
static public add_validation_error( array $error, array $data = array() )
```

Add validation error.


</details>
<details>
<summary><code>reset_validation_results</code></summary>

```php
static public reset_validation_results()
```

Reset the stored removed nodes and attributes.

After testing if the markup is valid, these static values will remain. So reset them in case another test is needed.


</details>
<details>
<summary><code>print_edit_form_validation_status</code></summary>

```php
static public print_edit_form_validation_status( $post )
```

Checks the AMP validity of the post content.

If it&#039;s not valid AMP, it displays an error message above the &#039;Classic&#039; editor.
 This is essentially a PHP implementation of ampBlockValidation.handleValidationErrorsStateChange() in JS.


</details>
<details>
<summary><code>get_source_comment</code></summary>

```php
static public get_source_comment( array $source, $is_start = true )
```

Get source start comment.


</details>
<details>
<summary><code>parse_source_comment</code></summary>

```php
static public parse_source_comment( \DOMComment $comment )
```

Parse source comment.


</details>
<details>
<summary><code>has_dependency</code></summary>

```php
static protected has_dependency( \WP_Dependencies $dependencies, $current_handle, $dependency_handle )
```

Recursively determine if a given dependency depends on another.


</details>
<details>
<summary><code>is_matching_script</code></summary>

```php
static protected is_matching_script( \DOMElement $element, $script_handle )
```

Determine if a script element matches a given script handle.


</details>
<details>
<summary><code>locate_sources</code></summary>

```php
static public locate_sources( \DOMNode $node )
```

Walk back tree to find the open sources.


</details>
<details>
<summary><code>add_block_source_comments</code></summary>

```php
static public add_block_source_comments( $content )
```

Add block source comments.


</details>
<details>
<summary><code>handle_block_source_comment_replacement</code></summary>

```php
static protected handle_block_source_comment_replacement( $matches )
```

Handle block source comment replacement.


</details>
<details>
<summary><code>wrap_widget_callbacks</code></summary>

```php
static public wrap_widget_callbacks()
```

Wrap callbacks for registered widgets to keep track of queued assets and the source for anything printed for validation.


</details>
<details>
<summary><code>wrap_hook_callbacks</code></summary>

```php
static public wrap_hook_callbacks( $hook )
```

Wrap filter/action callback functions for a given hook.

Wrapped callback functions are reset to their original functions after invocation. This runs at the &#039;all&#039; action. The shutdown hook is excluded.


</details>
<details>
<summary><code>has_parameters_passed_by_reference</code></summary>

```php
static protected has_parameters_passed_by_reference( $reflection )
```

Determine whether the given reflection method/function has params passed by reference.


</details>
<details>
<summary><code>decorate_shortcode_source</code></summary>

```php
static public decorate_shortcode_source( $output, $tag )
```

Filters the output created by a shortcode callback.


</details>
<details>
<summary><code>decorate_embed_source</code></summary>

```php
static public decorate_embed_source( $output, $url, $attr )
```

Filters the output created by embeds.


</details>
<details>
<summary><code>decorate_filter_source</code></summary>

```php
static public decorate_filter_source( $value )
```

Wraps output of a filter to add source stack comments.


</details>
<details>
<summary><code>get_source</code></summary>

```php
static public get_source( $callback )
```

Gets the plugin or theme of the callback, if one exists.


</details>
<details>
<summary><code>can_output_buffer</code></summary>

```php
static public can_output_buffer()
```

Check whether or not output buffering is currently possible.

This is to guard against a fatal error: &quot;ob_start(): Cannot use output buffering in output buffering display handlers&quot;.


</details>
<details>
<summary><code>wrapped_callback</code></summary>

```php
static public wrapped_callback( $callback )
```

Wraps a callback in comments if it outputs markup.

If the sanitizer removes markup, this indicates which plugin it was from. The call_user_func_array() logic is mainly copied from WP_Hook:apply_filters().


</details>
<details>
<summary><code>wrap_buffer_with_source_comments</code></summary>

```php
static public wrap_buffer_with_source_comments( $output )
```

Wrap output buffer with source comments.

A key reason for why this is a method and not a closure is so that the can_output_buffer method will be able to identify it by name.


</details>
<details>
<summary><code>get_amp_validate_nonce</code></summary>

```php
static public get_amp_validate_nonce()
```

Get nonce for performing amp_validate request.

The returned nonce is irrespective of the authenticated user.


</details>
<details>
<summary><code>should_validate_response</code></summary>

```php
static public should_validate_response()
```

Whether the request is to validate URL for validation errors.

All AMP responses get validated, but when the amp_validate query parameter is present, then the source information for each validation error is captured and the validation results are returned as JSON instead of the AMP HTML page.


</details>
<details>
<summary><code>get_validate_response_data</code></summary>

```php
static public get_validate_response_data( $sanitization_results )
```

Get response data for a validate request.


</details>
<details>
<summary><code>remove_illegal_source_stack_comments</code></summary>

```php
static public remove_illegal_source_stack_comments( Document $dom )
```

Remove source stack comments which appear inside of script and style tags.

HTML comments that appear inside of script and style elements get parsed as text content. AMP does not allow such HTML comments to appear inside of CDATA, resulting in validation errors to be emitted when validating a page that happens to have source stack comments output when generating JSON data (e.g. All in One SEO). Additionally, when source stack comments are output inside of style elements the result can either be CSS parse errors or incorrect stylesheet sizes being reported due to the presence of the source stack comments. So to prevent these issues from occurring, the source stack comments need to be removed from the document prior to sanitizing.


</details>
<details>
<summary><code>finalize_validation</code></summary>

```php
static public finalize_validation( Document $dom )
```

Finalize validation.


</details>
<details>
<summary><code>update_admin_bar_item</code></summary>

```php
static private update_admin_bar_item( Document $dom, $total_count, $kept_count, $unreviewed_count )
```

Override AMP status in admin bar set in \AMP_Validation_Manager::add_admin_bar_menu_items() when there are validation errors which have not been explicitly accepted.


</details>
<details>
<summary><code>filter_sanitizer_args</code></summary>

```php
static public filter_sanitizer_args( $sanitizers )
```

Adds the validation callback if front-end validation is needed.


</details>
<details>
<summary><code>validate_after_plugin_activation</code></summary>

```php
static public validate_after_plugin_activation()
```

Validates the latest published post.


</details>
<details>
<summary><code>validate_url</code></summary>

```php
static public validate_url( $url )
```

Validates a given URL.

The validation errors will be stored in the validation status custom post type, as well as in a transient.


</details>
<details>
<summary><code>validate_url_and_store</code></summary>

```php
static public validate_url_and_store( $url, $post = null )
```

Validate URL and store result.


</details>
<details>
<summary><code>serialize_validation_error_messages</code></summary>

```php
static public serialize_validation_error_messages( $messages )
```

Serialize validation error messages.

In order to safely pass validation error messages through redirects with query parameters, they must be serialized with a HMAC for security. The messages contain markup so the HMAC prevents tampering.


</details>
<details>
<summary><code>unserialize_validation_error_messages</code></summary>

```php
static public unserialize_validation_error_messages( $serialized )
```

Unserialize validation error messages.


</details>
<details>
<summary><code>get_validate_url_error_message</code></summary>

```php
static public get_validate_url_error_message( $error_code, $error_message = '' )
```

Get error message for a validate URL failure.


</details>
<details>
<summary><code>print_plugin_notice</code></summary>

```php
static public print_plugin_notice()
```

On activating a plugin, display a notice if a plugin causes an AMP validation error.


</details>
<details>
<summary><code>enqueue_block_validation</code></summary>

```php
static public enqueue_block_validation()
```

Enqueues the block validation script.


</details>
