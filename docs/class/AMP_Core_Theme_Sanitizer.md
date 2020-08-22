## Class `AMP_Core_Theme_Sanitizer`

Class AMP_Core_Theme_Sanitizer

Fixes up common issues in core themes and others.

### Methods
<details>
<summary>`get_theme_features_config`</summary>

```php
static protected get_theme_features_config( $theme_slug )
```

Retrieve the config for features needed by a theme.


</details>
<details>
<summary>`get_supported_themes`</summary>

```php
static public get_supported_themes()
```

Get list of supported core themes.


</details>
<details>
<summary>`get_acceptable_errors`</summary>

```php
static public get_acceptable_errors()
```

Get the acceptable validation errors.


</details>
<details>
<summary>`extend_theme_support`</summary>

```php
static public extend_theme_support()
```

Adds extra theme support arguments on the fly.

This method is neither a buffering hook nor a sanitization callback and is called manually by {@see AMP_Theme_Support}. Typically themes will add theme support directly and don&#039;t need such a method. In this case, it is a workaround for adding theme support on behalf of external themes.


</details>
<details>
<summary>`get_theme_support_args`</summary>

```php
static protected get_theme_support_args( $theme )
```

Returns extra arguments to pass to `add_theme_support()`.


</details>
<details>
<summary>`get_theme_config`</summary>

```php
static protected get_theme_config( $theme )
```

Get theme config.


</details>
<details>
<summary>`get_theme_features`</summary>

```php
static protected get_theme_features( $args, $static = false )
```

Find theme features for core theme.


</details>
<details>
<summary>`add_buffering_hooks`</summary>

```php
static public add_buffering_hooks( $args = array() )
```

Add filters to manipulate output during output buffering before the DOM is constructed.


</details>
<details>
<summary>`set_twentyseventeen_quotes_icon`</summary>

```php
static public set_twentyseventeen_quotes_icon()
```

Add filter to output the quote icons in front of the article content.

This is only used in Twenty Seventeen.


</details>
<details>
<summary>`add_twentyseventeen_attachment_image_attributes`</summary>

```php
static public add_twentyseventeen_attachment_image_attributes()
```

Add filter to adjust the attachment image attributes to ensure attachment pages have a consistent &lt;amp-img&gt; rendering.

This is only used in Twenty Seventeen.


</details>
<details>
<summary>`sanitize`</summary>

```php
public sanitize()
```

Fix up core themes to do things in the AMP way.


</details>
<details>
<summary>`prevent_sanitize_in_customizer_preview`</summary>

```php
public prevent_sanitize_in_customizer_preview( $xpaths = array() )
```

Adds the data-ampdevmode attribute to the set of specified elements to prevent further sanitization. This is necessary as certain features in the Customizer require these elements to be present in their unaltered state.


</details>
<details>
<summary>`dequeue_scripts`</summary>

```php
static public dequeue_scripts( $handles = array() )
```

Dequeue scripts.


</details>
<details>
<summary>`remove_actions`</summary>

```php
static public remove_actions( $actions = array() )
```

Remove actions.


</details>
<details>
<summary>`add_smooth_scrolling`</summary>

```php
public add_smooth_scrolling( $link_xpaths )
```

Add smooth scrolling from link to target element.


</details>
<details>
<summary>`force_svg_support`</summary>

```php
public force_svg_support()
```

Force SVG support, replacing no-svg class name with svg class name.


</details>
<details>
<summary>`force_fixed_background_support`</summary>

```php
public force_fixed_background_support()
```

Force support for fixed background-attachment.


</details>
<details>
<summary>`add_has_header_video_body_class`</summary>

```php
static public add_has_header_video_body_class( $args = array() )
```

Add body class when there is a header video.


</details>
<details>
<summary>`get_twentyseventeen_navigation_outer_height`</summary>

```php
static protected get_twentyseventeen_navigation_outer_height()
```

Get the (common) navigation outer height.


</details>
<details>
<summary>`add_twentytwenty_masthead_styles`</summary>

```php
static public add_twentytwenty_masthead_styles()
```

Add required styles for featured image header and image blocks in Twenty Twenty.


</details>
<details>
<summary>`add_twentytwenty_custom_logo_fix`</summary>

```php
static public add_twentytwenty_custom_logo_fix()
```

Fix display of Custom Logo in Twenty Twenty.

This is required because width:auto on the site-logo amp-img does not preserve the proportional width in the same way as the same styles applied to an img.


</details>
<details>
<summary>`add_img_display_block_fix`</summary>

```php
static public add_img_display_block_fix()
```

Add style rule with a selector of higher specificity than just `img` to make `amp-img` have `display:block` rather than `display:inline-block`.

This is needed to override the AMP core stylesheet which has a more specific selector `.i-amphtml-layout-intrinsic` which is given a `display: inline-block`; this display value prevents margins from collapsing with surrounding block elements, resulting in larger margins in AMP than expected.


</details>
<details>
<summary>`add_twentynineteen_masthead_styles`</summary>

```php
static public add_twentynineteen_masthead_styles()
```

Add required styles for featured image header in Twenty Nineteen.

The following is necessary because the styles in the theme apply to the featured img, and the CSS parser will then convert the selectors to amp-img. Nevertheless, object-fit does not apply on amp-img and it needs to apply on an actual img.


</details>
<details>
<summary>`add_twentyseventeen_masthead_styles`</summary>

```php
static public add_twentyseventeen_masthead_styles()
```

Add required styles for video and image headers.

This is currently used exclusively for Twenty Seventeen.


</details>
<details>
<summary>`add_twentyseventeen_image_styles`</summary>

```php
static public add_twentyseventeen_image_styles()
```

Override the featured image header styling in style.css.

Used only for Twenty Seventeen.


</details>
<details>
<summary>`add_twentyseventeen_sticky_nav_menu`</summary>

```php
public add_twentyseventeen_sticky_nav_menu()
```

Add sticky nav menu to Twenty Seventeen.

This is implemented by cloning the navigation-top element, giving it a fixed position outside of the viewport, and then showing it at the top of the window as soon as the original nav begins to get scrolled out of view. In order to improve accessibility, the cloned nav gets aria-hidden=true and all of the links get tabindex=-1 to prevent the keyboard from focusing on elements off the screen; it is not necessary to focus on the elements in the fixed nav menu because as soon as the original nav menu is focused then the window is scrolled to the top anyway.


</details>
<details>
<summary>`add_nav_menu_styles`</summary>

```php
static public add_nav_menu_styles( $args = array() )
```

Add styles for the nav menu specifically to deal with AMP running in a no-js context.


</details>
<details>
<summary>`adjust_twentynineteen_images`</summary>

```php
static public adjust_twentynineteen_images()
```

Adjust images in twentynineteen.


</details>
<details>
<summary>`add_twentyfourteen_masthead_styles`</summary>

```php
static public add_twentyfourteen_masthead_styles()
```

Add styles for Twenty Fourteen masthead.


</details>
<details>
<summary>`add_twentyfourteen_slider_carousel`</summary>

```php
public add_twentyfourteen_slider_carousel()
```

Add amp-carousel for slider in Twenty Fourteen.


</details>
<details>
<summary>`add_twentyfourteen_search`</summary>

```php
public add_twentyfourteen_search()
```

Use AMP-based solutions for toggling search bar in Twenty Fourteen.


</details>
<details>
<summary>`wrap_modal_in_lightbox`</summary>

```php
public wrap_modal_in_lightbox( $args = array() )
```

Wrap a modal node tree in an &lt;amp-lightbox&gt; element.


</details>
<details>
<summary>`add_twentytwenty_modals`</summary>

```php
public add_twentytwenty_modals()
```

Add generic modal interactivity compat for the Twenty Twenty theme.

Modals implemented in JS will be transformed into &lt;amp-lightbox&gt; equivalents, with the tap actions being attached to their associated toggles.


</details>
<details>
<summary>`add_twentytwenty_toggles`</summary>

```php
public add_twentytwenty_toggles()
```

Add generic toggle interactivity compat for the Twentytwenty theme.

Toggles implemented in JS will be transformed into &lt;amp-bind&gt; equivalents, with &lt;amp-state&gt; components storing the CSS classes to set.


</details>
<details>
<summary>`get_closest_submenu`</summary>

```php
protected get_closest_submenu( \DOMElement $element )
```

Get the closest sub-menu within a menu item.


</details>
<details>
<summary>`add_twentytwenty_current_page_awareness`</summary>

```php
public add_twentytwenty_current_page_awareness()
```

Automatically open the submenus related to the current page in the menu modal.


</details>
<details>
<summary>`xpath_from_css_selector`</summary>

```php
protected xpath_from_css_selector( $css_selector )
```

Provides a &quot;best guess&quot; as to what XPath would mirror a given CSS selector.

This is a very simplistic conversion and will only work for very basic CSS selectors.


</details>
<details>
<summary>`guess_modal_role`</summary>

```php
protected guess_modal_role( \DOMElement $modal )
```

Try to guess the role of a modal based on its classes.


</details>
