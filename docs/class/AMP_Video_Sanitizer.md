## Class `AMP_Video_Sanitizer`

Class AMP_Video_Sanitizer

Converts &lt;video&gt; tags to &lt;amp-video&gt;

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

Sanitize the &lt;video&gt; elements from the HTML contained in this instance&#039;s Dom\Document.


</details>
<details>
<summary>`filter_video_dimensions`</summary>

```php
protected filter_video_dimensions( $new_attributes, $src )
```

Filter video dimensions, try to get width and height from original file if missing.

The video block will automatically have the width/height supplied for attachments.


</details>
<details>
<summary>`filter_attributes`</summary>

```php
private filter_attributes( $attributes )
```

&quot;Filter&quot; HTML attributes for &lt;amp-audio&gt; elements.


</details>
