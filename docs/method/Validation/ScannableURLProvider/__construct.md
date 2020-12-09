## Method `ScannableURLProvider::__construct()`

```php
public function __construct( $limit_per_type = 20, $include_conditionals = array(), $include_unsupported = false );
```

Class constructor.

### Arguments

* `integer $limit_per_type` - The maximum number of URLs to validate for each type.
* `array $include_conditionals` - An allowlist of conditionals to use for validation.
* `boolean $include_unsupported` - Whether to include URLs that don&#039;t support AMP.

### Source

:link: [src/Validation/ScannableURLProvider.php:53](/src/Validation/ScannableURLProvider.php#L53-L61)

<details>
<summary>Show Code</summary>

```php
public function __construct(
	$limit_per_type = 20,
	$include_conditionals = [],
	$include_unsupported = false
) {
	$this->limit_per_type       = $limit_per_type;
	$this->include_conditionals = $include_conditionals;
	$this->include_unsupported  = $include_unsupported;
}
```

</details>
