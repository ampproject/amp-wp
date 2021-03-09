## Function `get_optimizer_configuration`

```php
function get_optimizer_configuration( $args );
```

Get the AmpProject\Optimizer configuration object to use.

### Arguments

* `array $args` - Associative array of arguments to pass into the transformation engine.

### Return value

`\AmpProject\Optimizer\Configuration` - Optimizer configuration to use.

### Source

:link: [.jono-hero-image-debug/optimize.php:28](/.jono-hero-image-debug/optimize.php#L28-L88)

<details>
<summary>Show Code</summary>

```php
function get_optimizer_configuration( $args ) {
	$transformers = Optimizer\Configuration::DEFAULT_TRANSFORMERS;

	$enable_ssr = array_key_exists( ConfigurationArgument::ENABLE_SSR, $args )
		? $args[ ConfigurationArgument::ENABLE_SSR ]
		: true;

	/**
	 * Filter whether the AMP Optimizer should use server-side rendering or not.
	 *
	 * @since 1.5.0
	 *
	 * @param bool $enable_ssr Whether the AMP Optimizer should use server-side rendering or not.
	 */
	$enable_ssr = apply_filters( 'amp_enable_ssr', $enable_ssr );

	// In debugging mode, we don't use server-side rendering, as it further obfuscates the HTML markup.
	if ( ! $enable_ssr ) {
		$transformers = array_diff(
			$transformers,
			[
				Optimizer\Transformer\AmpRuntimeCss::class,
				Optimizer\Transformer\PreloadHeroImage::class,
				Optimizer\Transformer\ServerSideRendering::class,
				Optimizer\Transformer\TransformedIdentifier::class,
			]
		);
	} else {
		array_unshift( $transformers, Transformer\DetermineHeroImages::class );
	}

	array_unshift( $transformers, Transformer\AmpSchemaOrgMetadata::class );

	/**
	 * Filter the configuration to be used for the AMP Optimizer.
	 *
	 * @since 1.5.0
	 *
	 * @param array $configuration Associative array of configuration data.
	 */
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

	$config = new Optimizer\Configuration( $configuration );
	$config->registerConfigurationClass(
		Transformer\AmpSchemaOrgMetadata::class,
		Transformer\AmpSchemaOrgMetadataConfiguration::class
	);

	return $config;
}
```

</details>
