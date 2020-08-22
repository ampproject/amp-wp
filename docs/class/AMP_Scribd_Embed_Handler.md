## Class `AMP_Scribd_Embed_Handler`

Class AMP_Scribd_Embed_Handler

### Methods
<details>
<summary><code>register_embed</code></summary>

```php
public register_embed()
```

Registers embed.


</details>
<details>
<summary><code>unregister_embed</code></summary>

```php
public unregister_embed()
```

Unregisters embed.


</details>
<details>
<summary><code>filter_embed_oembed_html</code></summary>

```php
public filter_embed_oembed_html( $cache, $url )
```

Filter oEmbed HTML for Scribd to be AMP compatible.


</details>
<details>
<summary><code>sanitize_iframe</code></summary>

```php
private sanitize_iframe( $html )
```

Retrieves iframe element from HTML string and amends or appends the correct sandbox permissions.


</details>
