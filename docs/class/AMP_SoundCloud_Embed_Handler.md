## Class `AMP_SoundCloud_Embed_Handler`

Class AMP_SoundCloud_Embed_Handler

### Methods
<details>
<summary>`register_embed`</summary>

```php
public register_embed()
```

Register embed.


</details>
<details>
<summary>`unregister_embed`</summary>

```php
public unregister_embed()
```

Unregister embed.


</details>
<details>
<summary>`oembed`</summary>

```php
public oembed( $matches, $attr, $url )
```

Render oEmbed.


</details>
<details>
<summary>`filter_embed_oembed_html`</summary>

```php
public filter_embed_oembed_html( $cache, $url )
```

Filter oEmbed HTML for SoundCloud to convert to AMP.


</details>
<details>
<summary>`parse_amp_component_from_iframe`</summary>

```php
private parse_amp_component_from_iframe( $html, $url = null )
```

Parse AMP component from iframe.


</details>
<details>
<summary>`render`</summary>

```php
public render( $args, $url )
```

Render embed.


</details>
<details>
<summary>`render_embed_fallback`</summary>

```php
private render_embed_fallback( $url )
```

Render embed fallback.


</details>
<details>
<summary>`extract_params_from_iframe_src`</summary>

```php
private extract_params_from_iframe_src( $url )
```

Get params from Soundcloud iframe src.


</details>
