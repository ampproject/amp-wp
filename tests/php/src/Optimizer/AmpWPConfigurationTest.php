<?php

namespace AmpProject\AmpWP\Tests\Optimizer;

use AmpProject\AmpWP\Optimizer\AmpWPConfiguration;
use AmpProject\Optimizer\Configuration\PreloadHeroImageConfiguration;
use AmpProject\Optimizer\Transformer\PreloadHeroImage;
use AmpProject\Optimizer\Transformer\ServerSideRendering;
use AmpProject\Optimizer\TransformerConfiguration;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Optimizer\AmpWPConfiguration */
final class AmpWPConfigurationTest extends WP_UnitTestCase {

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::get()
	 */
	public function test_get_unfiltered_configuration_key() {
		$configuration = new AmpWPConfiguration();
		$transformers  = $configuration->get( AmpWPConfiguration::KEY_TRANSFORMERS );

		$this->assertContains( ServerSideRendering::class, $transformers );
	}

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::get()
	 */
	public function test_get_filtered_configuration_key() {
		add_filter( 'amp_enable_ssr', '__return_false' );

		$configuration = new AmpWPConfiguration();
		$transformers  = $configuration->get( AmpWPConfiguration::KEY_TRANSFORMERS );

		$this->assertNotContains( ServerSideRendering::class, $transformers );

		remove_filter( 'amp_enable_ssr', '__return_false' );
	}

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::getTransformerConfiguration
	 */
	public function test_get_unfiltered_transformer_configuration_key() {
		$configuration             = new AmpWPConfiguration();
		$transformer_configuration = $configuration->getTransformerConfiguration( PreloadHeroImage::class );

		$this->assertInstanceOf( TransformerConfiguration::class, $transformer_configuration );
		$this->assertEquals(
			'data-amp-original-style',
			$transformer_configuration->get( PreloadHeroImageConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE )
		);
	}

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::apply_filters()
	 * @covers \AmpProject\AmpWP\Optimizer\AmpWPConfiguration::getTransformerConfiguration()
	 */
	public function test_get_filtered_transformer_configuration_key() {
		$configuration_filter = static function ( $configuration ) {
			$configuration[ PreloadHeroImage::class ]['inlineStyleBackupAttribute'] = 'data-backup-style';
			return $configuration;
		};

		add_filter( 'amp_optimizer_config', $configuration_filter );

		$configuration             = new AmpWPConfiguration();
		$transformer_configuration = $configuration->getTransformerConfiguration( PreloadHeroImage::class );

		$this->assertInstanceOf( TransformerConfiguration::class, $transformer_configuration );
		$this->assertEquals(
			'data-backup-style',
			$transformer_configuration->get( PreloadHeroImageConfiguration::INLINE_STYLE_BACKUP_ATTRIBUTE )
		);

		remove_filter( 'amp_optimizer_config', $configuration_filter );
	}
}
