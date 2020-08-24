## Hook `widget_archives_dropdown_args`

### Source

:link: [includes/widgets/class-amp-widget-archives.php:63](../../includes/widgets/class-amp-widget-archives.php#L63-L70)

<details>
<summary>Show Code</summary>

```php
$dropdown_args = apply_filters(
	'widget_archives_dropdown_args',
	[
		'type'            => 'monthly',
		'format'          => 'option',
		'show_post_count' => $c,
	]
);
```

</details>
