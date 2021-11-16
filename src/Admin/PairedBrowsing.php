<?php
/**
 * Class PairedBrowsing.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Validation_Manager;
use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\PairedRouting;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Services;
use AmpProject\DevMode;
use WP_Post;
use WP_Admin_Bar;
use AmpProject\AmpWP\DevTools\UserAccess;

/**
 * Managing the paired browsing app.
 *
 * @since 2.1
 * @internal
 */
final class PairedBrowsing implements Service, Registerable, Conditional, Delayed, HasRequirements {

	/**
	 * Query var for requests to open the app.
	 *
	 * @var string
	 */
	const APP_QUERY_VAR = 'amp-paired-browsing';

	/**
	 * DevTools User Access.
	 *
	 * @var UserAccess
	 */
	public $dev_tools_user_access;

	/**
	 * Paired Routing.
	 *
	 * @var PairedRouting
	 */
	public $paired_routing;

	/**
	 * Get the action to use for registering the service.
	 *
	 * This action needs to run late enough in the frontend and the backend for the user to be logged-in and for
	 * AMP dev mode to be opted-in to.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'wp_loaded';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return (
			Services::get( 'dependency_support' )->has_support()
			&&
			(
				AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
				||
				(
					AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
					&&
					get_stylesheet() === AMP_Options_Manager::get_option( Option::READER_THEME )
				)
			)
			&&
			amp_is_dev_mode()
			&&
			is_user_logged_in()
		);
	}

	/**
	 * Get the list of service IDs required for this service to be registered.
	 *
	 * @return string[] List of required services.
	 */
	public static function get_requirements() {
		return [
			'dependency_support',
		];
	}

	/**
	 * PairedBrowsing constructor.
	 *
	 * @param UserAccess    $dev_tools_user_access DevTools User Access.
	 * @param PairedRouting $paired_routing    Paired Routing.
	 */
	public function __construct( UserAccess $dev_tools_user_access, PairedRouting $paired_routing ) {
		$this->dev_tools_user_access = $dev_tools_user_access;
		$this->paired_routing        = $paired_routing;
	}

	/**
	 * Adds the filters.
	 */
	public function register() {
		add_action( 'wp', [ $this, 'init_frontend' ], PHP_INT_MAX );
		add_filter( 'amp_dev_mode_element_xpaths', [ $this, 'filter_dev_mode_element_xpaths' ] );
		add_filter( 'amp_validated_url_status_actions', [ $this, 'filter_validated_url_status_actions' ], 10, 2 );
	}

	/**
	 * Filter Dev Mode XPaths to include the inline script used by the client.
	 *
	 * @param string[] $xpaths Element XPaths.
	 * @return string[] XPaths.
	 */
	public function filter_dev_mode_element_xpaths( $xpaths ) {
		$xpaths[] = '//script[ @id = "amp-paired-browsing-client-js-before" ]';
		return $xpaths;
	}

	/**
	 * Filter the status actions for a validated URL to add the paired browsing link.
	 *
	 * @param string[] $actions Action links.
	 * @param WP_Post  $post    AMP Validated URL post.
	 * @return string[] Actions.
	 */
	public function filter_validated_url_status_actions( $actions, WP_Post $post ) {
		$actions['paired_browsing'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $this->get_paired_browsing_url( AMP_Validated_URL_Post_Type::get_url_from_post( $post ) ) ),
			esc_html__( 'Paired Browsing', 'amp' )
		);
		return $actions;
	}

	/**
	 * Initialize frontend.
	 */
	public function init_frontend() {
		if ( ! amp_is_available() ) {
			return;
		}

		if ( isset( $_GET[ self::APP_QUERY_VAR ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->init_app();
		} else {
			$this->init_client();
		}
	}

	/**
	 * Set up app.
	 *
	 * This is the parent request that has the iframes for both AMP and non-AMP.
	 */
	public function init_app() {
		add_action( 'template_redirect', [ $this, 'ensure_app_location' ] );
		add_filter( 'template_include', [ $this, 'filter_template_include_for_app' ], PHP_INT_MAX );
	}

	/**
	 * Set up client.
	 *
	 * Make sure pages have the paired browsing client script so that the app can interact with it.
	 */
	public function init_client() {
		add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu_item' ], 102 );

		/**
		 * Fires before registering plugin assets that may require core asset polyfills.
		 *
		 * @internal
		 */
		do_action( 'amp_register_polyfills' );

		$handle       = 'amp-paired-browsing-client';
		$asset        = require AMP__DIR__ . '/assets/js/amp-paired-browsing-client.asset.php';
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			$handle,
			amp_get_asset_url( '/js/amp-paired-browsing-client.js' ),
			$dependencies,
			$version,
			true
		);

		$is_amp_request = amp_is_request();
		$current_url    = amp_get_current_url();
		$amp_url        = $is_amp_request ? $current_url : $this->paired_routing->add_endpoint( $current_url );
		$non_amp_url    = ! $is_amp_request ? $current_url : $this->paired_routing->remove_endpoint( $current_url );

		wp_add_inline_script(
			$handle,
			sprintf(
				'var ampPairedBrowsingClientData = %s;',
				wp_json_encode(
					[
						'isAmpDocument' => $is_amp_request,
						'ampUrl'        => $amp_url,
						'nonAmpUrl'     => $non_amp_url,
					]
				)
			),
			'before'
		);

		// Mark enqueued script for AMP dev mode so that it is not removed.
		// @todo Revisit with <https://github.com/google/site-kit-wp/pull/505#discussion_r348683617>.
		$dev_mode_handles = array_merge(
			[ $handle, 'wp-i18n', 'wp-hooks', 'regenerator-runtime', 'wp-polyfill' ],
			$dependencies
		);
		add_filter(
			'script_loader_tag',
			static function ( $tag, $script_handle ) use ( $dev_mode_handles ) {
				if ( amp_is_request() && in_array( $script_handle, $dev_mode_handles, true ) ) {
					$tag = preg_replace( '/(?<=<script)(?=\s|>)/i', ' ' . DevMode::DEV_MODE_ATTRIBUTE, $tag );
				}
				return $tag;
			},
			10,
			2
		);
	}

	/**
	 * Add paired browsing menu item to admin bar for AMP.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
	 */
	public function add_admin_bar_menu_item( WP_Admin_Bar $wp_admin_bar ) {
		if ( $this->dev_tools_user_access->is_user_enabled() ) {
			$wp_admin_bar->add_node(
				[
					'parent' => 'amp',
					'id'     => 'amp-paired-browsing',
					'title'  => esc_html__( 'Paired Browsing', 'amp' ),
					'href'   => esc_url( $this->get_paired_browsing_url() ),
				]
			);
		}
	}

	/**
	 * Get paired browsing URL for a given URL.
	 *
	 * @param string $url URL.
	 * @return string Paired browsing URL.
	 */
	public function get_paired_browsing_url( $url = null ) {
		if ( ! $url ) {
			$url = amp_get_current_url();
		}
		$url = $this->paired_routing->remove_endpoint( $url );
		$url = remove_query_arg(
			[ QueryVar::NOAMP, AMP_Validated_URL_Post_Type::VALIDATE_ACTION, AMP_Validation_Manager::VALIDATION_ERROR_TERM_STATUS_QUERY_VAR ],
			$url
		);
		$url = add_query_arg( self::APP_QUERY_VAR, '1', $url );
		return $url;
	}

	/**
	 * Remove any unnecessary query vars that could hamper the paired browsing experience.
	 *
	 * When a redirect is successfully done, this method will exit and not return anything. Exiting is prevented by
	 * filtering `wp_redirect` to be `false`.
	 *
	 * @return bool Whether redirection was needed.
	 */
	public function ensure_app_location() {
		$original_url = amp_get_current_url();
		$updated_url  = $this->get_paired_browsing_url( $original_url );
		if ( $updated_url === $original_url ) {
			return false;
		}

		if ( wp_safe_redirect( $updated_url ) ) {
			exit; // @codeCoverageIgnore
		}
		return true;
	}

	/**
	 * Serve paired browsing experience if it is being requested.
	 *
	 * Includes a custom template that acts as an interface to facilitate a side-by-side comparison of a
	 * non-AMP page and its AMP version to review any discrepancies.
	 *
	 * @return string Custom template if in paired browsing mode, else the supplied template.
	 */
	public function filter_template_include_for_app() {

		/** This action is documented in includes/class-amp-theme-support.php */
		do_action( 'amp_register_polyfills' );

		$handle = 'amp-paired-browsing-app';
		wp_enqueue_style(
			$handle,
			amp_get_asset_url( '/css/amp-paired-browsing-app.css' ),
			[ 'dashicons' ],
			AMP__VERSION
		);

		wp_styles()->add_data( $handle, 'rtl', 'replace' );

		$handle       = 'amp-paired-browsing-app';
		$asset        = require AMP__DIR__ . '/assets/js/amp-paired-browsing-app.asset.php';
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			$handle,
			amp_get_asset_url( '/js/amp-paired-browsing-app.js' ),
			$dependencies,
			$version,
			true
		);

		$data = [
			'ampPairedBrowsingQueryVar' => self::APP_QUERY_VAR,
			'noampQueryVar'             => QueryVar::NOAMP,
			'noampMobile'               => QueryVar::NOAMP_MOBILE,
			'documentTitlePrefix'       => __( 'AMP Paired Browsing:', 'amp' ),
		];
		wp_add_inline_script(
			$handle,
			sprintf(
				'var ampPairedBrowsingAppData = %s;',
				wp_json_encode( $data )
			),
			'before'
		);

		return AMP__DIR__ . '/includes/templates/amp-paired-browsing.php';
	}
}
