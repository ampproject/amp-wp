## Method `AMP_WordPress_Embed_Handler::unregister_embed()`

```php
public function unregister_embed();
```

Unregister embed.

### Source

:link: [includes/embeds/class-amp-wordpress-embed-handler.php:52](/includes/embeds/class-amp-wordpress-embed-handler.php#L52-L54)

<details>
<summary>Show Code</summary>

```php
public function unregister_embed() {
	add_action( 'wp_head', 'wp_oembed_add_host_js' );
}
```

</details>
