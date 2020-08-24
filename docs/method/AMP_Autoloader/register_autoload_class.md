## Method `AMP_Autoloader::register_autoload_class()`

> :warning: This function is deprecated: Autoloading works via Composer. Extensions need to use their own mechanism.

```php
static public function register_autoload_class( $class_name, $filepath );
```

Allows an extensions plugin to register a class and its file for autoloading

phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

### Arguments

* `string $class_name` - Full classname (include namespace if applicable).
* `string $filepath` - Absolute filepath to class file, including .php extension.

### Source

:link: [includes/class-amp-autoloader.php:46](../../includes/class-amp-autoloader.php#L46-L48)

<details>
<summary>Show Code</summary>

```php
public static function register_autoload_class( $class_name, $filepath ) {
	_deprecated_function( 'AMP_Autoloader::register_autoload_class', '1.5', 'Use Composer or custom autoloader in extensions.' );
}
```

</details>
