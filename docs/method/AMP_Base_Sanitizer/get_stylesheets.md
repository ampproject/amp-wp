## Method `AMP_Base_Sanitizer::get_stylesheets()`

```php
public function get_stylesheets();
```

Get stylesheets.

### Return value

`array` - Values are the CSS stylesheets. Keys are MD5 hashes of the stylesheets.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:205](/includes/sanitizers/class-amp-base-sanitizer.php#L205-L215)

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
