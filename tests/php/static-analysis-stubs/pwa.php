<?php
/**
 * Interface representing a service worker registry.
 *
 * @since 0.2
 */
interface WP_Service_Worker_Registry {
	/**
	 * Registers an item.
	 *
	 * @since 0.2
	 *
	 * @param string $handle Handle of the item.
	 * @param array  $args   Optional. Additional arguments. Default empty array.
	 */
	public function register( $handle, $args = array() );
	/**
	 * Gets all registered items.
	 *
	 * @since 0.2
	 *
	 * @return array List of registered items.
	 */
	public function get_all();
}
/**
 * Class used to register service workers.
 *
 * @since 0.1
 *
 * @see WP_Dependencies
 *
 * @method WP_Service_Worker_Precaching_Routes precaching_routes()
 * @method WP_Service_Worker_Caching_Routes caching_routes()
 */
class WP_Service_Worker_Scripts extends WP_Scripts implements WP_Service_Worker_Registry {
	/**
	 * Constructor.
	 *
	 * @since 0.2
	 *
	 * @param array $components Optional. Service worker components as $slug => $instance pairs.
	 *                          Each component must implement `WP_Service_Worker_Component`.
	 *                          Default empty array.
	 */
	public function __construct( $components = array() ) {
	}
	/**
	 * Initialize the class.
	 */
	public function init() {
	}
	/**
	 * Magic call method. Allows accessing component registries.
	 *
	 * @since 0.2
	 *
	 * @param string $method Method name. Should be the identifier for a component registry.
	 * @param array  $args   Method arguments.
	 * @return WP_Service_Worker_Registry|null Registry instance if valid method name, otherwise null.
	 */
	public function __call( $method, $args ) {
	}
	/**
	 * Registers a service worker script.
	 *
	 * @since 0.2
	 *
	 * @param string $handle Handle of the script.
	 * @param array  $args   {
	 *     Additional script arguments.
	 *
	 *     @type string|callable $src  Required. URL to the source in the WordPress install, or a callback that
	 *                                 returns the JS to include in the service worker.
	 *     @type array           $deps An array of registered item handles this item depends on. Default empty array.
	 * }
	 */
	public function register( $handle, $args = array() ) {
	}
	/**
	 * Gets all registered service worker scripts.
	 *
	 * @since 0.2
	 *
	 * @return array List of registered scripts.
	 */
	public function get_all() {
	}
	/**
	 * Process one registered script.
	 *
	 * @param string $handle Handle.
	 * @param bool   $group Group. Unused.
	 * @return void
	 */
	public function do_item( $handle, $group = false ) {
	}
	/**
	 * Get validated path to file.
	 *
	 * @param string $url Relative path.
	 * @return string|WP_Error
	 */
	public function get_validated_file_path( $url ) {
	}
}
/**
 * Class representing a registry for caching routes.
 *
 * @since 0.2
 */
class WP_Service_Worker_Caching_Routes implements WP_Service_Worker_Registry
{
    /**
     * Stale while revalidate caching strategy.
     *
     * @since 0.2
     * @var string
     */
    const STRATEGY_STALE_WHILE_REVALIDATE = 'StaleWhileRevalidate';
    /**
     * Cache first caching strategy.
     *
     * @since 0.2
     * @var string
     */
    const STRATEGY_CACHE_FIRST = 'CacheFirst';
    /**
     * Network first caching strategy.
     *
     * @since 0.2
     * @var string
     */
    const STRATEGY_NETWORK_FIRST = 'NetworkFirst';
    /**
     * Cache only caching strategy.
     *
     * @since 0.2
     * @var string
     */
    const STRATEGY_CACHE_ONLY = 'CacheOnly';
    /**
     * Network only caching strategy.
     *
     * @since 0.2
     * @var string
     */
    const STRATEGY_NETWORK_ONLY = 'NetworkOnly';
    /**
     * Registers a route.
     *
     * @param string $route      Route regular expression, without delimiters.
     * @param array  $args       {
     *                           Additional route arguments.
     *
     * @type string  $strategy   Required. Strategy, can be WP_Service_Worker_Caching_Routes::STRATEGY_STALE_WHILE_REVALIDATE, WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_FIRST,
     *                              WP_Service_Worker_Caching_Routes::STRATEGY_NETWORK_FIRST, WP_Service_Worker_Caching_Routes::STRATEGY_CACHE_ONLY,
     *                              WP_Service_Worker_Caching_Routes::STRATEGY_NETWORK_ONLY.
     * @type string  $cache_name Name to use for the cache.
     * @type array   $plugins    Array of plugins with configuration. The key of each plugin in the array must match the plugin's name.
     *                              See https://developers.google.com/web/tools/workbox/guides/using-plugins#workbox_plugins.
     * }
     * @since 0.2
     *
     */
    public function register($route, $args = array())
    {
    }
    /**
     * Gets all registered routes.
     *
     * @return array List of registered routes.
     * @since 0.2
     *
     */
    public function get_all()
    {
        return $this->routes;
    }
    /**
     * Prepare caching strategy args for export to JS.
     *
     * @param array $strategy_args Strategy args.
     * @return string JS IIFE which returns object for passing to registerRoute.
     * @since 0.2
     *
     */
    public static function prepare_strategy_args_for_js_export($strategy_args)
    {
    }
}
/**
 * Interface for classes that host a registry.
 *
 * @since 0.2
 */
interface WP_Service_Worker_Registry_Aware {
	/**
	 * Gets the registry.
	 *
	 * @return WP_Service_Worker_Registry Registry instance.
	 */
	public function get_registry();
}
/**
 * Class used to register service workers.
 *
 * @since 0.1
 *
 * @see   WP_Dependencies
 */
class WP_Service_Workers implements WP_Service_Worker_Registry_Aware
{
    /**
     * Param for service workers.
     *
     * @var string
     */
    const QUERY_VAR = 'wp_service_worker';
    /**
     * Scope for front.
     *
     * @var int
     */
    const SCOPE_FRONT = 1;
    /**
     * Scope for admin.
     *
     * @var int
     */
    const SCOPE_ADMIN = 2;
    /**
     * Scope for both front and admin.
     *
     * @var int
     */
    const SCOPE_ALL = 3;
    /**
     * Constructor.
     *
     * Instantiates the service worker scripts registry.
     */
    public function __construct()
    {
    }
    /**
     * Gets the service worker scripts registry.
     *
     * @return WP_Service_Worker_Scripts Scripts registry instance.
     */
    public function get_registry()
    {
    }
    /**
     * Get the current scope for the service worker request.
     *
     * @todo We don't really need this. A simple call to is_admin() is all that is required.
     * @return int Scope. Either SCOPE_FRONT or SCOPE_ADMIN.
     */
    public function get_current_scope()
    {
    }
    /**
     * Get service worker logic for scope.
     *
     * @see wp_service_worker_loaded()
     */
    public function serve_request()
    {
    }
}
/**
 * Get service worker URL by scope.
 *
 * @since 0.1
 *
 * @param int $scope Scope for which service worker to output. Can be WP_Service_Workers::SCOPE_FRONT (default) or WP_Service_Workers::SCOPE_ADMIN.
 * @return string Service Worker URL.
 */
function wp_get_service_worker_url( $scope = WP_Service_Workers::SCOPE_FRONT ) {}
