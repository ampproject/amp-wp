## Class `AMP_Playlist_Embed_Handler`

Class AMP_Playlist_Embed_Handler

Creates AMP-compatible markup for the WordPress &#039;playlist&#039; shortcode.

### Methods
<details>
<summary>`register_embed`</summary>

```php
public register_embed()
```

Registers the playlist shortcode.


</details>
<details>
<summary>`unregister_embed`</summary>

```php
public unregister_embed()
```

Unregisters the playlist shortcode.


</details>
<details>
<summary>`enqueue_styles`</summary>

```php
public enqueue_styles()
```

Enqueues the playlist styling.


</details>
<details>
<summary>`shortcode`</summary>

```php
public shortcode( $attr )
```

Gets AMP-compliant markup for the playlist shortcode.

Uses the JSON that wp_playlist_shortcode() produces. Gets the markup, based on the type of playlist.


</details>
<details>
<summary>`audio_playlist`</summary>

```php
public audio_playlist( $data )
```

Gets an AMP-compliant audio playlist.


</details>
<details>
<summary>`video_playlist`</summary>

```php
public video_playlist( $data )
```

Gets an AMP-compliant video playlist.

This uses similar markup to the native playlist shortcode output. So the styles from wp-mediaelement.min.css will apply to it.


</details>
<details>
<summary>`get_thumb_dimensions`</summary>

```php
public get_thumb_dimensions( $track )
```

Gets the thumbnail image dimensions, including height and width.

If the width is higher than the maximum width, reduces it to the maximum width. And it proportionally reduces the height.


</details>
<details>
<summary>`print_tracks`</summary>

```php
public print_tracks( $state_id, $tracks )
```

Outputs the playlist tracks, based on the type of playlist.

These typically appear below the player. Clicking a track triggers the player to appear with its src.


</details>
<details>
<summary>`get_data`</summary>

```php
public get_data( $attr )
```

Gets the data for the playlist.


</details>
<details>
<summary>`get_title`</summary>

```php
public get_title( $track )
```

Gets the title for the track.


</details>
