## Class `AMP_Gallery_Embed_Handler`

Class AMP_Gallery_Embed_Handler

### Methods
* `register_embed`

<details>

```php
public register_embed()
```

Register embed.


</details>
* `generate_gallery_markup`

<details>

```php
public generate_gallery_markup( $html, $attrs )
```

Override the output of gallery_shortcode().


</details>
* `filter_post_gallery_markup`

<details>

```php
protected filter_post_gallery_markup( $html, $attrs )
```

Filter the output of gallery_shortcode().


</details>
* `unregister_embed`

<details>

```php
public unregister_embed()
```

Unregister embed.


</details>
* `sanitize_raw_embeds`

<details>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitizes gallery raw embeds to become an amp-carousel and/or amp-image-lightbox, depending on configuration options.


</details>
* `get_caption_element`

<details>

```php
protected get_caption_element( \DOMElement $img_element )
```

Get the caption element for the specified image element.


</details>
* `get_parent_container_for_image`

<details>

```php
protected get_parent_container_for_image( \DOMElement $image_element )
```

Get the parent container for the specified image element.


</details>
* `print_styles`

<details>

```php
public print_styles()
```

Prints the Gallery block styling.

It would be better to print this in AMP_Gallery_Block_Sanitizer, but by the time that runs, it&#039;s too late. This rule is copied exactly from block-library/style.css, but the selector here has amp-img &gt;. The image sanitizer normally converts the &lt;img&gt; from that original stylesheet &lt;amp-img&gt;, but that doesn&#039;t have the same effect as applying it to the &lt;img&gt;.


</details>
