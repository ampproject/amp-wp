## Method `AMP_Base_Sanitizer::get_scripts()`

```php
public function get_scripts();
```

Return array of values that would be valid as an HTML `script` element.

Array keys are AMP element names and array values are their respective Javascript URLs from https://cdn.ampproject.org

### Return value

`string[]` - Returns component name as array key and JavaScript URL as array value,                  respectively. Will return an empty array if sanitization has yet to be run                  or if it did not find any HTML elements to convert to AMP equivalents.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:181](/includes/sanitizers/class-amp-base-sanitizer.php#L181-L183)

<details>
<summary>Show Code</summary>

```php
public function get_scripts() {
	return [];
}
```

</details>
