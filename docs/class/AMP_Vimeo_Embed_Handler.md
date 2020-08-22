## Class `AMP_Vimeo_Embed_Handler`

Class AMP_Vimeo_Embed_Handler

Much of this class is borrowed from Jetpack embeds

### Methods
* `__construct`

<details>

```php
public __construct( $args = array() )
```

AMP_Vimeo_Embed_Handler constructor.


</details>
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
* `render`

<details>

```php
public render( $args )
```

Render.


</details>
* `get_video_id_from_url`

<details>

```php
private get_video_id_from_url( $url )
```

Determine the video ID from the URL.


</details>
* `video_override`

<details>

```php
public video_override( $html, $attr )
```

Override the output of Vimeo videos.

This overrides the value in wp_video_shortcode(). The pattern matching is copied from WP_Widget_Media_Video::render().


</details>
