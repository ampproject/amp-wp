<?php
/**
 * Reference site import Customizer settings step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Step;

use AmpProject\AmpWP\Tests\Cli\ReferenceSiteImporter;
use AmpProject\AmpWP\Tests\Cli\Step;

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
			self::import_settings( $this->settings['astra-settings'] );
		}

		// Add Custom CSS.
		if ( isset( $this->settings['custom-css'] ) ) {
			wp_update_custom_css_post( $this->settings['custom-css'] );
		}

	}

	/**
	 * Import Astra Setting's
	 *
	 * Download & Import images from Astra Customizer Settings.
	 *
	 * @since 1.0.10
	 *
	 * @param  array $settings Astra Customizer setting array.
	 * @return void
	 */
	public static function import_settings( $settings = array() ) {

		array_walk_recursive(
			$settings,
			static function ( &$value ) {
				if ( ! is_array( $value ) ) {

					if ( ReferenceSiteImporter::is_image_url( $value ) ) {
						$data = ReferenceSiteImporter::sideload_image( $value );

						if ( ! is_wp_error( $data ) ) {
							$value = $data->url;
						}
					}
				}
			}
		);

		// Updated settings.
		update_option( 'astra-settings', $settings );
	}
}
