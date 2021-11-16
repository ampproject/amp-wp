<?php
/**
 * Class EntityRegistrantDetection.
 *
 * @since   2.2
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\EntityRegistrantDetection;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Block_Type;
use WP_Block_Type_Registry;
use WP_Hook;
use WP_Post_Type;
use WP_Taxonomy;

/**
 * Service to find the source where entities are registered.
 *
 * @since   2.2
 * @package AmpProject\AmpWP
 * @internal
 */
class EntityRegistrantDetectionManager implements Service, Registerable, Delayed, Conditional {

	/**
	 * Query var for passing nonce value.
	 *
	 * @var string
	 */
	const NONCE_QUERY_VAR = 'amp_entity_registrant_detection_nonce';

	/**
	 * Injector.
	 *
	 * @var Injector
	 */
	private $injector;

	/**
	 * List of registered post-type.
	 *
	 * @var array
	 */
	protected $post_types_source = [];

	/**
	 * List of registered taxonomy.
	 *
	 * @var array
	 */
	protected $taxonomies_source = [];

	/**
	 * List of registered shortcode.
	 *
	 * @var array
	 */
	protected $shortcodes_source = [];

	/**
	 * List of registered block type.
	 *
	 * @var array
	 */
	protected $blocks_source = [];

	/**
	 * Constructor.
	 *
	 * @param Injector $injector Injector.
	 */
	public function __construct( Injector $injector ) {

		$this->injector = $injector;
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {

		return 'plugins_loaded';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether need to enable service or not.
	 */
	public static function is_needed() {

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
		$nonce = isset( $_GET[ self::NONCE_QUERY_VAR ] ) ? wp_slash( $_GET[ self::NONCE_QUERY_VAR ] ) : '';

		return ( current_user_can( 'manage_options' ) && self::verify_nonce( $nonce ) );
	}

	/**
	 * Get nonce for performing for detecting entities registrants.
	 *
	 * The returned nonce is irrespective of the authenticated user.
	 *
	 * @return string Nonce.
	 */
	public static function get_nonce() {

		return wp_hash( self::NONCE_QUERY_VAR . wp_nonce_tick(), 'nonce' );
	}

	/**
	 * Verify nonce with the given value.
	 *
	 * @param string $value Value to verify nonce.
	 *
	 * @return bool True on success, Otherwise False.
	 */
	public static function verify_nonce( $value ) {

		return ( ! empty( $value ) ) ? hash_equals( $value, self::get_nonce() ) : false;
	}

	/**
	 * Add source of entity registrant.
	 *
	 * @param string          $entity_type Type of entity. e.g. post_type, taxonomy, block, shortcode.
	 * @param string|string[] $entities    Slug or list of slug of the entity.
	 * @param array           $source      Source detail where entity is registered..
	 *
	 * @return bool True on success, Otherwise False.
	 */
	public function add_source( $entity_type, $entities, $source ) {

		$allowed_entity_types = [
			'post_type',
			'taxonomy',
			'block',
			'shortcode',
		];

		if (
			empty( $entities ) ||
			empty( $entity_type ) ||
			! in_array( $entity_type, $allowed_entity_types, true )
		) {
			return false;
		}

		if ( ! is_array( $entities ) ) {
			$entities = [ $entities ];
		}

		foreach ( $entities as $entity ) {
			switch ( $entity_type ) {
				case 'post_type':
					$post_type = get_post_type_object( $entity );

					if ( empty( $post_type ) || ! $post_type instanceof WP_Post_Type ) {
						break;
					}

					$this->post_types_source[ $entity ] = [
						'name'         => $post_type->label,
						'slug'         => $post_type->name,
						'description'  => $post_type->description,
						'hierarchical' => $post_type->hierarchical,
						'source'       => $source,
					];
					break;
				case 'taxonomy':
					$taxonomy = get_taxonomy( $entity );

					if ( empty( $taxonomy ) || ! $taxonomy instanceof WP_Taxonomy ) {
						break;
					}

					$this->taxonomies_source[ $entity ] = [
						'name'         => $taxonomy->label,
						'slug'         => $taxonomy->name,
						'description'  => $taxonomy->description,
						'hierarchical' => $taxonomy->hierarchical,
						'source'       => $source,
					];
					break;
				case 'block':
					$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

					if ( empty( $block_types[ $entity ] ) || ! $block_types[ $entity ] instanceof WP_Block_Type ) {
						break;
					}

					$block_type = $block_types[ $entity ];

					$this->blocks_source[ $entity ] = [
						'name'        => $block_type->name,
						'title'       => $block_type->name,
						'description' => $block_type->description,
						'category'    => $block_type->category,
						'attributes'  => $block_type->get_attributes(),
						'is_dynamic'  => $block_type->is_dynamic(),
						'source'      => $source,
					];
					break;
				case 'shortcode':
					global $shortcode_tags;

					if ( ! isset( $shortcode_tags[ $entity ] ) ) {
						break;
					}

					$this->shortcodes_source[ $entity ] = [
						'tag'    => $entity,
						'source' => $source,
					];
					break;
			}
		}

		return true;
	}

	/**
	 * Get list of registered entities.
	 *
	 * @return array List of registered entities.
	 */
	public function get_registered_entities() {

		return [
			'post_types' => $this->post_types_source,
			'taxonomies' => $this->taxonomies_source,
			'blocks'     => $this->blocks_source,
			'shortcodes' => $this->shortcodes_source,
		];
	}

	/**
	 * Adds hooks.
	 */
	public function register() {

		$int_min = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound

		add_action( 'all', [ $this, 'wrap_hook_callbacks' ], $int_min );
	}

	/**
	 * Wrap action/filter callback to given hook to check if callback registering any entity or not.
	 *
	 * @global WP_Hook[] $wp_filter
	 *
	 * @param string $hook Hook name for action or filter.
	 *
	 * @return void
	 */
	public function wrap_hook_callbacks( $hook ) {

		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook ] ) || 'shutdown' === $hook ) {
			return;
		}

		foreach ( $wp_filter[ $hook ]->callbacks as $priority => &$callbacks ) {
			foreach ( $callbacks as &$callback ) {
				if ( ! $callback['function'] instanceof CallbackWrapper ) {
					$callback['function'] = $this->wrapped_callback(
						array_merge(
							$callback,
							compact( 'priority', 'hook' )
						)
					);
				}
			}
		}

	}

	/**
	 * Wrap the given callable function.
	 *
	 * @param array $callback [
	 *     The callback data.
	 *     @type callable $function
	 *     @type int      $accepted_args
	 *     @type int      $priority
	 *     @type int      $hook
	 * ]
	 *
	 * @return CallbackWrapper Instance of CallbackWrapper.
	 */
	protected function wrapped_callback( $callback ) {

		return $this->injector->make( CallbackWrapper::class, compact( 'callback' ) );
	}
}
