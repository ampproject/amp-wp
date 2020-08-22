## Class `AMP_Iframe_Sanitizer`

Class AMP_Iframe_Sanitizer

Converts &lt;iframe&gt; tags to &lt;amp-iframe&gt;

### Methods
<details>
<summary><code>get_selector_conversion_mapping</code></summary>

```php
public get_selector_conversion_mapping()
```

Get mapping of HTML selectors to the AMP component selectors which they may be converted into.


</details>
<details>
<summary><code>sanitize</code></summary>

```php
public sanitize()
```

Sanitize the &lt;iframe&gt; elements from the HTML contained in this instance&#039;s Dom\Document.


</details>
<details>
<summary><code>normalize_attributes</code></summary>

```php
private normalize_attributes( $attributes )
```

Normalize HTML attributes for &lt;amp-iframe&gt; elements.


</details>
<details>
<summary><code>get_origin_from_url</code></summary>

```php
private get_origin_from_url( $url )
```

Obtain the origin part of a given URL (scheme, host, port).


</details>
<details>
<summary><code>build_placeholder</code></summary>

```php
private build_placeholder()
```

Builds a DOMElement to use as a placeholder for an &lt;iframe&gt;.

Important: The element returned must not be block-level (e.g. div) as the PHP DOM parser will move it out from inside any containing paragraph. So this is why a span is used.


</details>
<details>
<summary><code>sanitize_boolean_digit</code></summary>

```php
private sanitize_boolean_digit( $value )
```

Sanitizes a boolean character (or string) into a &#039;0&#039; or &#039;1&#039; character.


</details>
