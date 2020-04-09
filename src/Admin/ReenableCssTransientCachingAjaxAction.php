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
final class ReenableCssTransientCachingAjaxAction {

	/**
	 * Action to use for enqueueing the JS logic at the backend.
	 *
	 * @var string
	 */
	const BACKEND_ENQUEUE_ACTION = 'admin_enqueue_scripts';

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
	 */
	public function __construct(
		$action,
		callable $callback,
		$selector = ''
	) {
		$this->action   = $action;
		$this->callback = $callback;
		$this->selector = $selector;
	}

	/**
	 * Register the AJAX action with the WordPress system.
	 */
	public function register() {
		add_action( 'wp_ajax_' . $this->action, $this->callback );
		add_action( static::BACKEND_ENQUEUE_ACTION, [ $this, 'register_ajax_script' ] );
	}

	/**
	 * Register the AJAX logic.
	 */
	public function register_ajax_script() {
		if ( empty( $this->selector ) ) {
			return;
		}

		$selector = wp_json_encode( $this->selector );
		$action   = wp_json_encode( $this->action );

		$script = <<< JS_SCRIPT
;( function () {
    var selector = {$selector};
    setTimeout( function () {
        ( document.querySelectorAll( selector ) || [] )
            .forEach( ( element ) => {
                element.addEventListener( 'click', function ( event ) {
                    event.preventDefault();
                    wp.ajax.post( {$action}, {} )
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
} )();
JS_SCRIPT;

		wp_enqueue_script( 'wp-util' );
		wp_add_inline_script( 'wp-util', $script );
	}
}
