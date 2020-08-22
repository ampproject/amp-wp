## Class `AMP_SoundCloud_Embed_Handler`

Class AMP_SoundCloud_Embed_Handler

### Methods
<details>
<summary><code>register_embed</code></summary>

```php
public register_embed()
```

Register embed.


</details>
<details>
<summary><code>unregister_embed</code></summary>

```php
public unregister_embed()
```

Unregister embed.


</details>
<details>
<summary><code>oembed</code></summary>

```php
public oembed( $matches, $attr, $url )
```

Render oEmbed.


</details>
<details>
<summary><code>filter_embed_oembed_html</code></summary>

```php
public filter_embed_oembed_html( $cache, $url )
```

Filter oEmbed HTML for SoundCloud to convert to AMP.


</details>
<details>
<summary><code>parse_amp_component_from_iframe</code></summary>

```php
private parse_amp_component_from_iframe( $html, $url = null )
```

Parse AMP component from iframe.


</details>
<details>
<summary><code>render</code></summary>

```php
public render( $args, $url )
```

Render embed.


</details>
<details>
<summary><code>render_embed_fallback</code></summary>

```php
private render_embed_fallback( $url )
```

Render embed fallback.


</details>
<details>
<summary><code>extract_params_from_iframe_src</code></summary>

```php
private extract_params_from_iframe_src( $url )
```

Get params from Soundcloud iframe src.


</details>
