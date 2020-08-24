## Method `AMP_Base_Sanitizer::get_stylesheets()`

```php
public function get_stylesheets();
```

Get stylesheets.

### Source

[includes/sanitizers/class-amp-base-sanitizer.php:193](https://github.com/ampproject/amp-wp/blob/develop/includes/sanitizers/class-amp-base-sanitizer.php#L193-L203)

<details>
<summary>Show Code</summary>

```php
public function get_stylesheets() {
	$stylesheets = [];
	foreach ( $this->get_styles() as $selector => $properties ) {
		$stylesheet = sprintf( '%s { %s }', $selector, implode( '; ', $properties ) . ';' );
		$stylesheets[ md5( $stylesheet ) ] = $stylesheet;
	}
	return $stylesheets;
}
```

</details>
