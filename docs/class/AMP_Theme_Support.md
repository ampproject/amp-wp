## Class `AMP_Theme_Support`

Class AMP_Theme_Support

Callbacks for adding AMP-related things when theme support is added.

### Methods
<details>
<summary>`init`</summary>

```php
static public init()
```

Initialize.


</details>
<details>
<summary>`is_support_added_via_option`</summary>

```php
static public is_support_added_via_option()
```

Determine whether theme support was added via admin option.


</details>
<details>
<summary>`get_support_mode_added_via_option`</summary>

```php
static public get_support_mode_added_via_option()
```

Get the theme support mode added via admin option.


</details>
<details>
<summary>`get_support_mode_added_via_theme`</summary>

```php
static public get_support_mode_added_via_theme()
```

Get the theme support mode added via theme.


</details>
<details>
<summary>`get_support_mode`</summary>

```php
static public get_support_mode()
```

Get theme support mode.


</details>
<details>
<summary>`read_theme_support`</summary>

```php
static public read_theme_support()
```

Check theme support args or add theme support if option is set in the admin.

In older versions of the plugin, the DB option was only considered if the theme does not already explicitly support AMP. This is no longer the case. The DB option is the only value that is considered.


</details>
<details>
<summary>`get_theme_support_args`</summary>

```php
static public get_theme_support_args()
```

Get the theme support args.

This avoids having to repeatedly call `get_theme_support()`, check the args, shift an item off the array, and so on.


</details>
<details>
<summary>`supports_reader_mode`</summary>

```php
static public supports_reader_mode()
```

Gets whether the parent or child theme supports Reader Mode.

True if the theme does not call add_theme_support( &#039;amp&#039; ) at all, and it has an amp/ directory for templates.


</details>
<details>
<summary>`finish_init`</summary>

```php
static public finish_init()
```

Finish initialization once query vars are set.


</details>
<details>
<summary>`ensure_proper_amp_location`</summary>

```php
static public ensure_proper_amp_location()
```

Ensure that the current AMP location is correct.


</details>
<details>
<summary>`redirect_non_amp_url`</summary>

```php
static public redirect_non_amp_url( $status = 302 )
```

Redirect to non-AMP version of the current URL, such as because AMP is canonical or there are unaccepted validation errors.

If the current URL is already AMP-less then do nothing.


</details>
<details>
<summary>`is_paired_available`</summary>

```php
static public is_paired_available()
```

Determines whether transitional mode is available.

When &#039;amp&#039; theme support has not been added or canonical mode is enabled, then this returns false.


</details>
<details>
<summary>`is_customize_preview_iframe`</summary>

```php
static public is_customize_preview_iframe()
```

Determine whether the user is in the Customizer preview iframe.


</details>
<details>
<summary>`add_amp_template_filters`</summary>

```php
static public add_amp_template_filters()
```

Register filters for loading AMP-specific templates.


</details>
<details>
<summary>`get_template_availability`</summary>

```php
static public get_template_availability( $query = null )
```

Determine template availability of AMP for the given query.

This is not intended to return whether AMP is available for a _specific_ post. For that, use `amp_is_post_supported()`.


</details>
<details>
<summary>`get_supportable_templates`</summary>

```php
static public get_supportable_templates()
```

Get the templates which can be supported.


</details>
<details>
<summary>`add_hooks`</summary>

```php
static public add_hooks()
```

Register hooks.


</details>
<details>
<summary>`register_widgets`</summary>

```php
static public register_widgets()
```

Register/override widgets.


</details>
<details>
<summary>`register_content_embed_handlers`</summary>

```php
static public register_content_embed_handlers()
```

Register content embed handlers.

This was copied from `AMP_Content::register_embed_handlers()` due to being a private method and due to `AMP_Content` not being well suited for use in AMP canonical.


</details>
<details>
<summary>`set_comments_walker`</summary>

```php
static public set_comments_walker( $args )
```

Add the comments template placeholder marker


</details>
<details>
<summary>`amend_comment_form`</summary>

```php
static public amend_comment_form()
```

Amend the comment form with the redirect_to field to persist the AMP page after submission.


</details>
<details>
<summary>`amend_comments_link`</summary>

```php
static public amend_comments_link( $comments_link )
```

Amend the comments/redpond links to go to non-AMP page when in legacy Reader mode.


</details>
<details>
<summary>`filter_amp_template_hierarchy`</summary>

```php
static public filter_amp_template_hierarchy( $templates )
```

Prepends template hierarchy with template_dir for AMP transitional mode templates.


</details>
<details>
<summary>`get_current_canonical_url`</summary>

```php
static public get_current_canonical_url()
```

Get canonical URL for current request.


</details>
<details>
<summary>`get_comment_form_state_id`</summary>

```php
static public get_comment_form_state_id( $post_id )
```

Get the ID for the amp-state.


</details>
<details>
<summary>`filter_comment_form_defaults`</summary>

```php
static public filter_comment_form_defaults( $default_args )
```

Filter comment form args to an element with [text] AMP binding wrap the title reply.


</details>
<details>
<summary>`filter_comment_reply_link`</summary>

```php
static public filter_comment_reply_link( $link, $args, $comment )
```

Modify the comment reply link for AMP.


</details>
<details>
<summary>`filter_cancel_comment_reply_link`</summary>

```php
static public filter_cancel_comment_reply_link( $formatted_link, $link, $text )
```

Filters the cancel comment reply link HTML.


</details>
<details>
<summary>`init_admin_bar`</summary>

```php
static public init_admin_bar()
```

Configure the admin bar for AMP.


</details>
<details>
<summary>`has_dependency`</summary>

```php
static protected has_dependency( \WP_Dependencies $dependencies, $current_handle, $dependency_handle )
```

Recursively determine if a given dependency depends on another.


</details>
<details>
<summary>`is_exclusively_dependent`</summary>

```php
static protected is_exclusively_dependent( \WP_Dependencies $dependencies, $dependency_handle, $dependent_handle )
```

Check if a handle is exclusively a dependency of another handle.

For example, check if dashicons is being added exclusively because it is a dependency of admin-bar, as opposed to being added because it was directly enqueued by a theme or a dependency of some other style.


</details>
<details>
<summary>`filter_admin_bar_style_loader_tag`</summary>

```php
static public filter_admin_bar_style_loader_tag( $tag, $handle )
```

Add data-ampdevmode attribute to any enqueued style that depends on the admin-bar.


</details>
<details>
<summary>`filter_customize_preview_style_loader_tag`</summary>

```php
static public filter_customize_preview_style_loader_tag( $tag, $handle )
```

Add data-ampdevmode attribute to any enqueued style that depends on the `customizer-preview` handle.


</details>
<details>
<summary>`filter_admin_bar_script_loader_tag`</summary>

```php
static public filter_admin_bar_script_loader_tag( $tag, $handle )
```

Add data-ampdevmode attribute to any enqueued script that depends on the admin-bar.


</details>
<details>
<summary>`ensure_required_markup`</summary>

```php
static public ensure_required_markup( Document $dom, $script_handles = array() )
```

Ensure the markup exists as required by AMP and elements are in the optimal loading order.

Ensure meta[charset], meta[name=viewport], and link[rel=canonical] exist, as the validating sanitizer may have removed an illegal meta[http-equiv] or meta[name=viewport]. For a singular post, core only outputs a canonical URL by default. Adds the preload links.


</details>
<details>
<summary>`dequeue_customize_preview_scripts`</summary>

```php
static public dequeue_customize_preview_scripts()
```

Dequeue Customizer assets which are not necessary outside the preview iframe.

Prevent enqueueing customize-preview styles if not in customizer preview iframe. These are only needed for when there is live editing of content, such as selective refresh.


</details>
<details>
<summary>`start_output_buffering`</summary>

```php
static public start_output_buffering()
```

Start output buffering.


</details>
<details>
<summary>`is_output_buffering`</summary>

```php
static public is_output_buffering()
```

Determine whether output buffering has started.


</details>
<details>
<summary>`finish_output_buffering`</summary>

```php
static public finish_output_buffering( $response )
```

Finish output buffering.


</details>
<details>
<summary>`filter_customize_partial_render`</summary>

```php
static public filter_customize_partial_render( $partial )
```

Filter rendered partial to convert to AMP.


</details>
<details>
<summary>`prepare_response`</summary>

```php
static public prepare_response( $response, $args = array() )
```

Process response to ensure AMP validity.


</details>
<details>
<summary>`get_optimizer`</summary>

```php
static private get_optimizer( $args )
```

Optimizer instance to use.


</details>
<details>
<summary>`get_optimizer_configuration`</summary>

```php
static private get_optimizer_configuration( $args )
```

Get the AmpProject\Optimizer configuration object to use.


</details>
<details>
<summary>`include_layout_in_wp_kses_allowed_html`</summary>

```php
static public include_layout_in_wp_kses_allowed_html( $context )
```

Adds &#039;data-amp-layout&#039; to the allowed &lt;img&gt; attributes for wp_kses().


</details>
<details>
<summary>`enqueue_assets`</summary>

```php
static public enqueue_assets()
```

Enqueue AMP assets if this is an AMP endpoint.


</details>
<details>
<summary>`setup_paired_browsing_client`</summary>

```php
static public setup_paired_browsing_client()
```

Setup pages to have the paired browsing client script so that the app can interact with it.


</details>
<details>
<summary>`get_paired_browsing_url`</summary>

```php
static public get_paired_browsing_url( $url = null )
```

Get paired browsing URL for a given URL.


</details>
<details>
<summary>`sanitize_url_for_paired_browsing`</summary>

```php
static public sanitize_url_for_paired_browsing()
```

Remove any unnecessary query vars that could hamper the paired browsing experience.


</details>
<details>
<summary>`serve_paired_browsing_experience`</summary>

```php
static public serve_paired_browsing_experience( $template )
```

Serve paired browsing experience if it is being requested.

Includes a custom template that acts as an interface to facilitate a side-by-side comparison of a non-AMP page and its AMP version to review any discrepancies.


</details>
<details>
<summary>`print_emoji_styles`</summary>

```php
static public print_emoji_styles()
```

Print the important emoji-related styles.


</details>
<details>
<summary>`amend_header_image_with_video_header`</summary>

```php
static public amend_header_image_with_video_header( $image_markup )
```

Conditionally replace the header image markup with a header video or image.

This is JS-driven in Core themes like Twenty Sixteen and Twenty Seventeen. So in order for the header video to display, this replaces the markup of the header image.


</details>
