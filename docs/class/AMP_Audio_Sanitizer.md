## Class `AMP_Audio_Sanitizer`

Class AMP_Audio_Sanitizer

Converts &lt;audio&gt; tags to &lt;amp-audio&gt;

### Methods
* `get_selector_conversion_mapping`

<details>

```php
public get_selector_conversion_mapping()
```

Get mapping of HTML selectors to the AMP component selectors which they may be converted into.


</details>
* `sanitize`

<details>

```php
public sanitize()
```

Sanitize the &lt;audio&gt; elements from the HTML contained in this instance&#039;s Dom\Document.


</details>
* `filter_attributes`

<details>

```php
private filter_attributes( $attributes )
```

&quot;Filter&quot; HTML attributes for &lt;amp-audio&gt; elements.


</details>
