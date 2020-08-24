## Method `AMP_Content::__construct()`

```php
public function __construct( $content, $embed_handler_classes, $sanitizer_classes, $args = array() );
```

AMP_Content constructor.

### Arguments

* `string $content` - Content.
* `array[] $embed_handler_classes` - Embed handlers, with keys as class names and values as arguments.
* `array[] $sanitizer_classes` - Sanitizers, with keys as class names and values as arguments.
* `array $args` - Args.

### Source

:link: [includes/templates/class-amp-content.php:74](../../includes/templates/class-amp-content.php#L74-L83)

<details>
<summary>Show Code</summary>

```php
public function __construct( $content, $embed_handler_classes, $sanitizer_classes, $args = [] ) {
	$this->content           = $content;
	$this->args              = $args;
	$this->embed_handlers    = $this->register_embed_handlers( $embed_handler_classes );
	$this->sanitizer_classes = $sanitizer_classes;
	$this->sanitizer_classes['AMP_Embed_Sanitizer']['embed_handlers'] = $this->embed_handlers;
	$this->transform();
}
```

</details>
