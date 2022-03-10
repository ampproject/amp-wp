<?php

namespace AmpProject\AmpWP\Tests;

use AMP_Block_Uniqid_Sanitizer;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\BlockUniqidTransformer;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/** @coversDefaultClass \AmpProject\AmpWP\BlockUniqidTransformer */
final class BlockUniqidTransformerTest extends TestCase {

	use MarkupComparison;

	/** @var BlockUniqidTransformer */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		$this->instance = new BlockUniqidTransformer();
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
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
			$this->assertFalse( BlockUniqidTransformer::is_needed() );
		} else {
			AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
			$post_id = self::factory()->post->create();

			$this->go_to( get_permalink( $post_id ) );
			$this->assertFalse( amp_is_request() );
			$this->assertFalse( BlockUniqidTransformer::is_needed() );

			$this->go_to( amp_get_permalink( $post_id ) );
			$this->assertTrue( amp_is_request() );
			$this->assertTrue( BlockUniqidTransformer::is_needed() );
		}
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {
		remove_all_filters( 'amp_content_sanitizers' );

		$this->assertArrayNotHasKey(
			AMP_Block_Uniqid_Sanitizer::class,
			amp_get_content_sanitizers()
		);

		$this->instance->register();
		$this->assertArrayHasKey(
			AMP_Block_Uniqid_Sanitizer::class,
			amp_get_content_sanitizers()
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
	 * @covers ::unique_id()
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
		$this->markTestIncomplete( 'Test neds to migrate over to tests for AMP_Block_Uniqid_Sanitizer.' );

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
