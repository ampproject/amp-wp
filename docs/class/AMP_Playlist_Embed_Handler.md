## Class `AMP_Playlist_Embed_Handler`

Class AMP_Playlist_Embed_Handler

Creates AMP-compatible markup for the WordPress &#039;playlist&#039; shortcode.

### Methods
<details>
<summary><code>register_embed</code></summary>

```php
public register_embed()
```

Registers the playlist shortcode.


</details>
<details>
<summary><code>unregister_embed</code></summary>

```php
public unregister_embed()
```

Unregisters the playlist shortcode.


</details>
<details>
<summary><code>enqueue_styles</code></summary>

```php
public enqueue_styles()
```

Enqueues the playlist styling.


</details>
<details>
<summary><code>shortcode</code></summary>

```php
public shortcode( $attr )
```

Gets AMP-compliant markup for the playlist shortcode.

Uses the JSON that wp_playlist_shortcode() produces. Gets the markup, based on the type of playlist.


</details>
<details>
<summary><code>audio_playlist</code></summary>

```php
public audio_playlist( $data )
```

Gets an AMP-compliant audio playlist.


</details>
<details>
<summary><code>video_playlist</code></summary>

```php
public video_playlist( $data )
```

Gets an AMP-compliant video playlist.

This uses similar markup to the native playlist shortcode output. So the styles from wp-mediaelement.min.css will apply to it.


</details>
<details>
<summary><code>get_thumb_dimensions</code></summary>

```php
public get_thumb_dimensions( $track )
```

Gets the thumbnail image dimensions, including height and width.

If the width is higher than the maximum width, reduces it to the maximum width. And it proportionally reduces the height.


</details>
<details>
<summary><code>print_tracks</code></summary>

```php
public print_tracks( $state_id, $tracks )
```

Outputs the playlist tracks, based on the type of playlist.

These typically appear below the player. Clicking a track triggers the player to appear with its src.


</details>
<details>
<summary><code>get_data</code></summary>

```php
public get_data( $attr )
```

Gets the data for the playlist.


</details>
<details>
<summary><code>get_title</code></summary>

```php
public get_title( $track )
```

Gets the title for the track.


</details>
