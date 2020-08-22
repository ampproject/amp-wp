## Class `AMP_Playbuzz_Sanitizer`

Class AMP_Playbuzz_Sanitizer

Converts Playbuzz embed to &lt;amp-playbuzz&gt;

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

Sanitize the Playbuzz elements from the HTML contained in this instance&#039;s Dom\Document.


</details>
* `filter_attributes`

<details>

```php
private filter_attributes( $attributes )
```

&quot;Filter&quot; HTML attributes for &lt;amp-audio&gt; elements.


</details>
