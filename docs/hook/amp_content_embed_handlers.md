## Filter `amp_content_embed_handlers`

```php
apply_filters( 'amp_content_embed_handlers', $handlers, $post );
```

Filters the content embed handlers.

### Arguments

* `array $handlers` - Handlers.
* `\WP_Post $post` - Post. Deprecated. It will be null when `amp_is_canonical()`.

### Source

:link: [includes/amp-helper-functions.php:1322](/includes/amp-helper-functions.php#L1322-L1348)

<details>
<summary>Show Code</summary>

```php
return apply_filters(
	'amp_content_embed_handlers',
	[
		AMP_Core_Block_Handler::class         => [],
		AMP_Twitter_Embed_Handler::class      => [],
		AMP_YouTube_Embed_Handler::class      => [],
		AMP_Crowdsignal_Embed_Handler::class  => [],
		AMP_DailyMotion_Embed_Handler::class  => [],
		AMP_Vimeo_Embed_Handler::class        => [],
		AMP_SoundCloud_Embed_Handler::class   => [],
		AMP_Instagram_Embed_Handler::class    => [],
		AMP_Issuu_Embed_Handler::class        => [],
		AMP_Meetup_Embed_Handler::class       => [],
		AMP_Facebook_Embed_Handler::class     => [],
		AMP_Pinterest_Embed_Handler::class    => [],
		AMP_Playlist_Embed_Handler::class     => [],
		AMP_Reddit_Embed_Handler::class       => [],
		AMP_TikTok_Embed_Handler::class       => [],
		AMP_Tumblr_Embed_Handler::class       => [],
		AMP_Gallery_Embed_Handler::class      => [],
		AMP_Gfycat_Embed_Handler::class       => [],
		AMP_Imgur_Embed_Handler::class        => [],
		AMP_Scribd_Embed_Handler::class       => [],
		AMP_WordPress_TV_Embed_Handler::class => [],
	],
	$post
);
```

</details>
