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
	 * Prepare for invocation.
	 *
	 * @since 1.5
	 *
	 * @param array ...$args Arguments.
	 * @return array Preparation data.
	 *
	 * @global WP_Scripts|null $wp_scripts
	 * @global WP_Styles|null  $wp_styles
	 */
	protected function prepare( ...$args ) {
		global $wp_scripts, $wp_styles;

		if ( isset( $wp_styles ) && $wp_styles instanceof WP_Styles ) {
			$styles = $wp_styles;
		} elseif ( isset( $args[0] ) && $args[0] instanceof WP_Styles ) {
			$styles = $args[0]; // This is a special case for wp_default_styles().
		} else {
			$styles = null;
		}
		if ( isset( $wp_scripts ) && $wp_scripts instanceof WP_Scripts ) {
			$scripts = $wp_scripts;
		} elseif ( isset( $args[0] ) && $args[0] instanceof WP_Scripts ) {
			$scripts = $args[0]; // This is a special case for wp_default_scripts().
		} else {
			$scripts = null;
		}

		if ( isset( $styles ) ) {
			$before_styles_enqueued   = $styles->queue;
			$before_styles_registered = array_keys( $styles->registered );
			$before_styles_extras     = wp_list_pluck( $styles->registered, 'extra' );
		} else {
			$before_styles_registered = [];
			$before_styles_enqueued   = [];
			$before_styles_extras     = [];
		}
		if ( isset( $scripts ) ) {
			$before_scripts_enqueued   = $scripts->queue;
			$before_scripts_registered = array_keys( $scripts->registered );
			$before_scripts_extras     = wp_list_pluck( $scripts->registered, 'extra' );
		} else {
			$before_scripts_registered = [];
			$before_scripts_enqueued   = [];
			$before_scripts_extras     = [];
		}

		$is_filter = isset( $this->callback['source']['hook'] ) && ! did_action( $this->callback['source']['hook'] );

		// Wrap the markup output of (action) hooks in source comments.
		AMP_Validation_Manager::$hook_source_stack[] = $this->callback['source'];
		if ( ! $is_filter && AMP_Validation_Manager::can_output_buffer() ) {
			$has_buffer_started = ob_start( [ 'AMP_Validation_Manager', 'wrap_buffer_with_source_comments' ] );
		} else {
			$has_buffer_started = false;
		}

		return compact(
			'styles',
			'before_styles_registered',
			'before_styles_enqueued',
			'before_styles_extras',
			'scripts',
			'before_scripts_registered',
			'before_scripts_enqueued',
			'before_scripts_extras',
			'is_filter',
			'has_buffer_started'
		);
	}

	/**
	 * Invoke wrapped callback.
	 *
	 * @param array ...$args Args.
	 * @return mixed Response.
	 */
	public function __invoke( ...$args ) {
		$preparation = $this->prepare( ...$args );

		$result = call_user_func_array(
			$this->callback['function'],
			array_slice( $args, 0, (int) $this->callback['accepted_args'] )
		);

		$this->finalize( $preparation );

		return $result;
	}

	/**
	 * Invoke wrapped callback with first argument passed by reference.
	 *
	 * @since 1.5
	 *
	 * @param object $first_arg     First argument.
	 * @param array  ...$other_args Other arguments.
	 * @return mixed
	 */
	public function invoke_with_first_ref_arg( &$first_arg, ...$other_args ) {
		$preparation = $this->prepare( $first_arg, ...$other_args );

		$result = $this->callback['function'](
			$first_arg,
			...array_slice( $other_args, 0, (int) $this->callback['accepted_args'] - 1 )
		);

		$this->finalize( $preparation );

		return $result;
	}

	/**
	 * Finalize invocation.
	 *
	 * @since 1.5
	 *
	 * @param array $preparation Preparation data.
	 *
	 * @global WP_Scripts|null $wp_scripts
	 * @global WP_Styles|null  $wp_styles
	 */
	protected function finalize( array $preparation ) {
		// If a script/style was registered or enqueued in the callback, these should now be defined if not defined already.
		global $wp_scripts, $wp_styles;

		if ( $preparation['has_buffer_started'] ) {
			ob_end_flush();
		}
		array_pop( AMP_Validation_Manager::$hook_source_stack );

		if ( isset( $wp_styles ) && $wp_styles instanceof WP_Styles ) {
			$styles = $wp_styles;
		} elseif ( isset( $preparation['styles'] ) && $preparation['styles'] instanceof WP_Styles ) {
			$styles = $preparation['styles'];
		}
		if ( isset( $styles ) ) {
			$this->finalize_styles(
				$styles,
				$preparation['before_styles_registered'],
				$preparation['before_styles_enqueued'],
				$preparation['before_styles_extras']
			);
		}

		if ( isset( $wp_scripts ) && $wp_scripts instanceof WP_Scripts ) {
			$scripts = $wp_scripts;
		} elseif ( isset( $preparation['scripts'] ) && $preparation['scripts'] instanceof WP_Scripts ) {
			$scripts = $preparation['scripts'];
		}
		if ( isset( $scripts ) ) {
			$this->finalize_scripts(
				$scripts,
				$preparation['before_scripts_registered'],
				$preparation['before_scripts_enqueued'],
				$preparation['before_scripts_extras']
			);
		}
	}

	/**
	 * Finalize styles after invocation.
	 *
	 * @since 1.5
	 *
	 * @param WP_Styles $wp_styles         Styles registry.
	 * @param string[]  $before_registered Style handles registered before invocation.
	 * @param string[]  $before_enqueued   Style handles enqueued before invocation.
	 * @param array[]   $before_extras     Style extras before invocation.
	 */
	protected function finalize_styles( WP_Styles $wp_styles, array $before_registered, array $before_enqueued, array $before_extras ) {

		// Keep track of which source enqueued the styles.
		// Note: Only the first time a style is registered/enqueued will be detected.
		$added_handles = array_unique(
			array_merge(
				array_diff( $wp_styles->queue, $before_enqueued ),
				array_diff( array_keys( $wp_styles->registered ), $before_registered )
			)
		);
		foreach ( $added_handles as $handle ) {
			AMP_Validation_Manager::$enqueued_style_sources[ $handle ][] = array_merge(
				$this->callback['source'],
				[ 'dependency_type' => 'style' ],
				compact( 'handle' )
			);
		}

		// Keep track of which source added an inline style.
		foreach ( $wp_styles->registered as $handle => $dependency ) {
			if ( empty( $dependency->extra['after'] ) ) {
				continue;
			}

			$additions = array_diff(
				array_filter( $dependency->extra['after'] ),
				array_filter( isset( $before_extras[ $handle ]['after'] ) ? (array) $before_extras[ $handle ]['after'] : [] )
			);
			foreach ( $additions as $addition ) {
				AMP_Validation_Manager::$extra_style_sources[ $handle ][ $addition ][] = array_merge(
					$this->callback['source'],
					[
						'dependency_type' => 'style',
						'extra_key'       => 'after',
						'text'            => $addition,
					],
					compact( 'handle' )
				);
			}
		}
	}

	/**
	 * Finalize scripts after invocation.
	 *
	 * @since 1.5
	 *
	 * @param WP_Scripts $wp_scripts        Scripts registry.
	 * @param string[]   $before_registered Script handles registered before invocation.
	 * @param string[]   $before_enqueued   Script handles enqueued before invocation.
	 * @param array[]    $before_extras     Script extras before invocation.
	 */
	protected function finalize_scripts( WP_Scripts $wp_scripts, array $before_registered, array $before_enqueued, array $before_extras ) {

		// Keep track of which source enqueued the scripts.
		// Note: Only the first time a script is registered/enqueued will be detected.
		$added_handles = array_unique(
			array_merge(
				array_diff( $wp_scripts->queue, $before_enqueued ),
				array_diff( array_keys( $wp_scripts->registered ), $before_registered )
			)
		);
		foreach ( $added_handles as $added_handle ) {
			$handles = [ $added_handle ];

			// Account for case where registered script is a placeholder for a set of scripts (e.g. jquery).
			if ( isset( $wp_scripts->registered[ $added_handle ] ) && false === $wp_scripts->registered[ $added_handle ]->src ) {
				$handles = array_merge( $handles, $wp_scripts->registered[ $added_handle ]->deps );
			}

			foreach ( $handles as $handle ) {
				AMP_Validation_Manager::$enqueued_script_sources[ $handle ][] = array_merge(
					$this->callback['source'],
					[ 'dependency_type' => 'script' ],
					compact( 'handle' )
				);
			}
		}

		// Keep track of which source added inline scripts.
		foreach ( $wp_scripts->registered as $handle => $dependency ) {
			if ( empty( $dependency->extra ) ) {
				continue;
			}
			foreach ( [ 'data', 'before', 'after' ] as $key ) {
				if ( empty( $dependency->extra[ $key ] ) ) {
					continue;
				}

				if ( empty( $before_extras[ $handle ][ $key ] ) ) {
					$before = [];
				} elseif ( 'data' === $key ) {
					// Undo concatenation done by \WP_Scripts::localize().
					$before = explode( "\n", $before_extras[ $handle ][ $key ] );
				} else {
					$before = $before_extras[ $handle ][ $key ];
				}

				if ( empty( $dependency->extra[ $key ] ) ) {
					$after = [];
				} elseif ( 'data' === $key ) {
					// Undo concatenation done by \WP_Scripts::localize().
					$after = explode( "\n", $dependency->extra[ $key ] );
				} else {
					$after = $dependency->extra[ $key ];
				}

				$additions = array_diff(
					array_filter( $after ),
					array_filter( $before )
				);
				foreach ( $additions as $addition ) {
					AMP_Validation_Manager::$extra_script_sources[ $addition ][] = array_merge(
						$this->callback['source'],
						[
							'dependency_type' => 'script',
							'extra_key'       => $key,
							'text'            => $addition,
						],
						compact( 'handle' )
					);
				}
			}
		}
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
