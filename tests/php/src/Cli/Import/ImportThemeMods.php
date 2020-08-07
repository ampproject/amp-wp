<?php
/**
 * Reference site import theme mods step.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Import;

use AmpProject\AmpWP\Tests\Cli\ReferenceSiteImporter;
use AmpProject\AmpWP\Tests\Cli\ImportStep;
use WP_CLI;

final class ImportThemeMods implements ImportStep {

	/**
	 * Associative array of theme_mods to process.
	 *
	 * @var array
	 */
	private $theme_mods;

	/**
	 * ImportThemeMods constructor.
	 *
	 * @param array $theme_mods Associative array of options to process.
	 */
	public function __construct( $theme_mods ) {
		$this->theme_mods = $theme_mods;
	}

	/**
	 * Process the step.
	 *
	 * @return int Number of items that were successfully processed.
	 *             Returns -1 for failure.
	 */
	public function process() {
		$count = 0;

		foreach ( $this->theme_mods as $key => $value ) {
			if ( null === $value ) {
				WP_CLI::log(
					WP_CLI::colorize(
						"Skipping empty theme mod %G'{$key}'%n..."
					)
				);

				continue;
			}

			WP_CLI::log(
				WP_CLI::colorize(
					"Updating theme mod %G'{$key}'%n..."
				)
			);

			switch ( $key ) {
				case 'nav_menu_locations':
					if ( $this->set_nav_menu_locations( $value ) ) {
						++$count;
					}
					break;

				case 'custom_logo':
					if ( $this->insert_logo( $value ) ) {
						++$count;
					}
					break;

				default:
					set_theme_mod( $key, $value );
					// set_theme_mod does not have a return value, so just
					// assume it succeeded.
					++$count;
					break;
			}
		}

		return $count;
	}

	/**
	 * Insert Logo By URL.
	 *
	 * @param string $image_url Logo URL.
	 * @return bool Whether the logo insertion was successful.
	 */
	private function insert_logo( $image_url = '' ) {
		$attachment_id = $this->download_image( $image_url );

		if ( ! $attachment_id ) {
			return false;
		}

		set_theme_mod( 'custom_logo', $attachment_id );

		// set_theme_mod does not have a return value, so just assume it
		// succeeded.
		return true;
	}

	/**
	 * Download image by URL.
	 *
	 * @param string $image_url Image URL to download.
	 * @return int|false Attachment ID of the image, or false if failed.
	 */
	private function download_image( $image_url = '' ) {
		$data = (object) ReferenceSiteImporter::sideload_image( $image_url );

		if ( is_wp_error( $data ) ) {
			WP_CLI::warning(
				WP_CLI::colorize(
					"Failed to download image %G'{$image_url}'%n."
				)
			);
			return false;
		}

		if ( empty( $data->attachment_id ) ) {
			WP_CLI::warning(
				WP_CLI::colorize(
					"Failed to retrieve attachment ID for downloaded image %G'{$image_url}'%n."
				)
			);
			return false;
		}

		return $data->attachment_id;
	}

	/**
	 * Translate from menu_slug into menu_id.
	 *
	 * @param array $nav_menu_locations Associative array of nav menu locations.
	 * @return bool Whether setting the nav menu locations was successful.
	 */
	private function set_nav_menu_locations( $nav_menu_locations = [] ) {
		if ( empty( $nav_menu_locations ) ) {
			return false;
		}

		$menu_locations = [];
		foreach ( $nav_menu_locations as $menu => $value ) {
			$term = get_term_by( 'slug', $value, 'nav_menu' );

			if ( is_object( $term ) ) {
				$menu_locations[ $menu ] = $term->term_id;
			}
		}

		set_theme_mod( 'nav_menu_locations', $menu_locations );

		// set_theme_mod does not have a return value, so just assume it
		// succeeded.
		return true;
	}
}
