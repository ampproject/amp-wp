## Filter `amp_frontend_show_canonical`

> :warning: This filter is deprecated: Remove amp_add_amphtml_link() call on wp_head action instead.

```php
apply_filters( 'amp_frontend_show_canonical' );
```

Filters whether to show the amphtml link on the frontend.

This is deprecated since the name was wrong and the use case is not clear. To remove this from being printed, instead of using the filter you can rather do:
     add_action( &#039;template_redirect&#039;, static function () {         remove_action( &#039;wp_head&#039;, &#039;amp_add_amphtml_link&#039; );     } );

### Source

:link: [includes/amp-helper-functions.php:669](/includes/amp-helper-functions.php#L669-L682)

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
