<?php
/**
 * Reference site export theme mods.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;

final class ExportThemeMods implements ExportStep {

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result ) {
		$theme_mods = $this->adapt_theme_mods( $this->fetch_theme_mods(), $export_result );

		$export_result->add_step( 'import_theme_mods', compact( 'theme_mods' ) );

		return $export_result;
	}

	/**
	 * Adapt the theme mods to get rid of hard-coded elements like IDs.
	 *
	 * @param array        $theme_mods    Associative array of theme mods to
	 *                                    adapt.
	 * @param ExportResult $export_result Export result to adapt.
	 * @return array Adapted associative array of theme mods.
	 */
	private function adapt_theme_mods( $theme_mods, $export_result ) {
		foreach ( $theme_mods as $key => $value ) {
			switch ( $key ) {
				case 'nav_menu_locations':
					$theme_mods[ $key ] = $this->get_nav_menu_location( $value );
					break;

				case 'custom_logo':
					$media_uploader     = new MediaFileUploader();
					$theme_mods[ $key ] = $media_uploader->upload(
						$export_result->get_site_name(),
						wp_get_attachment_url( $value )
					);
					break;
			}
		}

		return $theme_mods;
	}

	/**
	 * Fetch the theme mods.
	 *
	 * @return array Associative array of theme mods.
	 */
	private function fetch_theme_mods() {
		$theme_mods = array_filter( (array) get_theme_mods() );

		unset( $theme_mods['custom_css_post_id'] );

		return $theme_mods;
	}

	/**
	 * Translate from menu_id into menu_slug.
	 *
	 * @param array $nav_menu_locations Associative array of nav menu locations.
	 * @return array Associative array of translated menu locations.
	 */
	private function get_nav_menu_location( $nav_menu_locations ) {
		if ( empty( $nav_menu_locations ) ) {
			return [];
		}

		$menu_locations = [];
		foreach ( $nav_menu_locations as $menu => $value ) {
			$term = get_term( $value, 'nav_menu' );

			if ( is_object( $term ) ) {
				$menu_locations[ $menu ] = $term->slug;
			}
		}

		return $menu_locations;
	}
}
