## Filter `amp_optimizer_config`

```php
apply_filters( 'amp_optimizer_config', $configuration );
```

Filter the configuration to be used for the AMP Optimizer.

### Arguments

* `array $configuration` - Associative array of configuration data.

### Source

:link: [src/Optimizer/AmpWPConfiguration.php:87](/src/Optimizer/AmpWPConfiguration.php#L87-L95)

<details>
<summary>Show Code</summary>

```php
$this->configuration = apply_filters(
	'amp_optimizer_config',
	[
		self::KEY_TRANSFORMERS              => $transformers,
		Transformer\PreloadHeroImage::class => [
			Configuration\PreloadHeroImageConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE => 'data-amp-original-style',
		],
	]
);
```

</details>
