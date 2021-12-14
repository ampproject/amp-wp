<?php
/**
 * Class PluginActivationSiteScan.
 *
 * Does an async Site Scan whenever any plugin is activated.
 *
 * @since 2.2
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Validation_Manager;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Services;

/**
 * Class PluginActivationSiteScan
 *
 * @since 2.2
 * @internal
 */
final class PluginActivationSiteScan implements Conditional, Delayed, HasRequirements, Service, Registerable {
	/**
	 * Handle for JS file.
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-site-scan-notice';

	/**
	 * HTML ID for the app root element.
	 *
	 * @var string
	 */
	const APP_ROOT_ID = 'amp-site-scan-notice';

	/**
	 * RESTPreloader instance.
	 *
	 * @var RESTPreloader
	 */
	private $rest_preloader;

	/**
	 * Get the list of service IDs required for this service to be registered.
	 *
	 * @return string[] List of required services.
	 */
	public static function get_requirements() {
		return [ 'dependency_support' ];
	}

	/**
	 * OnboardingWizardSubmenuPage constructor.
	 *
	 * @param RESTPreloader $rest_preloader An instance of the RESTPreloader class.
	 */
	public function __construct( RESTPreloader $rest_preloader ) {
		$this->rest_preloader = $rest_preloader;
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		global $pagenow;

		return (
			is_admin()
			&&
			Services::get( 'dependency_support' )->has_support()
			&&
			! is_network_admin()
			&&
			'plugins.php' === $pagenow
			&&
			(
				! empty( $_GET['activate'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				||
				! empty( $_GET['activate-multi'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			)
			&&
			AMP_Validation_Manager::has_cap()
		);
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_init';
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		add_action( 'pre_current_active_plugins', [ $this, 'render_notice' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Render an admin notice that will do an async Site Scan.
	 */
	public function render_notice() {
		?>
			<div id="<?php echo esc_attr( self::APP_ROOT_ID ); ?>"></div>
		<?php
	}

	/**
	 * Enqueue notice assets.
	 */
	public function enqueue_assets() {
		$asset_file   = AMP__DIR__ . '/assets/js/' . self::ASSET_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			self::ASSET_HANDLE,
			amp_get_asset_url( 'js/' . self::ASSET_HANDLE . '.js' ),
			$dependencies,
			$version,
			true
		);

		wp_enqueue_style(
			self::ASSET_HANDLE,
			amp_get_asset_url( 'css/' . self::ASSET_HANDLE . '.css' ),
			[ 'wp-components' ],
			AMP__VERSION
		);

		$data = [
			'AMP_COMPATIBLE_PLUGINS_URL' => $this->get_amp_compatible_plugins_url(),
			'APP_ROOT_ID'                => self::APP_ROOT_ID,
			'OPTIONS_REST_PATH'          => '/amp/v1/options',
			'SETTINGS_LINK'              => menu_page_url( AMP_Options_Manager::OPTION_NAME, false ),
			'SCANNABLE_URLS_REST_PATH'   => '/amp/v1/scannable-urls',
			'VALIDATE_NONCE'             => AMP_Validation_Manager::has_cap() ? AMP_Validation_Manager::get_amp_validate_nonce() : '',
		];

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampSiteScanNotice = %s;',
				wp_json_encode( $data )
			),
			'before'
		);

		$this->add_preload_rest_paths();
	}

	/**
	 * Get a URL to AMP compatible plugins directory.
	 *
	 * For users capable of installing plugins, the link should lead to the Plugin install page.
	 * Other users will be directed to the plugins page on amp-wp.org.
	 *
	 * @return string URL to AMP compatible plugins directory.
	 */
	protected function get_amp_compatible_plugins_url() {
		if ( current_user_can( 'install_plugins' ) ) {
			return admin_url( '/plugin-install.php?tab=amp-compatible' );
		}

		return 'https://amp-wp.org/ecosystem/plugins/';
	}

	/**
	 * Adds REST paths to preload.
	 */
	protected function add_preload_rest_paths() {
		$paths = [
			'/amp/v1/options',
			add_query_arg(
				[
					'_fields' => [ 'url', 'amp_url', 'type', 'label' ],
				],
				'/amp/v1/scannable-urls'
			),
			add_query_arg(
				'_fields',
				[ 'author', 'name', 'plugin', 'status', 'version' ],
				'/wp/v2/plugins'
			),
			'/wp/v2/users/me',
		];

		foreach ( $paths as $path ) {
			$this->rest_preloader->add_preloaded_path( $path );
		}
	}
}
