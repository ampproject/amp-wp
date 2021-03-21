<?php
/**
 * Tests for Test_AMP_CLI_Validation_Command class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;

/**
 * Tests for Test_AMP_CLI_Validation_Command class.
 *
 * @since 1.0
 *
 * @coversDefaultClass AMP_CLI_Validation_Command
 */
class Test_AMP_CLI_Validation_Command extends WP_UnitTestCase {

	use PrivateAccess, ValidationRequestMocking;

	/**
	 * Store a reference to the validation command object.
	 *
	 * @var AMP_CLI_Validation_Command
	 */
	private $validation;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
		$this->validation = new AMP_CLI_Validation_Command();
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/**
	 * Test validate_urls.
	 *
	 * @covers ::validate_urls()
	 * @covers ::get_validation_provider()
	 * @covers ::get_validation_url_provider()
	 */
	public function test_validate_urls() {
		$number_of_posts = 20;
		$number_of_terms = 30;
		$posts           = [];
		$post_permalinks = [];
		$terms           = [];

		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$post_id           = self::factory()->post->create();
			$posts[]           = $post_id;
			$post_permalinks[] = get_permalink( $post_id );
		}
		$this->call_private_method( $this->validation, 'validate_urls' );

		// All of the posts created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $post_permalinks, $this->get_validated_urls() ) );

		$this->validation = new AMP_CLI_Validation_Command();
		for ( $i = 0; $i < $number_of_terms; $i++ ) {
			$terms[] = self::factory()->category->create();
		}

		// Terms need to be associated with a post in order to be returned in get_terms().
		wp_set_post_terms( $posts[0], $terms, 'category' );
		$this->call_private_method( $this->validation, 'validate_urls' );
		$expected_validated_urls = array_map( 'get_term_link', $terms );
		$actual_validated_urls   = $this->get_validated_urls();

		// All of the terms created above should be present in $validated_urls.
		$this->assertEmpty( array_diff( $expected_validated_urls, $actual_validated_urls ) );
		$this->assertContains( home_url( '/' ), $this->get_validated_urls() );
	}
}
