<?php

namespace AmpProject\AmpWP\Tests\Optimizer;

use AmpProject\AmpWP\Optimizer\AmpWPConfiguration;
use AmpProject\Optimizer\Configuration\OptimizeHeroImagesConfiguration;
use AmpProject\Optimizer\Transformer\OptimizeHeroImages;
use AmpProject\Optimizer\Transformer\ServerSideRendering;
use AmpProject\Optimizer\TransformerConfiguration;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Optimizer\AmpWPConfiguration */
final class AmpWPConfigurationTest extends TestCase {

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::get()
	 */
	public function test_get_unfiltered_configuration_key() {
		$configuration = new AmpWPConfiguration();
		$transformers  = $configuration->get( AmpWPConfiguration::KEY_TRANSFORMERS );

		$this->assertStringContainsString( ServerSideRendering::class, $transformers );
	}

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::get()
	 */
	public function test_get_filtered_configuration_key() {
		add_filter( 'amp_enable_ssr', '__return_false' );

		$configuration = new AmpWPConfiguration();
		$transformers  = $configuration->get( AmpWPConfiguration::KEY_TRANSFORMERS );

		$this->assertStringNotContainsString( ServerSideRendering::class, $transformers );

		remove_filter( 'amp_enable_ssr', '__return_false' );
	}

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::getTransformerConfiguration
	 */
	public function test_get_unfiltered_transformer_configuration_key() {
		$configuration             = new AmpWPConfiguration();
		$transformer_configuration = $configuration->getTransformerConfiguration( OptimizeHeroImages::class );

		$this->assertInstanceOf( TransformerConfiguration::class, $transformer_configuration );
		$this->assertEquals(
			'data-amp-original-style',
			$transformer_configuration->get( OptimizeHeroImagesConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE )
		);
	}

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::getTransformerConfiguration()
	 */
	public function test_get_filtered_transformer_configuration_key() {
		$configuration_filter = static function ( $configuration ) {
			$configuration[ OptimizeHeroImages::class ]['inlineStyleBackupAttribute'] = 'data-backup-style';
			return $configuration;
		};

		add_filter( 'amp_optimizer_config', $configuration_filter );

		$configuration             = new AmpWPConfiguration();
		$transformer_configuration = $configuration->getTransformerConfiguration( OptimizeHeroImages::class );

		$this->assertInstanceOf( TransformerConfiguration::class, $transformer_configuration );
		$this->assertEquals(
			'data-backup-style',
			$transformer_configuration->get( OptimizeHeroImagesConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE )
		);

		remove_filter( 'amp_optimizer_config', $configuration_filter );
	}
}
