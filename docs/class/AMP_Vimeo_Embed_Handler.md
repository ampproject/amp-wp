## Class `AMP_Vimeo_Embed_Handler`

Class AMP_Vimeo_Embed_Handler

Much of this class is borrowed from Jetpack embeds

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $args = array() )
```

AMP_Vimeo_Embed_Handler constructor.


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
<summary><code>oembed</code></summary>

```php
public oembed( $matches, $attr, $url )
```

Render oEmbed.


</details>
<details>
<summary><code>render</code></summary>

```php
public render( $args )
```

Render.


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

Override the output of Vimeo videos.

This overrides the value in wp_video_shortcode(). The pattern matching is copied from WP_Widget_Media_Video::render().


</details>
