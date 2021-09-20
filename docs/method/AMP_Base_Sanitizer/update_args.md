## Method `AMP_Base_Sanitizer::update_args()`

```php
public function update_args( $args );
```

Update args.

Merges the supplied args with the existing args.

### Arguments

* `array $args` - Args.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:150](/includes/sanitizers/class-amp-base-sanitizer.php#L150-L152)

<details>
<summary>Show Code</summary>

```php
public function update_args( $args ) {
	$this->args = array_merge( $this->args, $args );
}
```

</details>
