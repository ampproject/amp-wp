## Function `amp_print_analytics`

```php
function amp_print_analytics( $analytics );
```

Print analytics data.

### Arguments

* `array|string $analytics` - Analytics entries, or empty string when called via wp_footer action.

### Source

:link: [includes/amp-helper-functions.php:1258](../../includes/amp-helper-functions.php#L1258-L1318)

<details>
<summary>Show Code</summary>

```php
function amp_print_analytics( $analytics ) {
	if ( '' === $analytics ) {
		$analytics = [];
	}

	$analytics_entries = amp_get_analytics( $analytics );

	/**
	 * Triggers before analytics entries are printed as amp-analytics tags.
	 *
	 * This is useful for printing additional `amp-analytics` tags to the page without having to refactor any existing
	 * markup generation logic to use the data structure mutated by the `amp_analytics_entries` filter. For such cases,
	 * this action should be used for printing `amp-analytics` tags as opposed to using the `wp_footer` and
	 * `amp_post_template_footer` actions.
	 *
	 * @since 1.3
	 * @param array $analytics_entries Analytics entries, already potentially modified by the amp_analytics_entries filter.
	 */
	do_action( 'amp_print_analytics', $analytics_entries );

	if ( empty( $analytics_entries ) ) {
		return;
	}

	// Can enter multiple configs within backend.
	foreach ( $analytics_entries as $id => $analytics_entry ) {
		if ( ! isset( $analytics_entry['attributes'], $analytics_entry['config_data'] ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: 1: the analytics entry ID. 2: type. 3: attributes. 4: config_data. 5: comma-separated list of the actual entry keys. */
					esc_html__( 'Analytics entry for %1$s is missing one of the following keys: `%2$s` or `%3$s` (array keys: %4$s)', 'amp' ),
					esc_html( $id ),
					'attributes',
					'config_data',
					esc_html( implode( ', ', array_keys( $analytics_entry ) ) )
				),
				'0.3.2'
			);
			continue;
		}
		$script_element = AMP_HTML_Utils::build_tag(
			'script',
			[
				'type' => 'application/json',
			],
			wp_json_encode( $analytics_entry['config_data'] )
		);

		$amp_analytics_attr = array_merge(
			compact( 'id' ),
			$analytics_entry['attributes']
		);

		if ( ! empty( $analytics_entry['type'] ) ) {
			$amp_analytics_attr['type'] = $analytics_entry['type'];
		}

		echo AMP_HTML_Utils::build_tag( 'amp-analytics', $amp_analytics_attr, $script_element ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
```

</details>
