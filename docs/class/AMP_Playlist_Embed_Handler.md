## Class `AMP_Playlist_Embed_Handler`

Class AMP_Playlist_Embed_Handler

Creates AMP-compatible markup for the WordPress &#039;playlist&#039; shortcode.

### Methods
* `register_embed`

<details>

```php
public register_embed()
```

Registers the playlist shortcode.


</details>
* `unregister_embed`

<details>

```php
public unregister_embed()
```

Unregisters the playlist shortcode.


</details>
* `enqueue_styles`

<details>

```php
public enqueue_styles()
```

Enqueues the playlist styling.


</details>
* `shortcode`

<details>

```php
public shortcode( $attr )
```

Gets AMP-compliant markup for the playlist shortcode.

Uses the JSON that wp_playlist_shortcode() produces. Gets the markup, based on the type of playlist.


</details>
* `audio_playlist`

<details>

```php
public audio_playlist( $data )
```

Gets an AMP-compliant audio playlist.


</details>
* `video_playlist`

<details>

```php
public video_playlist( $data )
```

Gets an AMP-compliant video playlist.

This uses similar markup to the native playlist shortcode output. So the styles from wp-mediaelement.min.css will apply to it.


</details>
* `get_thumb_dimensions`

<details>

```php
public get_thumb_dimensions( $track )
```

Gets the thumbnail image dimensions, including height and width.

If the width is higher than the maximum width, reduces it to the maximum width. And it proportionally reduces the height.


</details>
* `print_tracks`

<details>

```php
public print_tracks( $state_id, $tracks )
```

Outputs the playlist tracks, based on the type of playlist.

These typically appear below the player. Clicking a track triggers the player to appear with its src.


</details>
* `get_data`

<details>

```php
public get_data( $attr )
```

Gets the data for the playlist.


</details>
* `get_title`

<details>

```php
public get_title( $track )
```

Gets the title for the track.


</details>
