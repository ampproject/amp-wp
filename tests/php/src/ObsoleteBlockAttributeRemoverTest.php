<?php

namespace AmpProject\AmpWP\Tests;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\ObsoleteBlockAttributeRemover;
use WP_UnitTestCase;
use WP_REST_Response;

/** @coversDefaultClass \AmpProject\AmpWP\ObsoleteBlockAttributeRemover */
final class ObsoleteBlockAttributeRemoverTest extends WP_UnitTestCase {

	/** @var ObsoleteBlockAttributeRemover */
	private $instance;

	const PROP_ATTRIBUTE_MAPPING = [
		'ampCarousel'  => 'data-amp-carousel',
		'ampLayout'    => 'data-amp-layout',
		'ampLightbox'  => 'data-amp-lightbox',
		'ampNoLoading' => 'data-amp-noloading',
	];

	public function setUp() {
		parent::setUp();
		$this->instance = new ObsoleteBlockAttributeRemover();
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
	}

	/** @covers ::get_registration_action() */
	public function test_get_registration_action() {
		$this->assertEquals( 'rest_api_init', ObsoleteBlockAttributeRemover::get_registration_action() );
	}

	/** @covers ::register() */
	public function test_register() {
		$editor_post_types = get_post_types_by_support( 'editor' );
		$this->assertNotEmpty( $editor_post_types );
		$this->instance->register();
		foreach ( $editor_post_types as $editor_post_type ) {
			$this->assertEquals( 10, has_filter( "rest_prepare_{$editor_post_type}", [ $this->instance, 'filter_rest_prepare_post' ] ) );
		}
	}

	/** @return array */
	public function get_block_data() {
		return [
			'gallery_carousel_lightbox' => [
				'
				<!-- wp:gallery {"ids":[1869,770,760],"ampCarousel":true,"ampLightbox":true} -->
				<figure class="wp-block-gallery columns-3 is-cropped" data-amp-lightbox="true" data-amp-carousel="true"><ul class="blocks-gallery-grid"><li class="blocks-gallery-item"><figure><img src="https://wordpressdev.lndo.site/content/uploads/2020/07/American_bison_k5680-1-1024x668.jpg" alt="" data-id="1869" data-full-url="https://wordpressdev.lndo.site/content/uploads/2020/07/American_bison_k5680-1-scaled.jpg" data-link="https://wordpressdev.lndo.site/2020/07/12/implement-transformer-in-optimizer-for-media-sizes-and-heights/american_bison_k5680-1/" class="wp-image-1869"/></figure></li><li class="blocks-gallery-item"><figure><img src="https://wordpressdev.lndo.site/content/uploads/2011/07/img_0767.jpg" alt="Huatulco Coastline" data-id="770" data-full-url="https://wordpressdev.lndo.site/content/uploads/2011/07/img_0767.jpg" data-link="https://wordpressdev.lndo.site/2010/09/10/post-format-gallery/img_0767/" class="wp-image-770"/><figcaption class="blocks-gallery-item__caption">Coastline in Huatulco, Oaxaca, Mexico</figcaption></figure></li><li class="blocks-gallery-item"><figure><img src="https://wordpressdev.lndo.site/content/uploads/2011/07/dsc09114-1024x768.jpg" alt="Sydney Harbor Bridge" data-id="760" data-full-url="https://wordpressdev.lndo.site/content/uploads/2011/07/dsc09114.jpg" data-link="https://wordpressdev.lndo.site/2010/09/10/post-format-gallery/dsc09114/" class="wp-image-760"/><figcaption class="blocks-gallery-item__caption">Sydney Harbor Bridge</figcaption></figure></li></ul><figcaption class="blocks-gallery-caption">Carousel and Lightbox</figcaption></figure>
				<!-- /wp:gallery -->
				',
				2,
			],
			'image_block_multi_attrs'   => [
				'
				<!-- wp:image {"id":1045,"width":336,"height":221,"sizeSlug":"large","ampLightbox":true,"ampLayout":"fixed","ampNoLoading":true} -->
				<figure class="wp-block-image size-large is-resized" data-amp-layout="fixed" data-amp-noloading="true" data-amp-lightbox="true"><img src="https://wordpressdev.lndo.site/content/uploads/2013/03/unicorn-wallpaper-1024x768.jpg" alt="Unicorn Wallpaper" class="wp-image-1045" width="336" height="221"/><figcaption>Noloading and lightbox and fixed layout</figcaption></figure>
				<!-- /wp:image -->
				',
				3,
			],
			'image_block'               => [
				'
				<!-- wp:image {"id":1869,"width":354,"height":233,"sizeSlug":"large","ampLayout":"fixed"} -->
				<figure class="wp-block-image size-large is-resized" data-amp-layout="fixed"><img src="https://wordpressdev.lndo.site/content/uploads/2020/07/American_bison_k5680-1-1024x668.jpg" alt="" class="wp-image-1869" width="354" height="233"/><figcaption>Fixed layout</figcaption></figure>
				<!-- /wp:image -->
				',
				1, // Expect props.
			],
			'amp_fit_text'              => [
				'
				<!-- wp:paragraph {"ampFitText":true} -->
				<amp-fit-text layout="fixed-height" min-font-size="6" max-font-size="72" height="100"><p>Here is amp-fit-text!</p></amp-fit-text>
				<!-- /wp:paragraph -->
				',
				0, // Expect props.
			],
			'huge_post'                 => [
				'
				<!-- wp:paragraph -->'
				. str_repeat( 'a', 111101 ) .
				'<!-- /wp:paragraph -->
				',
				0, // Expect props.
			],
		];
	}

	/**
	 * @covers ::filter_rest_prepare_post()
	 *
	 * @dataProvider get_block_data
	 * @param string $block_content
	 * @param int    $expected_prop_count
	 */
	public function test_filter_rest_prepare_post_raw( $block_content, $expected_prop_count ) {
		if ( ! function_exists( 'parse_blocks' ) ) {
			$this->markTestSkipped();
		}
		$block_content = str_replace( "\t", '', trim( $block_content ) );

		$parsed_blocks = parse_blocks( $block_content );
		$this->assertCount( 1, $parsed_blocks );
		$parsed_block = array_shift( $parsed_blocks );

		$response = new WP_REST_Response(
			[
				'content' => [
					'raw'      => $block_content,
					'rendered' => preg_replace( '/<!--.+?-->/s', '', $block_content ),
				],
			]
		);

		$filtered_response = $this->instance->filter_rest_prepare_post( clone $response );
		$this->assertNotNull( $filtered_response );

		if ( $expected_prop_count > 0 ) {
			$this->assertNotEquals( $response->data['content']['raw'], $filtered_response->data['content']['raw'] );
		} else {
			$this->assertEquals( $response->data['content']['raw'], $filtered_response->data['content']['raw'] );
		}
		$this->assertEquals( $response->data['content']['rendered'], $filtered_response->data['content']['rendered'] );

		$present_count = 0;
		foreach ( self::PROP_ATTRIBUTE_MAPPING as $prop => $attribute ) {
			if ( isset( $parsed_block['attrs'][ $prop ] ) ) {
				$this->assertStringContainsString( "$attribute=", $response->data['content']['raw'] );
				$this->assertStringNotContainsString( "$attribute=", $filtered_response->data['content']['raw'] );
				$present_count++;
			}
		}

		$this->assertEquals( $expected_prop_count, $present_count );
	}

	/** @covers ::filter_rest_prepare_post() */
	public function test_filter_rest_prepare_post_rendered_only() {
		$response = new WP_REST_Response(
			[
				'content' => [
					'rendered' => '<amp-fit-text layout="fixed-height" min-font-size="6" max-font-size="72" height="100"><p>Here is amp-fit-text!</p></amp-fit-text>',
				],
			]
		);

		$filtered_response = $this->instance->filter_rest_prepare_post( clone $response );
		$this->assertEquals( $response->data['content']['rendered'], $filtered_response->data['content']['rendered'] );
	}
}
