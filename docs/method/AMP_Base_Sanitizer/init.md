## Method `AMP_Base_Sanitizer::init()`

```php
public function init( $sanitizers );
```

Run logic before any sanitizers are run.

After the sanitizers are instantiated but before calling sanitize on each of them, this method is called with list of all the instantiated sanitizers.

### Arguments

* `\AMP_Base_Sanitizer[] $sanitizers` - Sanitizers.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:162](/includes/sanitizers/class-amp-base-sanitizer.php#L162)

<details>
<summary>Show Code</summary>

```php
public function init( $sanitizers ) {} // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
```

</details>
