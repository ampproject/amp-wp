## Method `AMP_Content::add_scripts()`

```php
private function add_scripts( $scripts );
```

Add scripts.

### Arguments

* `array $scripts` - Scripts.

### Source

:link: [includes/templates/class-amp-content.php:146](../../includes/templates/class-amp-content.php#L146-L148)

<details>
<summary>Show Code</summary>

```php
private function add_scripts( $scripts ) {
	$this->amp_scripts = array_merge( $this->amp_scripts, $scripts );
}
```

</details>
