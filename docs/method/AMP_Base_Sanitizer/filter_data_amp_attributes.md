## Method `AMP_Base_Sanitizer::filter_data_amp_attributes()`

```php
public function filter_data_amp_attributes( $attributes, $amp_data );
```

Set AMP attributes.

### Arguments

* `array $attributes` - Array of attributes.
* `array $amp_data` - Array of AMP attributes.

### Return value

`array` - Updated attributes.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:697](/includes/sanitizers/class-amp-base-sanitizer.php#L697-L705)

<details>
<summary>Show Code</summary>

```php
public function filter_data_amp_attributes( $attributes, $amp_data ) {
	if ( isset( $amp_data['layout'] ) ) {
		$attributes['data-amp-layout'] = $amp_data['layout'];
	}
	if ( isset( $amp_data['noloading'] ) ) {
		$attributes['data-amp-noloading'] = '';
	}
	return $attributes;
}
```

</details>
