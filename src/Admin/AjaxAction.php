<?php
/**
 * Class AjaxAction.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

/**
 * Base class to define a new AJAX action.
 *
 * @package AmpProject\AmpWP
 */
final class AjaxAction {


	/**
	 * The AJAX action should be accessible by any visitor.
	 *
	 * @var int
	 */
	const ACCESS_ANY = 0;

	/**
	 * The AJAX action should only be accessible by authenticated users.
	 *
	 * @var int
	 */
	const ACCESS_USER = 1;

	/**
	 * The AJAX action should only be accessible by unauthenticated visitors.
	 *
	 * @var int
	 */
	const ACCESS_NO_USER = 2;

	/**
	 * The AJAX handler listens on both the backend and the frontend.
	 *
	 * @var int
	 */
	const SCOPE_BOTH = 0;

	/**
	 * The AJAX handler listens on the backend only.
	 *
	 * @var int
	 */
	const SCOPE_BACKEND = 1;

	/**
	 * The AJAX handler listens on the frontend only.
	 *
	 * @var int
	 */
	const SCOPE_FRONTEND = 2;

	/**
	 * Action to use for enqueueing the JS logic at the frontend.
	 *
	 * @var string
	 */
	const FRONTEND_ENQUEUE_ACTION = 'wp_enqueue_scripts';

	/**
	 * Action to use for enqueueing the JS logic at the backend.
	 *
	 * @var string
	 */
	const BACKEND_ENQUEUE_ACTION = 'admin_enqueue_scripts';

	/**
	 * Access setting for the AJAX handler.
	 *
	 * @var int
	 */
	private $access;

	/**
	 * Scope setting to define where the AJAX handler is listening.
	 *
	 * @var int
	 */
	private $scope;

	/**
	 * Name of the action to identify incoming AJAX requests.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Callback to execute when an AJAX request was received.
	 *
	 * @var callable
	 */
	private $callback;

	/**
	 * Selector to attach the click handler to.
	 *
	 * @var string
	 */
	private $selector;

	/**
	 * Instantiate an AjaxAction instance.
	 *
	 * @param string   $action   Name of the action to identify the AJAX request.
	 * @param callable $callback Callback function to call when the AJAX request came in.
	 * @param string   $selector Optional. Selector to attach the click event to. Leave empty to skip the click handler.
	 * @param int      $access   Optional. Defaults to restricting the AJAX action to authenticated users only.
	 * @param int      $scope    Optional. Scope of where the AJAX handler is listening. Defaults to backend only.
	 */
	public function __construct(
		$action,
		callable $callback,
		$selector = '',
		$access = self::ACCESS_USER,
		$scope = self::SCOPE_BACKEND
	) {
		if ( ! is_int( $access ) || $access < self::ACCESS_ANY || $access > self::ACCESS_NO_USER ) {
			$access = self::ACCESS_USER;
		}

		if ( ! is_int( $scope ) || $scope < self::SCOPE_BOTH || $scope > self::SCOPE_FRONTEND ) {
			$scope = self::SCOPE_BACKEND;
		}

		$this->action   = $action;
		$this->callback = $callback;
		$this->selector = $selector;
		$this->access   = $access;
		$this->scope    = $scope;
	}

	/**
	 * Register the AJAX action with the WordPress system.
	 */
	public function register() {
		if ( in_array( $this->access, [ self::ACCESS_USER, self::ACCESS_ANY ], true ) ) {
			add_action( $this->get_action( self::ACCESS_USER ), $this->callback );
		}

		if ( in_array( $this->access, [ self::ACCESS_NO_USER, self::ACCESS_ANY ], true ) ) {
			add_action( $this->get_action( self::ACCESS_NO_USER ), $this->callback );
		}

		if ( in_array( $this->scope, [ self::SCOPE_FRONTEND, self::SCOPE_BOTH ], true )
			&& ! has_action( static::FRONTEND_ENQUEUE_ACTION, [ $this, 'register_ajax_script' ] ) ) {
			add_action( static::FRONTEND_ENQUEUE_ACTION, [ $this, 'register_ajax_script' ] );
		}

		if ( in_array( $this->scope, [ self::SCOPE_BACKEND, self::SCOPE_BOTH ], true )
			&& ! has_action( static::BACKEND_ENQUEUE_ACTION, [ $this, 'register_ajax_script' ] ) ) {
			add_action( static::BACKEND_ENQUEUE_ACTION, [ $this, 'register_ajax_script' ] );
		}
	}

	/**
	 * Register the AJAX logic.
	 */
	public function register_ajax_script() {
		if ( empty( $this->selector ) ) {
			return;
		}

		if ( self::SCOPE_FRONTEND === $this->scope && is_admin() ) {
			return;
		}

		if ( self::SCOPE_BACKEND === $this->scope && ! is_admin() ) {
			return;
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_start();

		?>
		<script>
			( function ( $ ) {
				$( function () {
					var admin_ajax_url = '<?php echo addslashes( admin_url( 'admin-ajax.php' ) ); ?>';
					var selector       = '<?php echo addslashes( $this->selector ); ?>';
					setTimeout( function () {
						( document.querySelectorAll( selector ) || [] )
							.forEach( ( element ) => {
								element.addEventListener( 'click', function ( event ) {
									event.preventDefault();
									$.ajax( {
										type: "post",
										dataType: "json",
										url: admin_ajax_url,
										data: { action: '<?php echo addslashes( $this->action ); ?>' }
									}, { action: '<?php echo addslashes( $this->action ); ?>' } )
										.done( function () {
											element.classList.remove( 'ajax-failure' );
											element.classList.add( 'ajax-success' )
										} )
										.fail( function () {
											element.classList.remove( 'ajax-success' );
											element.classList.add( 'ajax-failure' )
										} );
								} );
							} );
					}, 1000 );
				} );
			} )( jQuery );
		</script>
		<?php

		$script = ob_get_clean();
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		wp_enqueue_script( 'jquery' );
		wp_add_inline_script( 'jquery', $script );
	}

	/**
	 * Get the action name to use for registering the AJAX handler.
	 *
	 * @param int $access The access setting to use for the action.
	 *
	 * @return string WordPress action to register the AJAX handler against.
	 */
	private function get_action( $access ) {
		$prefix = 'wp_ajax_';

		if ( self::ACCESS_NO_USER === $access ) {
			$prefix .= 'nopriv_';
		}

		return $prefix . $this->action;
	}
}
