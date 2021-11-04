<?php
/**
 * Class CallbackWrapper.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\EntityRegistrantDetection;

use AmpProject\AmpWP\Services;
use WP_Block_Type_Registry;

/**
 * Callback wrapper for EntityRegistrantDetection.
 *
 * @since   2.2
 * @package AmpProject\AmpWP
 * @internal
 */
class CallbackWrapper {

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
	 * AMP_Validation_Callback_Wrapper constructor.
	 *
	 * @param array $callback [
	 *     The callback data.
	 *     @type callable $function
	 *     @type int      $accepted_args
	 * ]
	 */
	public function __construct( $callback ) {

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

		$callback_reflection = Services::get( 'dev_tools.callback_reflection' );
		$this->source        = $callback_reflection->get_source( $this->get_callback_function() );

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
				$this->registered_entities[ $entity_type ]
			);

			if ( $different && empty( $this->source ) ) {
				$this->set_source();
			}

			$this->registered_entities[ $entity_type ] = $different;

			EntityRegistrantDetectionManager::add_source( $entity_type, $different, $this->source );
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
}
