## Class `AMP_Widget_Text`

> :warning: This function is deprecated: As of 2.0 the AMP_Core_Block_Handler will sanitize the core widgets instead.

Class AMP_Widget_Text

### Methods

* [`inject_video_max_width_style`](../method/AMP_Widget_Text/inject_video_max_width_style.md) - Overrides the parent callback that strips width and height attributes.
### Source

:link: [includes/widgets/class-amp-widget-text.php:20](../../includes/widgets/class-amp-widget-text.php#L20-L34)

<details>
<summary>Show Code</summary>

```php
class AMP_Widget_Text extends WP_Widget_Text {
	/**
	 * Overrides the parent callback that strips width and height attributes.
	 *
	 * @param array $matches The matches returned from preg_replace_callback().
	 * @return string $html The markup, unaltered.
	 */
	public function inject_video_max_width_style( $matches ) {
		if ( amp_is_request() ) {
			return $matches[0];
		}
		return parent::inject_video_max_width_style( $matches );
	}
}
```

</details>
