## Method `AMP_WordPress_Embed_Handler::register_embed()`

```php
public function register_embed();
```

Register embed.

### Source

:link: [includes/embeds/class-amp-wordpress-embed-handler.php:45](/includes/embeds/class-amp-wordpress-embed-handler.php#L45-L47)

<details>
<summary>Show Code</summary>

```php
public function register_embed() {
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}
```

</details>
