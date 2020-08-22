## Class `AMP_Scribd_Embed_Handler`

Class AMP_Scribd_Embed_Handler

### Methods
<details>
<summary>`register_embed`</summary>

```php
public register_embed()
```

Registers embed.


</details>
<details>
<summary>`unregister_embed`</summary>

```php
public unregister_embed()
```

Unregisters embed.


</details>
<details>
<summary>`filter_embed_oembed_html`</summary>

```php
public filter_embed_oembed_html( $cache, $url )
```

Filter oEmbed HTML for Scribd to be AMP compatible.


</details>
<details>
<summary>`sanitize_iframe`</summary>

```php
private sanitize_iframe( $html )
```

Retrieves iframe element from HTML string and amends or appends the correct sandbox permissions.


</details>
