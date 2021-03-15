## Filter `amp_optimizer_config`

```php
apply_filters( 'amp_optimizer_config', $configuration );
```

Filter the configuration to be used for the AMP Optimizer.

### Arguments

* `array $configuration` - Associative array of configuration data.

### Source

:link: [includes/class-amp-theme-support.php:2133](/includes/class-amp-theme-support.php#L2133-L2144)

<details>
<summary>Show Code</summary>

```php
$configuration = apply_filters(
	'amp_optimizer_config',
	array_merge(
		[
			Optimizer\Configuration::KEY_TRANSFORMERS => $transformers,
			Optimizer\Transformer\PreloadHeroImage::class => [
				Optimizer\Configuration\PreloadHeroImageConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE => 'data-amp-original-style',
			],
		],
		$args
	)
);
```

</details>
