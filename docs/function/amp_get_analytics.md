## Function `amp_get_analytics`

```php
function amp_get_analytics( $analytics = array() );
```

Retrieve analytics data added in backend.

### Arguments

* `array $analytics` - Analytics entries.

### Return value

`array` - Analytics.

### Source

:link: [includes/amp-helper-functions.php:1218](../../includes/amp-helper-functions.php#L1218-L1248)

<details>
<summary>Show Code</summary>

```php
function amp_get_analytics( $analytics = [] ) {
	$analytics_entries = AMP_Options_Manager::get_option( Option::ANALYTICS, [] );

	/**
	 * Add amp-analytics tags.
	 *
	 * This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting.
	 * This filter should be used to alter entries for transitional mode.
	 *
	 * @since 0.7
	 *
	 * @param array $analytics_entries An associative array of the analytics entries we want to output. Each array entry must have a unique key, and the value should be an array with the following keys: `type`, `attributes`, `config_data`. See readme for more details.
	 */
	$analytics_entries = apply_filters( 'amp_analytics_entries', $analytics_entries );

	if ( ! $analytics_entries ) {
		return $analytics;
	}

	foreach ( $analytics_entries as $entry_id => $entry ) {
		if ( ! isset( $entry['attributes'] ) ) {
			$entry['attributes'] = [];
		}
		if ( ! isset( $entry['config_data'] ) && isset( $entry['config'] ) && is_string( $entry['config'] ) ) {
			$entry['config_data'] = json_decode( $entry['config'] );
		}
		$analytics[ $entry_id ] = $entry;
	}

	return $analytics;
}
```

</details>
