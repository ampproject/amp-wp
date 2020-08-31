## Hook `amp_frontend_show_canonical`

> :warning: This function is deprecated: Remove amp_add_amphtml_link() call on wp_head action instead.

### Source

:link: [includes/amp-helper-functions.php:779](../../includes/amp-helper-functions.php#L779-L792)

<details>
<summary>Show Code</summary>

```php
false === apply_filters_deprecated(
	'amp_frontend_show_canonical',
	[ true ],
	'2.0',
	'',
	sprintf(
		/* translators: 1: amphtml, 2: amp_add_amphtml_link(), 3: wp_head, 4: template_redirect */
		esc_html__( 'Removal of %1$s link should be done by removing %2$s from the %3$s action at %4$s.', 'amp' ),
		'amphtml',
		__FUNCTION__ . '()',
		'wp_head',
		'template_redirect'
	)
)
```

</details>
