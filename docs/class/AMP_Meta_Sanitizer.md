## Class `AMP_Meta_Sanitizer`

Class AMP_Meta_Sanitizer.

Sanitizes meta tags found in the header.

### Methods
<details>
<summary><code>get_body_meta_tag_name_attribute_deny_pattern</code></summary>

```php
private get_body_meta_tag_name_attribute_deny_pattern()
```

Get tag spec for meta tags which are allowed in the body.


</details>
<details>
<summary><code>sanitize</code></summary>

```php
public sanitize()
```

Sanitize.


</details>
<details>
<summary><code>ensure_charset_is_present</code></summary>

```php
protected ensure_charset_is_present()
```

Always ensure that we have an HTML 5 charset meta tag.

The charset is set to utf-8, which is what AMP requires.


</details>
<details>
<summary><code>ensure_viewport_is_present</code></summary>

```php
protected ensure_viewport_is_present()
```

Always ensure we have a viewport tag.

The viewport defaults to &#039;width=device-width&#039;, which is the bare minimum that AMP requires. If there are `@viewport` style rules, these will have been moved into the content attribute of their own meta[name=viewport] tags by the style sanitizer. When there are multiple such meta tags, this method extracts the viewport properties of each and then merges them into a single meta[name=viewport] tag. Any invalid properties will get removed by the tag-and-attribute sanitizer.


</details>
<details>
<summary><code>ensure_boilerplate_is_present</code></summary>

```php
protected ensure_boilerplate_is_present()
```

Always ensure we have a style[amp-boilerplate] and a noscript&gt;style[amp-boilerplate].

The AMP boilerplate styles should appear at the end of the head: &quot;Finally, specify the AMP boilerplate code. By putting the boilerplate code last, it prevents custom styles from accidentally overriding the boilerplate css rules.&quot;


</details>
<details>
<summary><code>process_amp_script_meta_tags</code></summary>

```php
protected process_amp_script_meta_tags()
```

Parse and concatenate &lt;amp-script&gt; source meta tags.


</details>
<details>
<summary><code>create_charset_element</code></summary>

```php
protected create_charset_element()
```

Create a new meta tag for the charset value.


</details>
<details>
<summary><code>create_viewport_element</code></summary>

```php
protected create_viewport_element( $viewport )
```

Create a new meta tag for the viewport setting.


</details>
<details>
<summary><code>re_add_meta_tags_in_optimized_order</code></summary>

```php
protected re_add_meta_tags_in_optimized_order()
```

Re-add the meta tags to the &lt;head&gt; node in the optimized order.

The order is defined by the array entries in $this-&gt;meta_tags.
 The optimal loading order for AMP pages is documented at: https://amp.dev/documentation/guides-and-tutorials/optimize-and-measure/optimize_amp/#optimize-the-amp-runtime-loading
 &quot;1. The first tag should be the meta charset tag, followed by any remaining meta tags.&quot;


</details>
