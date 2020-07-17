<?php
/**
 * Tests for AMP_Nav_Menu_Dropdown_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/**
 * Class Test_AMP_Nav_Menu_Dropdown_Sanitizer
 *
 * @covers AMP_Nav_Menu_Dropdown_Sanitizer
 */
class Test_AMP_Nav_Menu_Dropdown_Sanitizer extends WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * Test add_buffering_hooks.
	 *
	 * @covers AMP_Nav_Menu_Dropdown_Sanitizer::add_buffering_hooks
	 */
	public function test_add_buffering_hooks() {
		$filter = 'walker_nav_menu_start_el';
		remove_all_filters( $filter );

		// The filter should not be added if certain args are not passed.
		AMP_Nav_Menu_Dropdown_Sanitizer::add_buffering_hooks( [] );
		$this->assertEquals( false, has_filter( $filter ) );

		/**
		 * Expected output.
		 * NB: This test is placed first as the static `$nav_menu_item_number` variable affects the output of the filter.
		 */
		AMP_Nav_Menu_Dropdown_Sanitizer::add_buffering_hooks(
			[
				'sub_menu_button_class'        => 'foo',
				'sub_menu_button_toggle_class' => 'bar',
			]
		);
		$post          = new \stdClass();
		$post->classes = [ 'menu-item-has-children' ];

		$nav_menu_args                 = new \stdClass();
		$nav_menu_args->theme_location = 'foo';

		$expected = '<amp-state id="navMenuItemExpanded1"><script type="application/json">false</script></amp-state><button class="foo" [class]="&quot;foo&quot; + ( navMenuItemExpanded1 ? &quot; bar&quot; : &#039;&#039; )" aria-expanded="false" [aria-expanded]="navMenuItemExpanded1 ? &#039;true&#039; : &#039;false&#039;" on="tap:AMP.setState( { navMenuItemExpanded1: ! navMenuItemExpanded1 } )"><span class="screen-reader-text" [text]="navMenuItemExpanded1 ? &quot;collapse child menu&quot; : &quot;expand child menu&quot;">expand child menu</span></button>';
		$actual   = apply_filters( $filter, '', $post, 0, $nav_menu_args );
		$this->assertEquals( $expected, $actual );

		// If $nav_menu_args->theme_location is not set, `$item` is returned.
		AMP_Nav_Menu_Dropdown_Sanitizer::add_buffering_hooks(
			[
				'sub_menu_button_class'        => 'foo',
				'sub_menu_button_toggle_class' => 'bar',
			]
		);
		$actual = apply_filters( $filter, 'foobar', new \stdClass(), 0, new \stdClass() );
		$this->assertEquals( 'foobar', $actual );

		// If 'menu-item-has-children' is not the array for $post->classes,`$item` is returned.
		AMP_Nav_Menu_Dropdown_Sanitizer::add_buffering_hooks(
			[
				'sub_menu_button_class'        => 'foo',
				'sub_menu_button_toggle_class' => 'bar',
			]
		);
		$post                          = new \stdClass();
		$post->classes                 = [];
		$nav_menu_args                 = new \stdClass();
		$nav_menu_args->theme_location = 'foo';
		$actual                        = apply_filters( $filter, 'foobar', $post, 0, $nav_menu_args );
		$this->assertEquals( 'foobar', $actual );
	}

	/**
	 * Test ensure_defaults.
	 *
	 * @covers AMP_Nav_Menu_Dropdown_Sanitizer::ensure_defaults
	 */
	public function test_ensure_defaults() {
		$expected = [
			'expand_text'            => 'expand child menu',
			'collapse_text'          => 'collapse child menu',
			'nav_menu_item_state_id' => 'navMenuItemExpanded',
		];
		$actual   = $this->call_private_static_method( 'AMP_Nav_Menu_Dropdown_Sanitizer', 'ensure_defaults', [ [] ] );
		$this->assertEquals( $expected, $actual );
	}
}
