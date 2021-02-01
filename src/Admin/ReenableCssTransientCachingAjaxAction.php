<?php
/**
 * Class ReenableCssTransientCachingAjaxAction.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;

/**
 * Base class to define a new AJAX action.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class ReenableCssTransientCachingAjaxAction implements Service, Registerable {

	/**
	 * Action to use for enqueueing the JS logic at the backend.
	 *
	 * @var string
	 */
	const BACKEND_ENQUEUE_ACTION = 'admin_enqueue_scripts';

	/**
	 * AJAX action name to use.
	 *
	 * @var string
	 */
	const AJAX_ACTION = 'amp_reenable_css_transient_caching';

	/**
	 * Selector to attach the click handler to.
	 *
	 * @var string
	 */
	const SELECTOR = 'a.reenable-css-transient-caching';

	/**
	 * Register the AJAX action with the WordPress system.
	 */
	public function register() {
		add_action( static::BACKEND_ENQUEUE_ACTION, [ $this, 'register_ajax_script' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'reenable_css_transient_caching' ] );
	}

	/**
	 * Register the AJAX logic.
	 *
	 * @param string $hook_suffix Hook suffix to identify from what admin page the call is coming from.
	 */
	public function register_ajax_script( $hook_suffix ) {
		if ( 'site-health.php' !== $hook_suffix ) {
			return;
		}

		$script = <<< 'JS_SCRIPT'
;( function () {
	window.addEventListener( 'DOMContentLoaded', ( event ) => {
		var selector = SELECTOR;
		( document.querySelectorAll( selector ) || [] )
			.forEach( ( element ) => {
				element.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					if ( element.classList.contains( 'disabled' ) ) {
						return;
					}
					wp.ajax.post( ACTION, ARGUMENTS )
						.done( function () {
							element.classList.remove( 'ajax-failure' );
							element.classList.add( 'ajax-success' )
							element.classList.add( 'disabled' )
						} )
						.fail( function () {
							element.classList.remove( 'ajax-success' );
							element.classList.add( 'ajax-failure' )
							element.classList.add( 'disabled' )
						} );
				} );
			} );
	} );
} )();
JS_SCRIPT;

		$replacements = array_map(
			'wp_json_encode',
			[
				'SELECTOR'  => self::SELECTOR,
				'ACTION'    => self::AJAX_ACTION,
				'ARGUMENTS' => [ 'nonce' => wp_create_nonce( self::AJAX_ACTION ) ],
			]
		);

		$script = str_replace(
			array_keys( $replacements ),
			array_values( $replacements ),
			$script
		);

		wp_enqueue_script( 'wp-util' );
		wp_add_inline_script( 'wp-util', $script );
	}

	/**
	 * Re-enable the CSS Transient caching.
	 *
	 * This is triggered via an AJAX call from the Site Health panel.
	 */
	public function reenable_css_transient_caching() {
		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized.', 401 );
		}

		$result = AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, false );

		if ( false === $result ) {
			wp_send_json_error( 'CSS transient caching could not be re-enabled.', 500 );
		}

		wp_send_json_success( 'CSS transient caching was re-enabled.', 200 );
	}
}
