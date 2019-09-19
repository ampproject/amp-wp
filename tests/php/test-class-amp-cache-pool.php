<?php
/**
 * Tests for AMP_Cache_Pool.
 *
 * @package AMP
 * @since   1.0
 */

/**
 * Tests for AMP_Cache_Pool.
 *
 * @covers AMP_Cache_Pool
 */
class Test_AMP_Cache_Pool extends WP_UnitTestCase {

	public function test_transients_storing_a_value() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$cache = new AMP_Cache_Pool( 'testing', 5 );

		$cache->set( 'foo', 'bar' );

		$this->assertEquals( 'bar', $cache->get( 'foo' ) );
	}

	public function test_transients_overwriting_a_value() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$cache = new AMP_Cache_Pool( 'testing', 5 );

		$cache->set( 'foo', 'bar' );
		$cache->set( 'foo', 'baz' );

		$this->assertEquals( 'baz', $cache->get( 'foo' ) );
	}

	public function test_transients_cycling_through_values() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$cache = new AMP_Cache_Pool( 'testing', 5 );

		$cache->set( 'foo_1', 'bar_1' );
		$cache->set( 'foo_2', 'bar_2' );
		$cache->set( 'foo_3', 'bar_3' );
		$cache->set( 'foo_4', 'bar_4' );
		$cache->set( 'foo_5', 'bar_5' );
		$cache->set( 'foo_6', 'bar_6' );

		$this->assertEquals( false, $cache->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache->get( 'foo_4' ) );
		$this->assertEquals( 'bar_5', $cache->get( 'foo_5' ) );
		$this->assertEquals( 'bar_6', $cache->get( 'foo_6' ) );
	}

	public function test_transients_multiple_caches() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$cache_1 = new AMP_Cache_Pool( 'testing', 3 );
		$cache_2 = new AMP_Cache_Pool( 'testing', 3 );
		$cache_3 = new AMP_Cache_Pool( 'testing', 3 );

		$cache_1->set( 'foo_1', 'bar_1' );
		$cache_2->set( 'foo_2', 'bar_2' );
		$cache_3->set( 'foo_3', 'bar_3' );
		$cache_1->set( 'foo_4', 'bar_4' );

		$this->assertEquals( false, $cache_1->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_1->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_1->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_1->get( 'foo_4' ) );
		$this->assertEquals( false, $cache_2->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_2->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_2->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_2->get( 'foo_4' ) );
		$this->assertEquals( false, $cache_3->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_3->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_3->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_3->get( 'foo_4' ) );
	}

	public function test_transients_multiple_groups() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;

		$cache_1 = new AMP_Cache_Pool( 'testing_1', 3 );
		$cache_2 = new AMP_Cache_Pool( 'testing_2', 3 );
		$cache_3 = new AMP_Cache_Pool( 'testing_3', 3 );

		$cache_1->set( 'foo_1', 'bar_1' );
		$cache_2->set( 'foo_2', 'bar_2' );
		$cache_3->set( 'foo_3', 'bar_3' );
		$cache_1->set( 'foo_4', 'bar_4' );

		$this->assertEquals( 'bar_1', $cache_1->get( 'foo_1' ) );
		$this->assertEquals( false, $cache_1->get( 'foo_2' ) );
		$this->assertEquals( false, $cache_1->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_1->get( 'foo_4' ) );
		$this->assertEquals( false, $cache_2->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_2->get( 'foo_2' ) );
		$this->assertEquals( false, $cache_2->get( 'foo_3' ) );
		$this->assertEquals( false, $cache_2->get( 'foo_4' ) );
		$this->assertEquals( false, $cache_3->get( 'foo_1' ) );
		$this->assertEquals( false, $cache_3->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_3->get( 'foo_3' ) );
		$this->assertEquals( false, $cache_3->get( 'foo_4' ) );
	}

	public function test_object_cache_storing_a_value() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = true;

		$cache = new AMP_Cache_Pool( 'testing', 5 );

		$cache->set( 'foo', 'bar' );

		$this->assertEquals( 'bar', $cache->get( 'foo' ) );
	}

	public function test_object_cache_overwriting_a_value() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = true;

		$cache = new AMP_Cache_Pool( 'testing', 5 );

		$cache->set( 'foo', 'bar' );
		$cache->set( 'foo', 'baz' );

		$this->assertEquals( 'baz', $cache->get( 'foo' ) );
	}

	public function test_object_cache_cycling_through_values() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = true;

		$cache = new AMP_Cache_Pool( 'testing', 5 );

		$cache->set( 'foo_1', 'bar_1' );
		$cache->set( 'foo_2', 'bar_2' );
		$cache->set( 'foo_3', 'bar_3' );
		$cache->set( 'foo_4', 'bar_4' );
		$cache->set( 'foo_5', 'bar_5' );
		$cache->set( 'foo_6', 'bar_6' );

		// Size does not apply to object caches.
		$this->assertEquals( 'bar_1', $cache->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache->get( 'foo_4' ) );
		$this->assertEquals( 'bar_5', $cache->get( 'foo_5' ) );
		$this->assertEquals( 'bar_6', $cache->get( 'foo_6' ) );
	}

	public function test_object_cache_multiple_caches() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = true;

		$cache_1 = new AMP_Cache_Pool( 'testing', 3 );
		$cache_2 = new AMP_Cache_Pool( 'testing', 3 );
		$cache_3 = new AMP_Cache_Pool( 'testing', 3 );

		$cache_1->set( 'foo_1', 'bar_1' );
		$cache_2->set( 'foo_2', 'bar_2' );
		$cache_3->set( 'foo_3', 'bar_3' );
		$cache_1->set( 'foo_4', 'bar_4' );

		// Size does not apply to object caches.
		$this->assertEquals( 'bar_1', $cache_1->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_1->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_1->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_1->get( 'foo_4' ) );
		$this->assertEquals( 'bar_1', $cache_2->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_2->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_2->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_2->get( 'foo_4' ) );
		$this->assertEquals( 'bar_1', $cache_3->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_3->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_3->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_3->get( 'foo_4' ) );
	}

	public function test_object_cache_multiple_groups() {
		global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = true;

		$cache_1 = new AMP_Cache_Pool( 'testing_1', 3 );
		$cache_2 = new AMP_Cache_Pool( 'testing_2', 3 );
		$cache_3 = new AMP_Cache_Pool( 'testing_3', 3 );

		$cache_1->set( 'foo_1', 'bar_1' );
		$cache_2->set( 'foo_2', 'bar_2' );
		$cache_3->set( 'foo_3', 'bar_3' );
		$cache_1->set( 'foo_4', 'bar_4' );

		$this->assertEquals( 'bar_1', $cache_1->get( 'foo_1' ) );
		$this->assertEquals( false, $cache_1->get( 'foo_2' ) );
		$this->assertEquals( false, $cache_1->get( 'foo_3' ) );
		$this->assertEquals( 'bar_4', $cache_1->get( 'foo_4' ) );
		$this->assertEquals( false, $cache_2->get( 'foo_1' ) );
		$this->assertEquals( 'bar_2', $cache_2->get( 'foo_2' ) );
		$this->assertEquals( false, $cache_2->get( 'foo_3' ) );
		$this->assertEquals( false, $cache_2->get( 'foo_4' ) );
		$this->assertEquals( false, $cache_3->get( 'foo_1' ) );
		$this->assertEquals( false, $cache_3->get( 'foo_2' ) );
		$this->assertEquals( 'bar_3', $cache_3->get( 'foo_3' ) );
		$this->assertEquals( false, $cache_3->get( 'foo_4' ) );
	}
}
