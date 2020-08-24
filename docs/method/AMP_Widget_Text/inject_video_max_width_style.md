## Method `AMP_Widget_Text::inject_video_max_width_style()`

```php
public function inject_video_max_width_style( $matches );
```

Overrides the parent callback that strips width and height attributes.

### Arguments

* `array $matches` - The matches returned from preg_replace_callback().

### Source

:link: [includes/widgets/class-amp-widget-text.php:28](../../includes/widgets/class-amp-widget-text.php#L28-L33)

<details>
<summary>Show Code</summary>

```php
public function inject_video_max_width_style( $matches ) {
	if ( amp_is_request() ) {
		return $matches[0];
	}
	return parent::inject_video_max_width_style( $matches );
}
```

</details>
