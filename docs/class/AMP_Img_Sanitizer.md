## Class `AMP_Img_Sanitizer`

Class AMP_Img_Sanitizer

Converts &lt;img&gt; tags to &lt;amp-img&gt; or &lt;amp-anim&gt;

### Methods
<details>
<summary>`get_selector_conversion_mapping`</summary>

```php
public get_selector_conversion_mapping()
```

Get mapping of HTML selectors to the AMP component selectors which they may be converted into.


</details>
<details>
<summary>`sanitize`</summary>

```php
public sanitize()
```

Sanitize the &lt;img&gt; elements from the HTML contained in this instance&#039;s Dom\Document.


</details>
<details>
<summary>`filter_attributes`</summary>

```php
private filter_attributes( $attributes )
```

&quot;Filter&quot; HTML attributes for &lt;amp-anim&gt; elements.


</details>
<details>
<summary>`determine_dimensions`</summary>

```php
private determine_dimensions( $need_dimensions )
```

Determine width and height attribute values for images without them.

Attempt to determine actual dimensions, otherwise set reasonable defaults.


</details>
<details>
<summary>`adjust_and_replace_nodes_in_array_map`</summary>

```php
private adjust_and_replace_nodes_in_array_map( $node_lists )
```

Now that all images have width and height attributes, make final tweaks and replace original image nodes


</details>
<details>
<summary>`adjust_and_replace_node`</summary>

```php
private adjust_and_replace_node( $node )
```

Make final modifications to DOMNode


</details>
<details>
<summary>`maybe_add_lightbox_attributes`</summary>

```php
private maybe_add_lightbox_attributes( $attributes, $node )
```

Set lightbox attributes.


</details>
<details>
<summary>`does_node_have_block_class`</summary>

```php
private does_node_have_block_class( $node )
```

Gets whether a node has the class &#039;wp-block-image&#039;, meaning it is a wrapper for an Image block.


</details>
<details>
<summary>`is_gif_url`</summary>

```php
private is_gif_url( $url )
```

Determines if a URL is considered a GIF URL


</details>
