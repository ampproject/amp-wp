## Method `AMP_Content::register_embed_handlers()`

```php
private function register_embed_handlers( $embed_handler_classes );
```

Register embed handlers.

### Arguments

* `array[] $embed_handler_classes` - Embed handlers, with keys as class names and values as arguments.

### Source

:link: [includes/templates/class-amp-content.php:166](../../includes/templates/class-amp-content.php#L166-L193)

<details>
<summary>Show Code</summary>

```php
private function register_embed_handlers( $embed_handler_classes ) {
	$embed_handlers = [];
	foreach ( $embed_handler_classes as $embed_handler_class => $args ) {
		$embed_handler = new $embed_handler_class( array_merge( $this->args, $args ) );
		if ( ! $embed_handler instanceof AMP_Base_Embed_Handler ) {
			_doing_it_wrong(
				__METHOD__,
				esc_html(
					sprintf(
						/* translators: 1: embed handler. 2: AMP_Embed_Handler */
						__( 'Embed Handler (%1$s) must extend `%2$s`', 'amp' ),
						esc_html( $embed_handler_class ),
						'AMP_Embed_Handler'
					)
				),
				'0.1'
			);
			continue;
		}
		$embed_handler->register_embed();
		$embed_handlers[] = $embed_handler;
	}
	return $embed_handlers;
}
```

</details>
