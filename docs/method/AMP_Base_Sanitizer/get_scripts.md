## Method `AMP_Base_Sanitizer::get_scripts()`

```php
public function get_scripts();
```

Return array of values that would be valid as an HTML `script` element.

Array keys are AMP element names and array values are their respective Javascript URLs from https://cdn.ampproject.org

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:170](../../includes/sanitizers/class-amp-base-sanitizer.php#L170-L172)

<details>
<summary>Show Code</summary>

```php
public function get_scripts() {
	return [];
}
```

</details>
