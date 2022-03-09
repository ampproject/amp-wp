<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\BlockUniqidClassNameTransformer;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/** @coversDefaultClass \AmpProject\AmpWP\BlockUniqidClassNameTransformer */
final class BlockUniqidClassNameTransformerTest extends TestCase {

	use MarkupComparison;

	/** @var BlockUniqidClassNameTransformer */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		if ( version_compare( get_bloginfo( 'version' ), '5.0.3', '<' ) ) {
			$this->markTestSkipped( 'Block uniqid class name transformer requires WordPress 5.0.3.' );
		}

		$this->instance = new BlockUniqidClassNameTransformer();
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'wp', BlockUniqidClassNameTransformer::get_registration_action() );
	}

	/**
	 * @covers ::is_needed()
	 * @covers ::has_gutenberg_plugin()
	 */
	public function test_is_needed() {
		if (
			! defined( 'GUTENBERG_VERSION' )
			&&
			version_compare( get_bloginfo( 'version' ), '5.9', '<' )
		) {
			$this->assertFalse( BlockUniqidClassNameTransformer::is_needed() );
		} else {
			AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
			$post_id = self::factory()->post->create();

			$this->go_to( get_permalink( $post_id ) );
			$this->assertFalse( amp_is_request() );
			$this->assertFalse( BlockUniqidClassNameTransformer::is_needed() );

			$this->go_to( amp_get_permalink( $post_id ) );
			$this->assertTrue( amp_is_request() );
			$this->assertTrue( BlockUniqidClassNameTransformer::is_needed() );
		}
	}

	/**
	 * @covers ::register()
	 * @covers ::get_block_inline_style_registration_hook_name()
	 */
	public function test_register() {
		$this->instance->register();
		$this->assertEquals( PHP_INT_MAX, has_filter( 'render_block', [ $this->instance, 'transform_class_names_in_block_content' ] ) );

		$expected_hook_name = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ? 'wp_enqueue_scripts' : 'wp_footer';
		$this->assertEquals(
			defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX, // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound
			has_filter( $expected_hook_name, [ $this->instance, 'transform_class_names_in_inline_styles' ] )
		);
	}

	/** @return array */
	public function get_block_data() {
		return [
			'transform_duotone_class_name'           => [
				'block_content'          => '<div class="wp-duotone-621e12fb51e3a wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'expected_block_content' => '<div class="wp-duotone-1 wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'style_handle'           => 'wp-duotone-621e12fb51e3a',
				'style_content'          => ".wp-duotone-621e12fb51e3a > .wp-block-cover__image-background, .wp-duotone-621e12fb51e3a > .wp-block-cover__video-background{filter:url(\'#wp-duotone-621e12fb51e3a\') !important;}",
				'expected_style_content' => ".wp-duotone-1 > .wp-block-cover__image-background, .wp-duotone-1 > .wp-block-cover__video-background{filter:url(\'#wp-duotone-1\') !important;}",
			],
			'transform_container_class_name'         => [
				'block_content'          => '<div class="wp-container-621e133aaf0e2 wp-block-group is-style-default has-black-background-color has-background" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">This is a super cool class name: <code>wp-container-0123456789abc</code>!</div>',
				'expected_block_content' => '<div class="wp-container-2 wp-block-group is-style-default has-black-background-color has-background" style="padding-top:20px;padding-right:20px;padding-bottom:20px;padding-left:20px">This is a super cool class name: <code>wp-container-0123456789abc</code>!</div>',
				'style_handle'           => null,
				'style_content'          => null,
				'expected_style_content' => null,
			],
			'ignore_class_names_without_hash'        => [
				'block_content'          => '<div class="wp-duotone-test wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'expected_block_content' => '<div class="wp-duotone-test wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'style_handle'           => null,
				'style_content'          => null,
				'expected_style_content' => null,
			],
			'ignore_already_transformed_class_names' => [
				'block_content'          => '<div class="wp-duotone-1 wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'expected_block_content' => '<div class="wp-duotone-1 wp-block-cover">This is a super cool class name: <code>wp-duotone-0123456789abc</code>!</div>',
				'style_handle'           => null,
				'style_content'          => null,
				'expected_style_content' => null,
			],
		];
	}

	/**
	 * @covers ::transform_class_names_in_block_content()
	 * @covers ::transform_class_names_in_inline_styles()
	 * @covers ::get_class_name_regexp_pattern()
	 *
	 * @dataProvider get_block_data
	 *
	 * @param string      $block_content
	 * @param string      $expected_block_content
	 * @param string|null $style_handle
	 * @param string|null $style_content
	 * @param string|null $expected_style_content
	 */
	public function test_transform_class_names( $block_content, $expected_block_content, $style_handle, $style_content, $expected_style_content ) {
		$transformed_block_content = $this->instance->transform_class_names_in_block_content( $block_content );
		$this->assertEqualMarkup( $expected_block_content, $transformed_block_content );

		if ( ! empty( $style_handle ) && ! empty( $style_content ) && ! empty( $expected_style_content ) ) {
			global $wp_styles;

			wp_register_style( $style_handle, '', [], '1' );
			wp_add_inline_style( $style_handle, $style_content );
			wp_enqueue_style( $style_handle );

			$this->assertTrue( wp_style_is( $style_handle ) );

			$this->instance->transform_class_names_in_inline_styles();

			$this->assertEquals(
				$expected_style_content,
				$wp_styles->registered[ $style_handle ]->extra['after'][0]
			);
		}
	}
}
