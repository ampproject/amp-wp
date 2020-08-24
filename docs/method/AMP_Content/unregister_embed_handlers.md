## Method `AMP_Content::unregister_embed_handlers()`

```php
private function unregister_embed_handlers( $embed_handlers );
```

Unregister embed handlers.

### Arguments

* `\AMP_Base_Embed_Handler[] $embed_handlers` - Embed handlers.

### Source

:link: [includes/templates/class-amp-content.php:200](../../includes/templates/class-amp-content.php#L200-L205)

<details>
<summary>Show Code</summary>

```php
private function unregister_embed_handlers( $embed_handlers ) {
	foreach ( $embed_handlers as $embed_handler ) {
		$this->add_scripts( $embed_handler->get_scripts() ); // @todo Why add_scripts here? Shouldn't it be array_diff()?
		$embed_handler->unregister_embed();
	}
}
```

</details>
