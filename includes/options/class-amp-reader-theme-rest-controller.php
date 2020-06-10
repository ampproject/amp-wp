<?php
/**
 * Reader theme controller.
 *
 * @package AMP
 * @since 1.6.0
 */

/**
 * AMP reader theme REST controller.
 *
 * @since 1.6.0
 */
final class AMP_Reader_Theme_REST_Controller extends WP_REST_Controller {

	/**
	 * Reader themes provider class.
	 *
	 * @var AMP_Reader_Themes
	 */
	private $reader_themes;

	/**
	 * Constructor.
	 *
	 * @param AMP_Reader_Themes $reader_themes AMP_Reader_Themes instance to provide theme data.
	 */
	public function __construct( $reader_themes ) {
		$this->reader_themes = $reader_themes;
		$this->namespace     = 'amp-wp/v1';
		$this->rest_base     = 'reader-themes';
	}

	/**
	 * Sets up hooks.
	 */
	public function init() {
		add_filter( 'amp_reader_themes', [ $this, 'prepare_default_reader_themes_for_rest' ] );
	}

	/**
	 * Overrides data for default themes.
	 *
	 * @param array $themes Default reader themes.
	 * @return array Filtered default reader themes.
	 */
	public function prepare_default_reader_themes_for_rest( $themes ) {
		return array_map( [ $this, 'prepare_default_reader_theme_for_rest' ], $themes );
	}

	/**
	 * Overrides data for a default theme.
	 *
	 * @param array $theme Reader theme data.
	 * @return array Filtered reader theme data.
	 */
	public function prepare_default_reader_theme_for_rest( $theme ) {
		$theme_slugs = wp_list_pluck( $this->reader_themes->get_default_reader_themes(), 'slug' );

		if ( in_array( $theme['slug'], $theme_slugs, true ) || 'classic' === $theme['slug'] ) {
			$theme['screenshot_url'] = amp_get_asset_url( "images/reader-themes/{$theme['slug']}.png" );
		}

		$theme['description'] = wp_trim_words( $theme['description'], 25 );

		return $theme;
	}

	/**
	 * Registers routes for the controller.
	 *
	 * @since 1.6.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'  => WP_REST_Server::READABLE,
					'callback' => [ $this, 'get_items' ],
					'args'     => [],
				],
			]
		);
	}

	/**
	 * Retrieves all AMP plugin options specified in the endpoint schema.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$themes = $this->reader_themes->get_themes();

		return rest_ensure_response( $themes );
	}
}
