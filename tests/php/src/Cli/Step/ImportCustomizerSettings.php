<?php
/**
 * Reference site import Customizer settings step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Step;

use AmpProject\AmpWP\Tests\Cli\ReferenceSiteImporter;
use AmpProject\AmpWP\Tests\Cli\Step;
use WP_CLI;

final class ImportCustomizerSettings implements Step {

	/**
	 * Associative array of Customizer settings.
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * ImportCustomizerSettings constructor.
	 *
	 * @param array $settings Associative array of Customizer settings.
	 */
	public function __construct( $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Process the step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		// Update Astra Theme customizer settings.
		if ( isset( $this->settings['astra-settings'] ) ) {
			self::import_astra_settings( $this->settings['astra-settings'] );
		}

		// Add Custom CSS.
		if ( isset( $this->settings['custom-css'] ) ) {
			WP_CLI::log(
				WP_CLI::colorize( "Updating Customizer %GCustom CSS%n..." )
			);

			wp_update_custom_css_post( $this->settings['custom-css'] );
		}

		WP_CLI::success( 'Customizer settings imported successfully.' );
	}

	/**
	 * Import Astra theme settings.
	 *
	 * @param  array $settings Astra Customizer setting array.
	 * @return void
	 */
	public static function import_astra_settings( $settings = array() ) {
		WP_CLI::log(
			WP_CLI::colorize( "Sideloading images in option %G'astra-settings'%n..." )
		);

		array_walk_recursive(
			$settings,
			static function ( &$value ) {
				if ( ! is_array( $value ) ) {

					if ( ReferenceSiteImporter::is_image_url( $value ) ) {
						$data = ReferenceSiteImporter::sideload_image( $value );

						if ( ! is_wp_error( $data ) ) {
							$value = $data->url;
						} else {
							WP_CLI::warning( "Failed to sideload image '{$value}' - {$data->get_error_message()}" );
						}
					}
				}
			}
		);

		WP_CLI::log(
			WP_CLI::colorize( "Updating option in %G'astra-settings'%n..." )
		);

		update_option( 'astra-settings', $settings );
	}
}
