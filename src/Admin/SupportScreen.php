<?php
/**
 * To add support page under AMP menu page in WordPress admin.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Support\SupportData;

/**
 * SupportScreen class to add support page under AMP menu page in WordPress admin.
 *
 * @internal
 */
class SupportScreen implements Conditional, Service, Registerable {

	/**
	 * Handle for JS file.
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-support';

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
	 * SupportData instance.
	 *
	 * @var SupportData
	 */
	private $support_data;

	/**
	 * Class constructor.
	 *
	 * @param OptionsMenu $options_menu An instance of the class handling the parent menu.
	 * @param GoogleFonts $google_fonts An instance of the GoogleFonts service.
	 * @param SupportData $support_data An instance of the SupportData service.
	 */
	public function __construct( OptionsMenu $options_menu, GoogleFonts $google_fonts, SupportData $support_data ) {

		$this->parent_menu_slug = $options_menu->get_menu_slug();

		$this->google_fonts = $google_fonts;

		$this->support_data = $support_data;
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return is_admin();
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

		$args    = [];
		$post_id = filter_input( INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! empty( $post_id ) && 0 < intval( $post_id ) ) {
			$args = [
				'amp_validated_post_ids' => [
					$post_id,
				],
			];
		}

		$this->support_data->set_args( $args );
		$data = $this->support_data->get_data();

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampSupport = %s;',
				wp_json_encode(
					[
						'restEndpoint' => get_rest_url( null, 'amp/v1/send-diagnostic' ),
						'nonce'        => wp_create_nonce( 'wp_rest' ),
						'args'         => $args,
						'data'         => $data,
					]
				)
			),
			'before'
		);
	}

	/**
	 * Display Settings.
	 *
	 * @return void
	 */
	public function render_screen() {

		?>
		<div id="amp-support" class="wrap">
			<div class="amp amp-support">
				<div id="amp-support-root"></div>
			</div>
		</div>
		<?php
	}
}
