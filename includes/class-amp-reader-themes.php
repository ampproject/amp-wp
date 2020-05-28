<?php
/**
 * Fetches and formats data for AMP reader themes.
 *
 * @package AMP
 * @since 1.6.0
 */

/**
 * Class AMP_Reader_Themes.
 *
 * @since 1.6.0
 */
final class AMP_Reader_Themes {
	/**
	 * Formatted theme data.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	private $themes;

	/**
	 * The name of the currently active theme.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	private $current_theme_name;

	/**
	 * Whether themes can be installed in the current WordPress installation.
	 *
	 * @since 1.6.0
	 *
	 * @var bool
	 */
	private $can_install;

	/**
	 * Retrieves all AMP plugin options specified in the endpoint schema.
	 *
	 * @since 1.6.0
	 *
	 * @return array Formatted theme data.
	 */
	public function get_themes() {
		if ( ! is_null( $this->themes ) ) {
			return $this->themes;
		}

		$themes   = $this->get_default_supported_reader_themes( true );
		$themes   = array_map( [ $this, 'prepare_theme' ], $themes );
		$themes[] = $this->get_classic_mode();

		/**
		 * Filters supported reader themes.
		 *
		 * @param array Reader theme objects.
		 */
		$themes = apply_filters( 'amp_reader_themes', $themes );

		$this->themes = array_map( [ $this, 'prepare_theme_availability' ], $themes );

		return $this->themes;
	}

	/**
	 * Gets a reader theme by slug.
	 *
	 * @since 1.6.0
	 *
	 * @param string $slug Theme slug.
	 * @return array Theme data.
	 */
	public function get_reader_theme( $slug ) {
		return current(
			array_filter(
				$this->get_themes(),
				static function( $theme ) use ( $slug ) {
					return $theme['slug'] === $slug;
				}
			)
		);
	}

	/**
	 * Installs a theme from the WP repo.
	 *
	 * @param string $slug Theme slug.
	 * @return bool|WP_Error True if the installation was successful, false or a WP_Error object otherwise.
	 */
	public function install_reader_theme( $slug ) {
		// @todo Use or emulate wp_ajax_install_theme.

		if ( 'classic' === $slug ) {
			return true;
		}

		return true;
	}

	/**
	 * Retrieves theme data.
	 *
	 * @since 1.6.0
	 *
	 * @param boolean $from_api Whether to return theme data from the wordpress.org API. Default false.
	 * @return array Theme ecosystem posts copied the amp-wp.org website.
	 */
	public function get_default_supported_reader_themes( $from_api = false ) {
		if ( $from_api ) {
			if ( ! function_exists( 'themes_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/theme.php';
			}

			$response = themes_api(
				'query_themes',
				[
					'author'   => 'wordpressdotorg',
					'per_page' => 24, // There are only 12 as of 05/2020.
				]
			);

			if ( ! $response || is_wp_error( $response ) ) {
				return $response;
			}

			$supported_themes = array_diff(
				AMP_Core_Theme_Sanitizer::get_supported_themes(),
				[ 'twentyten' ] // Excluded because not responsive!
			);

			$supported_themes_from_response = array_filter(
				$response->themes,
				static function( $theme ) use ( $supported_themes ) {
					return in_array( $theme->slug, $supported_themes, true );
				}
			);

			return $supported_themes_from_response;
		}

		$json   = file_get_contents( AMP__DIR__ . '/assets/json/reader-themes.json' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$themes = json_decode( $json, true );

		return $themes;
	}

	/**
	 * Prepares a single theme.
	 *
	 * @since 1.6.0
	 *
	 * @param array $theme Theme data from the wordpress.org themes API.
	 * @return array Prepared theme array.
	 */
	public function prepare_theme( $theme ) {
		$prepared_theme = [];
		$theme_array    = (array) $theme;

		$keys = [
			'name',
			'slug',
			'preview_url',
			'screenshot_url',
			'homepage',
			'description',
			'requires',
			'requires_php',
			'download_link',
		];

		$prepared_theme = array_filter(
			$theme_array,
			function( $key ) use ( $keys ) {
				return in_array( $key, $keys, true );
			},
			ARRAY_FILTER_USE_KEY
		);

		foreach ( $keys as $key ) {
			if ( ! array_key_exists( $key, $prepared_theme ) ) {
				$prepared_theme[ $key ] = '';
			}
		}

		return $prepared_theme;
	}

	/**
	 * Provides the current theme name.
	 *
	 * @return string|bool The theme name, or false if the theme has errors.
	 */
	private function get_current_theme_name() {
		if ( is_null( $this->current_theme_name ) ) {
			$current_theme = wp_get_theme();

			$this->current_theme_name = $current_theme->exists() ? $current_theme->get( 'Name' ) : false;
		}

		return $this->current_theme_name;
	}

	/**
	 * Returns whether the themes can be installed on the system.
	 *
	 * @since 1.6.0
	 *
	 * @return bool True if themes can be installed.
	 */
	private function get_can_install() {
		if ( is_null( $this->can_install ) ) {
			if ( ! class_exists( 'WP_Upgrader' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			}

			$this->can_install = true === ( new WP_Upgrader() )->fs_connect( [ get_theme_root() ] );
		}

		return $this->can_install;
	}

	/**
	 * Adds information about theme availability and compatibility to the theme data.
	 *
	 * @since 1.6.0
	 *
	 * @param array $theme Theme data.
	 * @return array Theme data with fields added.
	 */
	public function prepare_theme_availability( $theme ) {
		$theme['availability'] = [
			'is_active'         => $this->get_current_theme_name() === $theme['name'],
			'can_install'       => $this->get_can_install(),
			'is_compatible_wp'  => empty( $theme['requires'] ) || is_wp_version_compatible( $theme['requires'] ),
			'is_compatible_php' => empty( $theme['requires_php'] ) || is_php_version_compatible( $theme['requires_php'] ),
		];

		return $theme;
	}

	/**
	 * Provides details for the classic theme included with the plugin.
	 *
	 * @since 1.6.0
	 *
	 * @return array
	 */
	private function get_classic_mode() {
		return [
			'name'           => 'AMP Classic',
			'slug'           => 'classic',
			'preview_url'    => 'https://amp-wp.org',
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentynineteen/screenshot.png?ver=1.5',
			'homepage'       => 'https://amp-wp.org',
			'description'    => __(
				// @todo Improved description text.
				'A legacy default template that looks nice and clean, with a good balance between ease and extensibility when it comes to customization.',
				'amp'
			),
			'requires'       => false,
			'requires_php'   => false,
			'download_link'  => '',
			'availability'   => [
				'is_active'         => false,
				'can_install'       => true,
				'is_compatible_wp'  => true,
				'is_compatible_php' => true,
			],
		];
	}
}
