<?php
/**
 * Class AMP_Cache_Pool
 *
 * @package AMP
 */

/**
 * Cache pool abstraction based on WordPress transients.
 *
 * @since 1.4.0
 */
final class AMP_Cache_Pool {

	/**
	 * Default size of the cache pool.
	 *
	 * @var int
	 */
	const DEFAULT_POOL_SIZE = 1000;

	/**
	 * Pool map of cached entries.
	 *
	 * @var array
	 */
	private $pool_map = [];

	/**
	 * Index into the pool map.
	 *
	 * @var int
	 */
	private $pool_index = -1;

	/**
	 * Cache group to use.
	 *
	 * @var string
	 */
	private $group;

	/**
	 * Size of the cache pool.
	 *
	 * @var int
	 */
	private $size;

	/**
	 * Whether an object cache is available.
	 *
	 * @var bool
	 */
	private $is_object_cache_available;

	/**
	 * Instantiate an AMP_Cache_Pool object.
	 *
	 * @param string $group Optional. Group to use. Defaults to an empty string.
	 * @param int    $size  Optional. Size of the cache pool.
	 */
	public function __construct( $group = '', $size = self::DEFAULT_POOL_SIZE ) {
		$this->group = $group;
		$this->size  = $size;

		$this->is_object_cache_available = false; // wp_using_ext_object_cache();

		if ( ! $this->is_object_cache_available ) {
			$this->read_pool_meta();
		}
	}

	/**
	 * Get the value of a given key from the cache.
	 *
	 * @param string $key Key of the cached value to retrieve.
	 *
	 * @return mixed Value that was stored under the requested key.
	 */
	public function get( $key ) {
		return $this->is_object_cache_available
			? wp_cache_get( $key, $this->group )
			: $this->get_rotated_transient( "{$this->group}-{$key}" );
	}

	/**
	 * Store a value under a given key in the cache.
	 *
	 * @param string $key   Key under which to store the value.
	 * @param mixed  $value Value to store in the cache.
	 */
	public function set( $key, $value ) {
		if ( $this->is_object_cache_available ) {
			wp_cache_set( $key, $value, $this->group );
		} else {
			$this->set_rotated_transient( "{$this->group}-{$key}", $value );
		}
	}

	/**
	 * Get a value from a rotating transient pool.
	 *
	 * @param string $key Key of the value to get.
	 * @return mixed Value for the requested key.
	 */
	private function get_rotated_transient( $key ) {
		$pool_index = array_search( $key, $this->pool_map, true );

		if ( false === $pool_index ) {
			return false;
		}

		return get_transient( "{$this->group}-pool-slot-{$pool_index}" );
	}

	/**
	 * Store a value in the rotating transient pool under a given key.
	 *
	 * @param string $key   Key under which to store the value.
	 * @param mixed  $value Value to store under the given key.
	 */
	private function set_rotated_transient( $key, $value ) {
		$pool_index = array_search( $key, $this->pool_map, true );

		// We already have the provided key and the value seems unchanged.
		if ( false !== $pool_index && $value === $this->pool_map[ $pool_index ] ) {
			return;
		}

		// As we didn't find the key, we create a new pool slot to store it.
		if ( false === $pool_index || -1 === $this->pool_index ) {
			$this->advance_pool_index();
			$this->pool_map[ $this->pool_index ] = $key;
		}

		// The expiration is to ensure transients don't stick around forever
		// since no LRU flushing like with external object cache.
		set_transient( "{$this->group}-pool-slot-{$this->pool_index}", $value, MONTH_IN_SECONDS );

		$this->persist_pool_meta();
	}

	/**
	 * Read the pool meta information that was persisted.
	 */
	private function read_pool_meta() {
		$this->pool_map   = get_transient( "{$this->group}-pool-map" ) ?: [];
		$this->pool_index = get_transient( "{$this->group}-pool-index" ) ?: -1;
	}

	/**
	 * Persist the pool meta information.
	 */
	private function persist_pool_meta() {
		set_transient( "{$this->group}-pool-map", $this->pool_map );
		set_transient( "{$this->group}-pool-index", $this->pool_index );
	}

	/**
	 * Advance the index into the pool cache.
	 */
	private function advance_pool_index() {
		$this->pool_index++;

		if ( $this->pool_index >= $this->size ) {
			$this->pool_index = 0;
		}
	}
}
