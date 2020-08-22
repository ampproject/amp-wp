## Class `AMP_Gallery_Embed_Handler`

Class AMP_Gallery_Embed_Handler

### Methods
<details>
<summary>`register_embed`</summary>

```php
public register_embed()
```

Register embed.


</details>
<details>
<summary>`generate_gallery_markup`</summary>

```php
public generate_gallery_markup( $html, $attrs )
```

Override the output of gallery_shortcode().


</details>
<details>
<summary>`filter_post_gallery_markup`</summary>

```php
protected filter_post_gallery_markup( $html, $attrs )
```

Filter the output of gallery_shortcode().


</details>
<details>
<summary>`unregister_embed`</summary>

```php
public unregister_embed()
```

Unregister embed.


</details>
<details>
<summary>`sanitize_raw_embeds`</summary>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitizes gallery raw embeds to become an amp-carousel and/or amp-image-lightbox, depending on configuration options.


</details>
<details>
<summary>`get_caption_element`</summary>

```php
protected get_caption_element( \DOMElement $img_element )
```

Get the caption element for the specified image element.


</details>
<details>
<summary>`get_parent_container_for_image`</summary>

```php
protected get_parent_container_for_image( \DOMElement $image_element )
```

Get the parent container for the specified image element.


</details>
<details>
<summary>`print_styles`</summary>

```php
public print_styles()
```

Prints the Gallery block styling.

It would be better to print this in AMP_Gallery_Block_Sanitizer, but by the time that runs, it&#039;s too late. This rule is copied exactly from block-library/style.css, but the selector here has amp-img &gt;. The image sanitizer normally converts the &lt;img&gt; from that original stylesheet &lt;amp-img&gt;, but that doesn&#039;t have the same effect as applying it to the &lt;img&gt;.


</details>
