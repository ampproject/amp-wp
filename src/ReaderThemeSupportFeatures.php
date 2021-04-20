<?php
/**
 * Class ReaderThemeSupportFeatures.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AMP_Options_Manager;

/**
 * Stores the primary theme's theme support features when Reader template mode is active and then adds the necessary styles to support them.
 *
 * Note that this service is not conditional based on whether Reader mode is selected because it needs to run logic
 * when transitioning from non-reader to Reader mode.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class ReaderThemeSupportFeatures implements Service, Registerable {

	/**
	 * Reader theme loader.
	 *
	 * @var ReaderThemeLoader
	 */
	private $reader_theme_loader;

	/**
	 * ReaderThemeLoader constructor.
	 *
	 * @param ReaderThemeLoader $reader_theme_loader Reader theme loader.
	 */
	public function __construct( ReaderThemeLoader $reader_theme_loader ) {
		$this->reader_theme_loader = $reader_theme_loader;
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'amp_options_updating', [ $this, 'filter_amp_options_updating' ] );
		add_action( 'after_switch_theme', [ $this, 'update_cached_theme_support' ] );

		add_action(
			'after_setup_theme',
			[ $this, 'add_primary_theme_support' ],
			100 // After the theme has added its own theme support.
		);

		add_action( 'amp_post_template_head', [ $this, 'print_theme_support_styles' ] );
		add_action(
			'wp_head',
			[ $this, 'print_theme_support_styles' ],
			9 // Because wp_print_styles happens at priority 8, and we want the primary theme's colors to override any conflicting theme color assignments.
		);


		// @todo Also do this when the theme has been updated?
	}

	/**
	 * Filter the AMP options when they are updated to add the primary theme's features.
	 *
	 * @param array $options Options.
	 * @return array Options.
	 */
	public function filter_amp_options_updating( $options ) {
		if ( $this->reader_theme_loader->is_enabled( $options ) ) {
			$options[ Option::PRIMARY_THEME_SUPPORT ] = $this->get_theme_support_features();
		} else {
			$options[ Option::PRIMARY_THEME_SUPPORT ] = null;
		}
		return $options;
	}

	/**
	 * Update primary theme's cached theme support.
	 */
	public function update_cached_theme_support() {
		if ( $this->reader_theme_loader->is_enabled() ) {
			AMP_Options_Manager::update_option( Option::PRIMARY_THEME_SUPPORT, $this->get_theme_support_features() );
		} else {
			AMP_Options_Manager::update_option( Option::PRIMARY_THEME_SUPPORT, null );
		}
	}

	/**
	 * Get the theme support features.
	 *
	 * @return array Theme support features.
	 */
	public function get_theme_support_features() {
		$features     = [];
		$feature_keys = [
			'editor-color-palette',
			'editor-gradient-presets',
			'editor-font-sizes',
		];
		foreach ( $feature_keys as $feature_key ) {
			$features[ $feature_key ] = current( (array) get_theme_support( $feature_key ) );
		}
		return $features;
	}

	/**
	 * Add theme support from primary theme.
	 */
	public function add_primary_theme_support() {
		if ( ! $this->reader_theme_loader->is_enabled() ) {
			return;
		}
		$theme_support_features = AMP_Options_Manager::get_option( Option::PRIMARY_THEME_SUPPORT );
		foreach ( $theme_support_features as $support => $feature ) {
			if ( is_array( $feature ) ) {
				add_theme_support( $support, $feature );
			}
		}
	}

	/**
	 * Print theme support styles.
	 *
	 * @internal
	 * @since 2.1
	 */
	public function print_theme_support_styles() {
		$features = [];
		if ( $this->reader_theme_loader->is_enabled() ) {
			$features = AMP_Options_Manager::get_option( Option::PRIMARY_THEME_SUPPORT );
		} elseif ( amp_is_legacy() ) {
			$feature_keys = [
				'editor-color-palette',
				'editor-gradient-presets',
				'editor-font-sizes',
			];
			foreach ( $feature_keys as $feature_key ) {
				$features[ $feature_key ] = current( (array) get_theme_support( $feature_key ) );
			}
		}

		if ( empty( $features ) ) {
			return;
		}

		echo '<style id="amp-wp-theme-support-feature-styles">';

		if ( ! empty( $features['editor-color-palette'] ) ) {
			foreach ( $features['editor-color-palette'] as $color_option ) {
				// There is no standard way to retrieve or derive the `color` style property when the editor color is being used
				// for the background, so the best alternative at the moment is to guess a good default value based on the
				// luminance of the editor color.
				$text_color = 127 > $this->get_relative_luminance_from_hex( $color_option['color'] ) ? '#fff' : '#000';

				printf(
					':root .has-%1$s-background-color { background-color: %2$s; color: %3$s; }',
					sanitize_key( $color_option['slug'] ),
					$color_option['color'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$text_color // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
			foreach ( $features['editor-color-palette'] as $color_option ) {
				printf(
					':root .has-%1$s-color { color: %2$s; }',
					sanitize_key( $color_option['slug'] ),
					$color_option['color'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
		}

		if ( ! empty( $features['editor-gradient-presets'] ) ) {
			foreach ( $features['editor-gradient-presets'] as $preset ) {
				printf(
					'.has-%s-gradient-background { background: %s }',
					sanitize_key( $preset['slug'] ),
					$preset['gradient'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
		}

		if ( ! empty( $features['editor-font-sizes'] ) ) {
			foreach ( $features['editor-font-sizes'] as $font_size ) {
				printf(
					':root .is-%1$s-text, :root .has-%1$s-font-size { font-size: %2$fpx }',
					sanitize_key( $font_size['slug'] ),
					(float) $font_size['size']
				);
			}
		}

		echo '</style>';
	}

	/**
	 * Get relative luminance from color hex value.
	 *
	 * Copied from `\Twenty_Twenty_One_Custom_Colors::get_relative_luminance_from_hex()`.
	 *
	 * @internal
	 * @see https://github.com/WordPress/wordpress-develop/blob/acbbbd18b32b5429264622141a6d058b64f3a5ad/src/wp-content/themes/twentytwentyone/classes/class-twenty-twenty-one-custom-colors.php#L138-L156
	 * @since 2.1
	 *
	 * @param string $hex Color hex value.
	 * @return int Relative luminance value.
	 */
	public function get_relative_luminance_from_hex( $hex ) {

		// Remove the "#" symbol from the beginning of the color.
		$hex = ltrim( $hex, '#' );

		// Make sure there are 6 digits for the below calculations.
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		// Get red, green, blue.
		$red   = hexdec( substr( $hex, 0, 2 ) );
		$green = hexdec( substr( $hex, 2, 2 ) );
		$blue  = hexdec( substr( $hex, 4, 2 ) );

		// Calculate the luminance.
		$lum = ( 0.2126 * $red ) + ( 0.7152 * $green ) + ( 0.0722 * $blue );
		return (int) round( $lum );
	}
}
