## Class `AMP_YouTube_Embed_Handler`

Class AMP_YouTube_Embed_Handler

Much of this class is borrowed from Jetpack embeds.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $args = array() )
```

AMP_YouTube_Embed_Handler constructor.


</details>
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
<summary>`filter_embed_oembed_html`</summary>

```php
public filter_embed_oembed_html( $cache, $url )
```

Filter oEmbed HTML for YouTube to convert to AMP.


</details>
<details>
<summary>`parse_props`</summary>

```php
private parse_props( $html, $url, $video_id )
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
<summary>`get_video_id_from_url`</summary>

```php
private get_video_id_from_url( $url )
```

Determine the video ID from the URL.


</details>
<details>
<summary>`video_override`</summary>

```php
public video_override( $html, $attr )
```

Override the output of YouTube videos.

This overrides the value in wp_video_shortcode(). The pattern matching is copied from WP_Widget_Media_Video::render().


</details>
