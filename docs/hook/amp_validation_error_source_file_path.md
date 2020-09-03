## Hook `amp_validation_error_source_file_path`


Filters the file path to be opened in an external editor for a given AMP validation error source.

This is useful to map the file path from inside of a Docker container or VM to the host machine.

### Source

:link: [includes/validation/class-amp-validation-error-taxonomy.php:2316](../../includes/validation/class-amp-validation-error-taxonomy.php#L2316)

<details>
<summary>Show Code</summary>

```php
$file_path = apply_filters( 'amp_validation_error_source_file_path', $file_path, $source );
```

</details>
