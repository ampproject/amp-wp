## Function `amp_get_content_embed_handlers`

```php
function amp_get_content_embed_handlers( $post = null );
```

Get content embed handlers.

### Arguments

* `\WP_Post $post` - Post that the content belongs to. Deprecated when theme supports AMP, as embeds may apply                      to non-post data (e.g. Text widget).

### Return value

`array` - Embed handlers.

### Source

:link: [includes/amp-helper-functions.php:1330](../../includes/amp-helper-functions.php#L1330-L1380)

<details>
<summary>Show Code</summary>

```php
function amp_get_content_embed_handlers( $post = null ) {
	if ( ! amp_is_legacy() && $post ) {
		_deprecated_argument(
			__FUNCTION__,
			'0.7',
			sprintf(
				/* translators: %s: $post */
				esc_html__( 'The %s argument is deprecated when theme supports AMP.', 'amp' ),
				'$post'
			)
		);
		$post = null;
	}

	/**
	 * Filters the content embed handlers.
	 *
	 * @since 0.2
	 * @since 0.7 Deprecated $post parameter.
	 *
	 * @param array   $handlers Handlers.
	 * @param WP_Post $post     Post. Deprecated. It will be null when `amp_is_canonical()`.
	 */
	return apply_filters(
		'amp_content_embed_handlers',
		[
			'AMP_Core_Block_Handler'         => [],
			'AMP_Twitter_Embed_Handler'      => [],
			'AMP_YouTube_Embed_Handler'      => [],
			'AMP_Crowdsignal_Embed_Handler'  => [],
			'AMP_DailyMotion_Embed_Handler'  => [],
			'AMP_Vimeo_Embed_Handler'        => [],
			'AMP_SoundCloud_Embed_Handler'   => [],
			'AMP_Instagram_Embed_Handler'    => [],
			'AMP_Issuu_Embed_Handler'        => [],
			'AMP_Meetup_Embed_Handler'       => [],
			'AMP_Facebook_Embed_Handler'     => [],
			'AMP_Pinterest_Embed_Handler'    => [],
			'AMP_Playlist_Embed_Handler'     => [],
			'AMP_Reddit_Embed_Handler'       => [],
			'AMP_TikTok_Embed_Handler'       => [],
			'AMP_Tumblr_Embed_Handler'       => [],
			'AMP_Gallery_Embed_Handler'      => [],
			'AMP_Gfycat_Embed_Handler'       => [],
			'AMP_Imgur_Embed_Handler'        => [],
			'AMP_Scribd_Embed_Handler'       => [],
			'AMP_WordPress_TV_Embed_Handler' => [],
		],
		$post
	);
}
```

</details>
