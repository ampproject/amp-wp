<?php
/**
 * Class CallbackWrapper.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\EntityRegistrantDetection;

use AmpProject\AmpWP\DevTools\CallbackReflection;
use ArrayAccess;
use WP_Block_Type_Registry;

/**
 * Callback wrapper for EntityRegistrantDetection.
 *
 * @since   2.2
 * @package AmpProject\AmpWP
 * @internal
 */
class CallbackWrapper implements ArrayAccess {

	/**
	 * List of default entities.
	 *
	 * @var array [
	 *     @type array $post_type List of default post types slug.
	 *     @type array $taxonomy  List of default taxonomy slug.
	 *     @type array $block     List of default block type.
	 *     @type array $shortcode List of default shortcode.
	 * ]
	 */
	const DEFAULT_ENTITIES = [
		'post_type' => [
			'post',
			'page',
			'attachment',
			'revision',
			'nav_menu_item',
		],
		'taxonomy'  => [
			'category',
			'post_tag',
			'nav_menu',
			'post_format',
		],
		'block'     => [
			'core/archives',
			'core/block',
			'core/calendar',
			'core/categories',
			'core/file',
			'core/latest-comments',
			'core/latest-posts',
			'core/legacy-widget',
			'core/loginout',
			'core/page-list',
			'core/post-content',
			'core/post-date',
			'core/post-excerpt',
			'core/post-featured-image',
			'core/post-terms',
			'core/post-title',
			'core/post-template',
			'core/query',
			'core/query-pagination',
			'core/query-pagination-next',
			'core/query-pagination-numbers',
			'core/query-pagination-previous',
			'core/query-title',
			'core/rss',
			'core/search',
			'core/shortcode',
			'core/site-tagline',
			'core/site-logo',
			'core/site-title',
			'core/social-link',
			'core/tag-cloud',
			'core/audio',
			'core/button',
			'core/buttons',
			'core/code',
			'core/column',
			'core/columns',
			'core/cover',
			'core/embed',
			'core/freeform',
			'core/gallery',
			'core/group',
			'core/heading',
			'core/html',
			'core/image',
			'core/list',
			'core/media-text',
			'core/missing',
			'core/more',
			'core/nextpage',
			'core/paragraph',
			'core/preformatted',
			'core/pullquote',
			'core/quote',
			'core/separator',
			'core/social-links',
			'core/spacer',
			'core/table',
			'core/text-columns',
			'core/verse',
			'core/video',
		],
		'shortcode' => [
			'caption',
			'gallery',
			'audio',
			'video',
			'playlist',
			'embed',
		],
	];

	/**
	 * Callback data.
	 *
	 * @var array
	 */
	protected $callback;

	/**
	 * Source of the function.
	 *
	 * @var array
	 */
	protected $source;

	/**
	 * List of registered entities by callback.
	 *
	 * @var array [
	 *     @type array $post_type List of registered post types slug.
	 *     @type array $taxonomy  List of registered taxonomy slug.
	 *     @type array $block     List of registered block type.
	 *     @type array $shortcode List of registered shortcode.
	 * ]
	 */
	protected $registered_entities;

	/**
	 * Instance of EntityRegistrantDetectionManager.
	 *
	 * @var EntityRegistrantDetectionManager
	 */
	protected $detection_manager;

	/**
	 * Callback reflection.
	 *
	 * @var CallbackReflection
	 */
	protected $callback_reflection;

	/**
	 * AMP_Validation_Callback_Wrapper constructor.
	 *
	 * @param EntityRegistrantDetectionManager $detection_manager   Instance of EntityRegistrantDetectionManager.
	 * @param CallbackReflection               $callback_reflection Instance of CallbackReflection.
	 * @param array                            $callback            [
	 *     The callback data.
	 *     @type callable $function
	 *     @type int      $accepted_args
	 *     @type int      $priority
	 *     @type string   $hook
	 * ]
	 */
	public function __construct( EntityRegistrantDetectionManager $detection_manager, CallbackReflection $callback_reflection, $callback ) {

		$this->detection_manager   = $detection_manager;
		$this->callback_reflection = $callback_reflection;
		$this->callback            = $callback;
		$this->registered_entities = [
			'post_type' => [],
			'taxonomy'  => [],
			'block'     => [],
			'shortcode' => [],
		];
	}

	/**
	 * Get callback function.
	 *
	 * @return callable
	 */
	public function get_callback_function() {

		return $this->callback['function'];
	}

	/**
	 * Invoke wrapped callback.
	 *
	 * @param array ...$args Args.
	 *
	 * @return mixed Response.
	 */
	public function __invoke( ...$args ) {

		$this->prepare();

		$result = call_user_func_array(
			$this->get_callback_function(),
			array_slice( $args, 0, (int) $this->callback['accepted_args'] )
		);

		$this->finalize();

		return $result;
	}

	/**
	 * Set the source of a callback function.
	 *
	 * @return void
	 */
	protected function set_source() {

		$this->source = $this->callback_reflection->get_source( $this->get_callback_function() );

		unset( $this->source['reflection'] );

		if ( $this->callback['hook'] ) {
			$this->source['hook'] = $this->callback['hook'];
		}

		if ( $this->callback['priority'] ) {
			$this->source['priority'] = $this->callback['priority'];
		}
	}

	/**
	 * Collect the currently registered entities before executing the original function.
	 *
	 * @return void
	 */
	protected function prepare() {

		$this->registered_entities = $this->get_registered_entities();
	}

	/**
	 * Find the difference of registered entities between before and after executing function.
	 *
	 * @return void
	 */
	protected function finalize() {

		$final_registered_entities = $this->get_registered_entities();

		foreach ( $this->registered_entities as $entity_type => $entities ) {

			$different = array_diff(
				$final_registered_entities[ $entity_type ],
				$this->registered_entities[ $entity_type ],
				self::DEFAULT_ENTITIES[ $entity_type ]
			);
			$different = array_values( $different );

			if ( $different && empty( $this->source ) ) {
				$this->set_source();
			}

			$this->registered_entities[ $entity_type ] = $different;

			$this->detection_manager->add_source( $entity_type, $different, $this->source );
		}

	}

	/**
	 * Collect the currently registered entities.
	 *
	 * @return array [
	 *     @type array $post_type List of registered post types slug.
	 *     @type array $taxonomy  List of registered taxonomy slug.
	 *     @type array $block     List of registered block type.
	 *     @type array $shortcode List of registered shortcode.
	 * ]
	 */
	protected function get_registered_entities() {

		global $shortcode_tags;

		$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

		return [
			'post_type' => array_values( get_post_types() ),
			'taxonomy'  => array_values( get_taxonomies() ),
			'block'     => array_keys( $block_types ),
			'shortcode' => array_keys( $shortcode_tags ),
		];
	}

	/**
	 * Offset exists.
	 *
	 * @param mixed $offset Offset.
	 *
	 * @return bool Exists.
	 */
	public function offsetExists( $offset ) {

		if ( ! is_array( $this->callback ) ) {
			return false;
		}

		return isset( $this->callback[ $offset ] );
	}

	/**
	 * Offset get.
	 *
	 * @param mixed $offset Offset.
	 *
	 * @return mixed|null Value.
	 */
	public function offsetGet( $offset ) {

		if ( is_array( $this->callback ) && isset( $this->callback[ $offset ] ) ) {
			return $this->callback[ $offset ];
		}

		return null;
	}

	/**
	 * Offset set.
	 *
	 * @param mixed $offset Offset.
	 * @param mixed $value  Value.
	 */
	public function offsetSet( $offset, $value ) {

		if ( ! is_array( $this->callback ) ) {
			return;
		}

		if ( is_null( $offset ) ) {
			$this->callback[] = $value;
		} else {
			$this->callback[ $offset ] = $value;
		}
	}

	/**
	 * Offset unset.
	 *
	 * @param mixed $offset Offset.
	 */
	public function offsetUnset( $offset ) {

		if ( ! is_array( $this->callback ) ) {
			return;
		}

		unset( $this->callback[ $offset ] );
	}
}
