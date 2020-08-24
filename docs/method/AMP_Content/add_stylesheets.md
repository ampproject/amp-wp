## Method `AMP_Content::add_stylesheets()`

```php
private function add_stylesheets( $stylesheets );
```

Add stylesheets.

### Arguments

* `array $stylesheets` - Styles.

### Source

:link: [includes/templates/class-amp-content.php:156](../../includes/templates/class-amp-content.php#L156-L158)

<details>
<summary>Show Code</summary>

```php
private function add_stylesheets( $stylesheets ) {
	$this->amp_stylesheets = array_merge( $this->amp_stylesheets, $stylesheets );
}
```

</details>
