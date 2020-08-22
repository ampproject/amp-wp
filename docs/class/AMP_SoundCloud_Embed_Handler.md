## Class `AMP_SoundCloud_Embed_Handler`

Class AMP_SoundCloud_Embed_Handler

### Methods
* `register_embed`

<details>

```php
public register_embed()
```

Register embed.


</details>
* `unregister_embed`

<details>

```php
public unregister_embed()
```

Unregister embed.


</details>
* `oembed`

<details>

```php
public oembed( $matches, $attr, $url )
```

Render oEmbed.


</details>
* `filter_embed_oembed_html`

<details>

```php
public filter_embed_oembed_html( $cache, $url )
```

Filter oEmbed HTML for SoundCloud to convert to AMP.


</details>
* `parse_amp_component_from_iframe`

<details>

```php
private parse_amp_component_from_iframe( $html, $url = null )
```

Parse AMP component from iframe.


</details>
* `render`

<details>

```php
public render( $args, $url )
```

Render embed.


</details>
* `render_embed_fallback`

<details>

```php
private render_embed_fallback( $url )
```

Render embed fallback.


</details>
* `extract_params_from_iframe_src`

<details>

```php
private extract_params_from_iframe_src( $url )
```

Get params from Soundcloud iframe src.


</details>
