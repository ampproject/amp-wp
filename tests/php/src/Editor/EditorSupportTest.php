<?php

namespace AmpProject\AmpWP\Tests\Editor;

use AmpProject\AmpWP\Editor\EditorSupport;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\WithBlockEditorSupport;
use AmpProject\AmpWP\Tests\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Editor\EditorSupport */
final class EditorSupportTest extends TestCase {

	use WithBlockEditorSupport;

	/** @var EditorSupport */
	private $instance;

	public function set_up() {
		parent::set_up();

		$this->instance = new EditorSupport();

		unset( $GLOBALS['current_screen'], $GLOBALS['wp_scripts'] );
	}

	public function test_it_can_be_initialized() {
		$this->assertInstanceOf( EditorSupport::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
	}

	/**
	 * Test data for test_supports_current_screen().
	 *
	 * @return array
	 */
	public function get_data_for_test_supports_current_screen() {
		return [
			'supports post type and amp'         => [ true, true, true ],
			'supports only post type'            => [ true, false, false ],
			'supports only amp'                  => [ false, true, false ],
			'does not support post type nor amp' => [ false, false, false ],
		];
	}

	/**
	 * @covers ::is_current_screen_block_editor_for_amp_enabled_post_type()
	 * @dataProvider get_data_for_test_supports_current_screen()
	 *
	 * @param bool $post_type_uses_block_editor Whether post type can be edited in the block editor.
	 * @param bool $post_type_supports_amp      Whether post type supports AMP.
	 * @param bool $expected_result             Expected result for test assertions.
	 */
	public function test_supports_current_screen( $post_type_uses_block_editor, $post_type_supports_amp, $expected_result ) {
		$this->setup_environment( $post_type_uses_block_editor, $post_type_supports_amp );

		// Note: Without Gutenberg being installed on WP 4.9, the expected result would be `false`
		// when `$post_type_uses_block_editor` and `$post_type_supports_amp` are `true`.
		$this->assertSame( $expected_result, $this->instance->is_current_screen_block_editor_for_amp_enabled_post_type() );
	}
}
