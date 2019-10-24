<?php
/**
 * Class AMP_Validation_Callback_Wrapper
 *
 * @package AMP
 */

/**
 * Class AMP_Validation_Callback_Wrapper
 *
 * @since 1.2.1
 */
class AMP_Validation_Callback_Wrapper implements ArrayAccess {

	/**
	 * Callback data.
	 *
	 * @var array
	 */
	protected $callback;

	/**
	 * AMP_Validation_Callback_Wrapper constructor.
	 *
	 * @param array $callback {
	 *     The callback data.
	 *
	 *     @type callable $function
	 *     @type int      $accepted_args
	 *     @type array    $source
	 * }
	 */
	public function __construct( $callback ) {
		$this->callback = $callback;
	}

	/**
	 * Invoke wrapped callback.
	 *
	 * @return mixed
	 */
	public function __invoke() {
		global $wp_styles, $wp_scripts;

		$function      = $this->callback['function'];
		$accepted_args = $this->callback['accepted_args'];
		$args          = func_get_args();

		$before_styles_enqueued = [];
		if ( isset( $wp_styles, $wp_styles->queue ) ) {
			$before_styles_enqueued = $wp_styles->queue;
		}
		$before_scripts_enqueued = [];
		if ( isset( $wp_scripts, $wp_scripts->queue ) ) {
			$before_scripts_enqueued = $wp_scripts->queue;
		}

		$is_filter = isset( $this->callback['source']['hook'] ) && ! did_action( $this->callback['source']['hook'] );

		// Wrap the markup output of (action) hooks in source comments.
		AMP_Validation_Manager::$hook_source_stack[] = $this->callback['source'];
		$has_buffer_started                          = false;
		if ( ! $is_filter && AMP_Validation_Manager::can_output_buffer() ) {
			$has_buffer_started = ob_start( [ 'AMP_Validation_Manager', 'wrap_buffer_with_source_comments' ] );
		}
		$result = call_user_func_array( $function, array_slice( $args, 0, (int) $accepted_args ) );
		if ( $has_buffer_started ) {
			ob_end_flush();
		}
		array_pop( AMP_Validation_Manager::$hook_source_stack );

		// Keep track of which source enqueued the styles.
		if ( isset( $wp_styles, $wp_styles->queue ) ) {
			foreach ( array_diff( $wp_styles->queue, $before_styles_enqueued ) as $handle ) {
				AMP_Validation_Manager::$enqueued_style_sources[ $handle ][] = array_merge(
					$this->callback['source'],
					[ 'dependency_type' => 'style' ],
					compact( 'handle' )
				);
			}
		}

		// Keep track of which source enqueued the scripts, and immediately report validity.
		if ( isset( $wp_scripts, $wp_scripts->queue ) ) {
			foreach ( array_diff( $wp_scripts->queue, $before_scripts_enqueued ) as $queued_handle ) {
				$handles = [ $queued_handle ];

				// Account for case where registered script is a placeholder for a set of scripts (e.g. jquery).
				if ( isset( $wp_scripts->registered[ $queued_handle ] ) && false === $wp_scripts->registered[ $queued_handle ]->src ) {
					$handles = array_merge( $handles, $wp_scripts->registered[ $queued_handle ]->deps );
				}

				foreach ( $handles as $handle ) {
					AMP_Validation_Manager::$enqueued_script_sources[ $handle ][] = array_merge(
						$this->callback['source'],
						[ 'dependency_type' => 'script' ],
						compact( 'handle' )
					);
				}
			}
		}

		return $result;
	}

	/**
	 * Offset set.
	 *
	 * @param mixed $offset Offset.
	 * @param mixed $value  Value.
	 */
	public function offsetSet( $offset, $value ) {
		if ( ! is_array( $this->callback['function'] ) ) {
			return;
		}
		if ( is_null( $offset ) ) {
			$this->callback['function'][] = $value;
		} else {
			$this->callback['function'][ $offset ] = $value;
		}
	}

	/**
	 * Offset exists.
	 *
	 * @param mixed $offset Offset.
	 * @return bool Exists.
	 */
	public function offsetExists( $offset ) {
		if ( ! is_array( $this->callback['function'] ) ) {
			return false;
		}
		return isset( $this->callback['function'][ $offset ] );
	}

	/**
	 * Offset unset.
	 *
	 * @param mixed $offset Offset.
	 */
	public function offsetUnset( $offset ) {
		if ( ! is_array( $this->callback['function'] ) ) {
			return;
		}
		unset( $this->callback['function'][ $offset ] );
	}

	/**
	 * Offset get.
	 *
	 * @param mixed $offset Offset.
	 * @return mixed|null Value.
	 */
	public function offsetGet( $offset ) {
		if ( is_array( $this->callback['function'] ) && isset( $this->callback['function'][ $offset ] ) ) {
			return $this->callback['function'][ $offset ];
		}
		return null;
	}
}
