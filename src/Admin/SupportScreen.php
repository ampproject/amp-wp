<?php
/**
 * To add support page under AMP menu page in WordPress admin.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Support\SupportData;
use AMP_Validated_URL_Post_Type;
use AMP_Validation_Manager;
use WP_Query;

/**
 * SupportScreen class to add support page under AMP menu page in WordPress admin.
 *
 * @internal
 * @since 2.2
 */
class SupportScreen implements Conditional, Delayed, Service, Registerable {

	/**
	 * Handle for JS file.
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-support';

	/**
	 * The minimum version of WordPress support for the "Support page".
	 *
	 * @var string
	 */
	const WP_MIN_VERSION = '5.2';

	/**
	 * Injector.
	 *
	 * @var Injector
	 */
	private $injector;

	/**
	 * The parent menu slug.
	 *
	 * @var string
	 */
	private $parent_menu_slug;

	/**
	 * GoogleFonts instance.
	 *
	 * @var GoogleFonts
	 */
	private $google_fonts;

	/**
	 * Get registration action.
	 *
	 * Note that this runs at `init` so that it comes after the user is set. It can't use admin_init even though it the
	 * `is_needed()` method calls `is_admin()` since it adds an `admin_menu` action which runs _before_ `admin_init`.
	 *
	 * @return string
	 */
	public static function get_registration_action() {
		return 'init';
	}

	/**
	 * Class constructor.
	 *
	 * @param Injector    $injector     Injector.
	 * @param OptionsMenu $options_menu An instance of the class handling the parent menu.
	 * @param GoogleFonts $google_fonts An instance of the GoogleFonts service.
	 */
	public function __construct( Injector $injector, OptionsMenu $options_menu, GoogleFonts $google_fonts ) {

		$this->injector = $injector;

		$this->parent_menu_slug = $options_menu->get_menu_slug();

		$this->google_fonts = $google_fonts;

	}

	/**
	 * Determine whether the user has the capability to access the support screen.
	 *
	 * @return bool Whether user has the capability.
	 */
	public static function has_cap() {
		return (
			current_user_can( 'view_site_health_checks' )
			&&
			current_user_can( 'manage_options' )
			&&
			AMP_Validation_Manager::has_cap()
		);
	}

	/**
	 * Returns whether minimum WordPress version is available for support page or not.
	 *
	 * @return bool True if current WordPress's version is greater than or equal to minimum version.
	 */
	public static function check_core_version() {
		return version_compare( get_bloginfo( 'version' ), self::WP_MIN_VERSION, '>=' );
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return (
			self::check_core_version()
			&&
			is_admin()
			&&
			self::has_cap()
		);
	}

	/**
	 * Adds hooks.
	 */
	public function register() {

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_menu', [ $this, 'add_menu_items' ], 9 );
	}

	/**
	 * Returns the slug for the support page.
	 *
	 * @return string
	 */
	public function get_menu_slug() {

		return 'amp-support';
	}

	/**
	 * Provides the support screen handle.
	 *
	 * @return string
	 */
	public function screen_handle() {

		return sprintf( 'amp_page_%s', $this->get_menu_slug() );
	}

	/**
	 * Add menu.
	 */
	public function add_menu_items() {

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		add_submenu_page(
			$this->parent_menu_slug,
			esc_html__( 'Support', 'amp' ),
			esc_html__( 'Support', 'amp' ),
			'manage_options',
			$this->get_menu_slug(),
			[ $this, 'render_screen' ],
			10
		);

	}

	/**
	 * Enqueues settings page assets.
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {

		if ( $this->screen_handle() !== $hook_suffix ) {
			return;
		}

		/** This action is documented in includes/class-amp-theme-support.php */
		do_action( 'amp_register_polyfills' );

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
			amp_get_asset_url( 'css/amp-support.css' ),
			[
				$this->google_fonts->get_handle(),
				'wp-components',
			],
			AMP__VERSION
		);

		$args = [];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$amp_url = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : '';

		if ( ! empty( $amp_url ) ) {
			$args = [
				'urls' => [ $amp_url ],
			];
		}

		$support_data = $this->injector->make( SupportData::class, compact( 'args' ) );
		$data         = $support_data->get_data();

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampSupport = %s;',
				wp_json_encode(
					[
						'restEndpoint'          => get_rest_url( null, 'amp/v1/send-diagnostic' ),
						'args'                  => $args,
						'data'                  => $data,
						'ampValidatedPostCount' => $this->get_amp_validated_post_counts(),
					]
				)
			),
			'before'
		);
	}

	/**
	 * Get count of amp validated post.
	 *
	 * @return array [
	 *     @type int $all   Count of all AMP validated URL post.
	 *     @type int $valid Count of non-stale AMP validated URL posts.
	 *     @type int $stale Count of stale AMP validated URL posts.
	 * ]
	 */
	public function get_amp_validated_post_counts() {

		$amp_validated_post_count = wp_count_posts( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG );

		$query_args = [
			'post_type'      => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_key'       => AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY,
			'meta_value'     => maybe_serialize( AMP_Validated_URL_Post_Type::get_validated_environment() ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		];
		$query      = new WP_Query( $query_args );

		$all   = intval( $amp_validated_post_count->publish );
		$fresh = intval( $query->found_posts );
		$stale = $all - $fresh;

		return compact( 'all', 'fresh', 'stale' );
	}

	/**
	 * Display Settings.
	 *
	 * @return void
	 */
	public function render_screen() {

		?>
		<div class="wrap">
			<div id="amp-support">
				<div class="amp amp-support">
					<div id="amp-support-root"></div>
				</div>
			</div>
		</div>
		<?php
	}
}
