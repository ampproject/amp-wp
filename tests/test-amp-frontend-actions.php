<?php
/**
 * Test AMP frontend actions.
 *
 * @package AMP
 */

/**
 * Test functions in includes/amp-frontend-actions.php
 */
class Test_AMP_Frontend_Actions extends WP_UnitTestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		require_once AMP__DIR__ . '/includes/amp-helper-functions.php';
		amp_add_frontend_actions();
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		remove_theme_support( 'amp' );
		parent::tearDown();
	}

	/**
	 * Test that hook is added.
	 *
	 * @covers \amp_add_frontend_actions()
	 */
	public function test_has_hook() {
		$this->assertEquals( 10, has_action( 'wp_head', 'amp_frontend_add_canonical' ) );
	}

	/**
	 * URLs to test amphtml link.
	 *
	 * @return array
	 */
	public function get_amphtml_urls() {
		$post_id = $this->factory()->post->create();
		return array(
			'home' => array(
				home_url( '/' ),
				add_query_arg( amp_get_slug(), '', home_url( '/' ) ),
			),
			'404'  => array(
				home_url( '/no-existe/' ),
				add_query_arg( amp_get_slug(), '', home_url( '/no-existe/' ) ),
			),
			'post' => array(
				get_permalink( $post_id ),
				amp_get_permalink( $post_id ),
			),
		);
	}

	/**
	 * Adding link when theme support is not present.
	 *
	 * @dataProvider get_amphtml_urls
	 * @covers \amp_frontend_add_canonical()
	 * @param string $canonical_url Canonical URL.
	 * @param string $amphtml_url   The amphtml URL.
	 */
	public function test_amp_frontend_add_canonical( $canonical_url, $amphtml_url ) {
		$this->go_to( $canonical_url );
		ob_start();
		amp_frontend_add_canonical();
		$output = ob_get_clean();
		$this->assertEquals(
			sprintf( '<link rel="amphtml" href="%s">', esc_url( $amphtml_url ) ),
			$output
		);

		// Make sure adding the filter hides the amphtml link.
		add_filter( 'amp_frontend_show_canonical', '__return_false' );
		ob_start();
		amp_frontend_add_canonical();
		$output = ob_get_clean();
		$this->assertEmpty( $output );
	}
}
