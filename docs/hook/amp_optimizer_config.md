## Hook `amp_optimizer_config`

### Source

:link: [includes/class-amp-theme-support.php:2256](../../includes/class-amp-theme-support.php#L2256-L2262)

<details>
<summary>Show Code</summary>

```php
$configuration = apply_filters(
	'amp_optimizer_config',
	array_merge(
		[ Optimizer\Configuration::KEY_TRANSFORMERS => $transformers ],
		$args
	)
);
```

</details>
