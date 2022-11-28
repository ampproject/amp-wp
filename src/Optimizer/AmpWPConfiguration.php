<?php
/**
 * Class AmpWPConfiguration.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Optimizer;

use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\DefaultConfiguration;
use AmpProject\Optimizer\Exception\UnknownConfigurationKey;
use AmpProject\Optimizer\Transformer;
use AmpProject\AmpWP\Optimizer\Transformer as WpTransformer;
use AmpProject\Optimizer\TransformerConfiguration;

/**
 * Optimizer Configuration implementation that is mutable and filterable.
 *
 * @package AmpProject\AmpWP
 * @since 2.1.0
 * @internal
 */
final class AmpWPConfiguration extends DefaultConfiguration {

	/**
	 * Whether the filters have already been applied.
	 *
	 * @var bool
	 */
	private $already_applied = false;

	/**
	 * Apply the filters to adapt the configuration.
	 *
	 * Note: They will only be applied once, when this method is hit for the first time.
	 */
	public function apply_filters() {
		if ( $this->already_applied ) {
			return;
		}

		$transformers = self::DEFAULT_TRANSFORMERS;

		/**
		 * Filter whether the AMP Optimizer should use server-side rendering or not.
		 *
		 * @since 1.5.0
		 *
		 * @param bool $enable_ssr Whether the AMP Optimizer should use server-side rendering or not.
		 */
		$enable_ssr = apply_filters( 'amp_enable_ssr', true );

		// In debugging mode, we don't use server-side rendering, as it further obfuscates the HTML markup.
		if ( ! $enable_ssr ) {
			$transformers = array_diff(
				$transformers,
				[
					Transformer\AmpRuntimeCss::class,
					Transformer\OptimizeAmpBind::class,
					Transformer\OptimizeHeroImages::class,
					Transformer\RewriteAmpUrls::class,
					Transformer\ServerSideRendering::class,
					Transformer\TransformedIdentifier::class,
				]
			);
		}

		array_unshift(
			$transformers,
			WpTransformer\DetermineHeroImages::class,
			WpTransformer\AmpSchemaOrgMetadata::class
		);

		$this->registerConfigurationClass(
			WpTransformer\AmpSchemaOrgMetadata::class,
			WpTransformer\AmpSchemaOrgMetadataConfiguration::class
		);

		/**
		 * Filter the configuration to be used for the AMP Optimizer.
		 *
		 * @since 1.5.0
		 *
		 * @param array $configuration Associative array of configuration data.
		 */
		$this->configuration = apply_filters(
			'amp_optimizer_config',
			[
				self::KEY_TRANSFORMERS                => $transformers,
				Transformer\OptimizeHeroImages::class => [
					Configuration\OptimizeHeroImagesConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE => 'data-amp-original-style',
					Configuration\OptimizeHeroImagesConfiguration::MAX_HERO_IMAGE_COUNT          => PHP_INT_MAX,
				],
			]
		);

		$this->already_applied = true;
	}

	/**
	 * Get the value for a given key from the configuration.
	 *
	 * @param string $key Configuration key to get the value for.
	 * @return mixed Configuration value for the requested key.
	 * @throws UnknownConfigurationKey If the key was not found.
	 */
	public function get( $key ) {
		$this->apply_filters();

		return parent::get( $key );
	}

	/**
	 * Get the transformer-specific configuration for the requested transformer.
	 *
	 * @param string $transformer FQCN of the transformer to get the configuration for.
	 * @return TransformerConfiguration Transformer-specific configuration.
	 */
	public function getTransformerConfiguration( $transformer ) {
		$this->apply_filters();

		return parent::getTransformerConfiguration( $transformer );
	}
}
