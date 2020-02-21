<?php
/**
 * Tests for amp.php.
 *
 * @package AMP
 */

/**
 * Tests for amp.php.
 */
class Test_AMP extends WP_UnitTestCase {

	/**
	 * Tear down and clean up.
	 */
	public function tearDown() {
		parent::tearDown();
		remove_theme_support( AMP_Theme_Support::SLUG );
	}

	/**
	 * Get data for test_amp_is_canonical.
	 *
	 * @return array
	 */
	public function get_amp_is_canonical_test_data() {
		return [
			'default'                     => [
				null,
				false,
				null,
			],
			'no_args'                     => [
				[],
				true,
				AMP_Theme_Support::STANDARD_MODE_SLUG,
			],
			'paired_implied'              => [
				[
					'template_dir' => 'amp-templates',
				],
				false,
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			],
			'paired_with_template_dir'    => [
				[
					AMP_Theme_Support::PAIRED_FLAG => true,
					'template_dir'                 => 'amp-templates',
				],
				false,
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			],
			'canonical_with_template_dir' => [
				[
					// This should be a rare scenario, as standard mode should mean no separate templates.
					AMP_Theme_Support::PAIRED_FLAG => false,
					'template_dir'                 => 'amp-templates',
				],
				true,
				AMP_Theme_Support::STANDARD_MODE_SLUG,
			],
			'paired_without_template_dir' => [
				[
					AMP_Theme_Support::PAIRED_FLAG => true,
				],
				false,
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			],
		];
	}

	/**
	 * Test amp_is_canonical().
	 *
	 * @dataProvider get_amp_is_canonical_test_data
	 * @covers ::amp_is_canonical()
	 * @covers AMP_Theme_Support::get_support_mode_added_via_theme()
	 * @covers AMP_Theme_Support::read_theme_support()
	 * @param mixed  $theme_support_args Theme support args.
	 * @param bool   $is_canonical       Whether canonical.
	 * @param string $expected_mode      Expected mode.
	 */
	public function test_amp_is_canonical( $theme_support_args, $is_canonical, $expected_mode ) {
		remove_theme_support( AMP_Theme_Support::SLUG );
		delete_option( AMP_Options_Manager::OPTION_NAME );
		if ( isset( $theme_support_args ) ) {
			if ( is_array( $theme_support_args ) ) {
				add_theme_support( AMP_Theme_Support::SLUG, $theme_support_args );
			} else {
				add_theme_support( AMP_Theme_Support::SLUG );
			}
		}
		AMP_Theme_Support::read_theme_support();
		$this->assertSame( $expected_mode, AMP_Theme_Support::get_support_mode_added_via_theme() );
		$this->assertSame( $is_canonical, amp_is_canonical() );
	}

	/**
	 * Test that init_validate_request will be called super early.
	 */
	public function test_init_validate_request_added_to_plugins_loaded_action() {
		$this->assertSame( ~PHP_INT_MAX, has_action( 'plugins_loaded', [ 'AMP_Validation_Manager', 'init_validate_request' ] ) );
	}
}
