<?php
/**
 * Class ReaderThemeLoader.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AMP_Options_Manager;
use AMP_Theme_Support;
use WP_Theme;
use WP_Customize_Manager;
use AMP_Reader_Themes;

/**
 * Switches to the designated Reader theme when template mode enabled and when requesting an AMP page.
 *
 * @package AmpProject\AmpWP
 */
final class ReaderThemeLoader implements Service, Registerable, Conditional {

	/**
	 * Reader theme.
	 *
	 * @var WP_Theme
	 */
	private $theme;

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		// The following needs to run at plugins_loaded because that is when _wp_customize_include runs. Otherwise, the
		// most logical action would be setup_theme.
		add_action( 'plugins_loaded', [ $this, 'override_theme' ], 9 );
	}

	/**
	 * Is Reader mode with a Reader theme selected.
	 *
	 * @return bool Whether new Reader mode.
	 */
	public static function is_needed() {
		return (
			AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
			&&
			AMP_Reader_Themes::DEFAULT_READER_THEME !== AMP_Options_Manager::get_option( Option::READER_THEME )
		);
	}

	/**
	 * Get reader theme.
	 *
	 * If the Reader template mode is enabled
	 *
	 * @return WP_Theme|null Theme if selected and no errors.
	 */
	public function get_reader_theme() {
		$reader_theme_slug = AMP_Options_Manager::get_option( Option::READER_THEME );
		if ( ! $reader_theme_slug ) {
			return null;
		}

		$reader_theme = wp_get_theme( $reader_theme_slug );
		if ( $reader_theme->errors() ) {
			return null;
		}

		return $reader_theme;
	}

	/**
	 * Switch theme if in Reader mode, a Reader theme was selected, and the AMP query var is present.
	 *
	 * Note that AMP_Theme_Support will redirect to the non-AMP version if AMP is not available for the query.
	 *
	 * @see WP_Customize_Manager::start_previewing_theme() which provides for much of the inspiration here.
	 */
	public function override_theme() {
		// Short-circuit if the request does not include the AMP query var.
		if ( ! isset( $_GET[ amp_get_slug() ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$theme = $this->get_reader_theme();
		if ( ! $theme instanceof WP_Theme ) {
			return;
		}

		$this->theme = $theme;

		$get_template   = function () {
			return $this->theme->get_template();
		};
		$get_stylesheet = function () {
			return $this->theme->get_stylesheet();
		};

		add_filter( 'stylesheet', $get_stylesheet );
		add_filter( 'template', $get_template );
		add_filter(
			'pre_option_current_theme',
			function () {
				return $this->theme->display( 'Name' );
			}
		);

		// @link: https://core.trac.wordpress.org/ticket/20027
		add_filter( 'pre_option_stylesheet', $get_stylesheet );
		add_filter( 'pre_option_template', $get_template );

		// Handle custom theme roots.
		add_filter(
			'pre_option_stylesheet_root',
			function () {
				return get_raw_theme_root( $this->theme->get_stylesheet(), true );
			}
		);
		add_filter(
			'pre_option_template_root',
			function () {
				return get_raw_theme_root( $this->theme->get_template(), true );
			}
		);

		$this->disable_widgets();
		add_filter( 'customize_previewable_devices', [ $this, 'customize_previewable_devices' ] );
		add_filter( 'customize_register', [ $this, 'remove_customizer_themes_panel' ], 11 );
	}

	/**
	 * Disable widgets.
	 */
	public function disable_widgets() {
		add_filter( 'sidebars_widgets', '__return_empty_array', PHP_INT_MAX );
		add_filter(
			'customize_loaded_components',
			static function( $components ) {
				return array_diff( $components, [ 'widgets' ] );
			}
		);
	}

	/**
	 * Make tablet (smartphone) the default device when opening AMP Customizer.
	 *
	 * @param array $devices Devices.
	 * @return array Devices.
	 */
	public function customize_previewable_devices( $devices ) {
		if ( isset( $devices['tablet'] ) ) {
			unset( $devices['desktop']['default'] );
			$devices['tablet']['default'] = true;
		}
		return $devices;
	}

	/**
	 * Remove themes panel from AMP Customizer.
	 *
	 * @param WP_Customize_Manager $wp_customize Customize manager.
	 */
	public function remove_customizer_themes_panel( WP_Customize_Manager $wp_customize ) {
		if ( $wp_customize->get_panel( 'themes' ) ) {
			$wp_customize->remove_panel( 'themes' );
		}
	}
}
