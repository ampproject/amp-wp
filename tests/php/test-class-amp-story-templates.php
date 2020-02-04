<?php
/**
 * Test AMP_Story_Templates.
 *
 * @package AMP
 */

/**
 * Test AMP_Story_Post_Type.
 */
class AMP_Story_Templates_Test extends WP_UnitTestCase {

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();

		if ( ! AMP_Story_Post_Type::has_required_block_capabilities() ) {
			$this->markTestSkipped( 'The minimum requirements for running stories are not present, so skipping test.' );
		}

		foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $block ) {
			if ( 'amp/' === substr( $block->name, 0, 4 ) ) {
				WP_Block_Type_Registry::get_instance()->unregister( $block->name );
			}
		}

		AMP_Options_Manager::update_option( 'experiences', [ AMP_Options_Manager::WEBSITE_EXPERIENCE, AMP_Options_Manager::STORIES_EXPERIENCE ] );
		// Create dummy post to keep Stories experience enabled.
		self::factory()->post->create( [ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ] );
		AMP_Story_Post_Type::register();
	}

	/**
	 * Test init().
	 *
	 * @covers AMP_Story_Templates::init()
	 */
	public function test_init() {
		$amp_story_templates = new AMP_Story_Templates();
		$amp_story_templates->init();

		$this->assertTrue( post_type_exists( 'amp_story' ) );
		$this->assertEquals( 10, has_action( 'save_post_wp_block', [ $amp_story_templates, 'flag_template_as_modified' ] ) );
		$this->assertEquals( 10, has_action( 'user_has_cap', [ $amp_story_templates, 'filter_user_has_cap' ] ) );
		$this->assertEquals( 10, has_action( 'pre_get_posts', [ $amp_story_templates, 'filter_pre_get_posts' ] ) );
	}

	/**
	 * Test filter_user_has_cap().
	 *
	 * @covers AMP_Story_Templates::filter_user_has_cap()
	 */
	public function test_filter_user_has_cap() {
		$amp_story_templates = new AMP_Story_Templates();
		$amp_story_templates->init();

		$story_id = self::factory()->post->create( [ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ] );
		wp_set_object_terms( $story_id, AMP_Story_Templates::TEMPLATES_TERM, AMP_Story_Templates::TEMPLATES_TAXONOMY );

		$allcaps = [
			'edit_others_posts'    => true,
			'edit_published_posts' => true,
		];
		$args    = [
			0 => 'edit_post',
			2 => $story_id,
		];

		$capabilities = $amp_story_templates->filter_user_has_cap( $allcaps, [], $args );
		$this->assertTrue( has_term( AMP_Story_Templates::TEMPLATES_TERM, AMP_Story_Templates::TEMPLATES_TAXONOMY, $args[2] ) );
		$this->assertEquals( [], $capabilities );
	}
}
