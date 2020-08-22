## Class `AMP_YouTube_Embed_Handler`

Class AMP_YouTube_Embed_Handler

Much of this class is borrowed from Jetpack embeds.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $args = array() )
```

AMP_YouTube_Embed_Handler constructor.


</details>
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
<summary><code>filter_embed_oembed_html</code></summary>

```php
public filter_embed_oembed_html( $cache, $url )
```

Filter oEmbed HTML for YouTube to convert to AMP.


</details>
<details>
<summary><code>parse_props</code></summary>

```php
private parse_props( $html, $url, $video_id )
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
<summary><code>get_video_id_from_url</code></summary>

```php
private get_video_id_from_url( $url )
```

Determine the video ID from the URL.


</details>
<details>
<summary><code>video_override</code></summary>

```php
public video_override( $html, $attr )
```

Override the output of YouTube videos.

This overrides the value in wp_video_shortcode(). The pattern matching is copied from WP_Widget_Media_Video::render().


</details>
