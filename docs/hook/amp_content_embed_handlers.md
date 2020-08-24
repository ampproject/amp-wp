## Hook `amp_content_embed_handlers`

### Source

:link: [includes/amp-helper-functions.php:1353](../../includes/amp-helper-functions.php#L1353-L1379)

<details>
<summary>Show Code</summary>

```php
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
```

</details>
