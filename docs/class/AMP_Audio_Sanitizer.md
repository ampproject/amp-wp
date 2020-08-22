## Class `AMP_Audio_Sanitizer`

Class AMP_Audio_Sanitizer

Converts &lt;audio&gt; tags to &lt;amp-audio&gt;

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

Sanitize the &lt;audio&gt; elements from the HTML contained in this instance&#039;s Dom\Document.


</details>
<details>
<summary><code>filter_attributes</code></summary>

```php
private filter_attributes( $attributes )
```

&quot;Filter&quot; HTML attributes for &lt;amp-audio&gt; elements.


</details>
