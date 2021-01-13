## Function `amp_add_amphtml_link`

```php
function amp_add_amphtml_link();
```

Add amphtml link.

If there are known validation errors for the current URL then do not output anything.

### Source

:link: [includes/amp-helper-functions.php:771](/includes/amp-helper-functions.php#L771-L821)

<details>
<summary>Show Code</summary>

```php
function amp_add_amphtml_link() {
	if (
		amp_is_canonical()
		||
		/**
		 * Filters whether to show the amphtml link on the frontend.
		 *
		 * This is deprecated since the name was wrong and the use case is not clear. To remove this from being printed,
		 * instead of using the filter you can rather do:
		 *
		 *     add_action( 'template_redirect', static function () {
		 *         remove_action( 'wp_head', 'amp_add_amphtml_link' );
		 *     } );
		 *
		 * @since 0.2
		 * @deprecated Remove amp_add_amphtml_link() call on wp_head action instead.
		 */
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
	) {
		return;
	}

	if ( ! amp_is_available() ) {
		printf( '<!-- %s -->', esc_html__( 'There is no amphtml version available for this URL.', 'amp' ) );
		return;
	}

	if ( AMP_Theme_Support::is_paired_available() ) {
		$amp_url = amp_add_paired_endpoint( amp_get_current_url() );
	} else {
		$amp_url = amp_get_permalink( get_queried_object_id() );
	}

	if ( $amp_url ) {
		$amp_url = remove_query_arg( QueryVar::NOAMP, $amp_url );
		printf( '<link rel="amphtml" href="%s">', esc_url( $amp_url ) );
	}
}
```

</details>
