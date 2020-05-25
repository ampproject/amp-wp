<?php
/**
 * Reader theme management.
 *
 * @package AMP
 * @since 1.6.0
 */

/**
 * AMP reader theme manager class.
 *
 * @since 1.6.0
 */
final class AMP_Reader_Theme_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {
		$this->namespace = 'amp-wp/v1';
		$this->rest_base = 'reader-themes';
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
					'methods'  => WP_REST_SERVER::READABLE,
					'callback' => [ $this, 'get_items' ],
					'args'     => [],
				],
				'schema' => $this->get_public_item_schema(),
			]
		);
	}

	/**
	 * Retrieves all AMP plugin options specified in the endpoint schema.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) {

		/**
		 * Filters reader themes before they're built.
		 *
		 * @param null|array $reader_themes
		 */
		$items = apply_filters( 'amp_pre_get_reader_themes', null );

		if ( is_null( $items ) ) {
			$items = $this->get_items_cached();
		}

		/**
		 * Filters supported reader themes.
		 *
		 * @param array Reader theme objects.
		 */
		$items = apply_filters( 'amp_reader_themes', $items );

		foreach ( $items as &$item ) {
			$item = $this->prepare_theme_for_response( $item );
		}

		array_unshift( $items, $this->get_classic_mode() );

		return rest_ensure_response( $items );
	}

	/**
	 * Gets configuration data for the AMP classic reader theme.
	 *
	 * @since 1.6.0
	 *
	 * @return array Classic reader theme data.
	 */
	public function get_classic_mode() {
		return [
			'id'             => 0,
			'slug'           => AMP_Theme_Support::DEFAULT_READER_THEME,
			'link'           => 'https://amp-wp.org',
			'title'          => 'AMP Classic',
			'content'        => __(
				'A legacy default template that looks nice and clean, wich a good balance between ease and extensibility when it comes to customization.',
				'amp'
			),
			'featured_media' => [
				'id'            => 0,
				'title'         => 'AMP Classic Theme',
				'alt_text'      => 'AMP Classic Theme',
				'media_details' => [
					'full' => [
						'height'     => 679,
						'source_url' => '//via.placeholder.com/1024x679',
						'width'      => 1024,
					],
				],
			],
			'ecosystem_url'  => null,
		];
	}

	/**
	 * Retrieves items from the remote endpoint if they are not available in cache.
	 *
	 * @since 1.6.0
	 *
	 * @return array Theme ecosystem posts from the amp-wp.org website.
	 */
	public function get_items_cached() {
		$url = add_query_arg(
			[
				'ecosystem_types' => 245, // Theme taxonomy term.
				'_fields'         => 'id,link,title,content,featured_media,meta,slug',
				'per_page'        => 99, // Only 30 available as of May, 2020.
			],
			'https://amp-wp.org/wp-json/wp/v2/ecosystem'
		);

		$cache_key = 'reader_themes';
		if ( wp_using_ext_object_cache() ) {
			$items = wp_cache_get( $cache_key, __CLASS__ );
		} else {
			$items = get_transient( __CLASS__ . $cache_key );
		}

		if ( false === $items ) {
			$request = wp_remote_get( $url );

			if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
				$items = [];
			} else {
				$items = json_decode( wp_remote_retrieve_body( $request ), true );
			}

			$items = $this->add_media_to_items( $items );

			if ( wp_using_ext_object_cache() ) {
				wp_cache_set( $cache_key, $items, __CLASS__, 15 * MINUTE_IN_SECONDS );
			} else {
				set_transient( __CLASS__ . $cache_key, $items, 15 * MINUTE_IN_SECONDS );
			}
		}

		return $items;
	}

	/**
	 * Retrieves featured media items from the amp-wp.org media REST endpoint and adds them to the theme data.
	 *
	 * @param array $items Theme items.
	 * @return array Theme items with image details added.
	 */
	private function add_media_to_items( $items ) {
		$media_ids = array_filter( wp_list_pluck( $items, 'featured_media' ) );

		$url = add_query_arg(
			[
				'_fields'    => 'id,title,alt_text,media_details',
				'include'    => implode( ',', $media_ids ),
				'media_type' => 'image',
				'per_page'   => count( $media_ids ),
			],
			'https://amp-wp.org/wp-json/wp/v2/media'
		);

		$request = wp_remote_get( $url );
		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			$media_items = [];
		} else {
			$media_items = json_decode( wp_remote_retrieve_body( $request ), true );
		}

		$keyed_media_items = [];
		foreach ( $media_items as $media_item ) {
			$keyed_media_items[ intval( $media_item['id'] ) ] = $this->prepare_featured_media_for_response( $media_item );
		}

		foreach ( $items as &$item ) {
			if ( ! empty( $item['featured_media'] ) && isset( $keyed_media_items[ $item['featured_media'] ] ) ) {
				$item['featured_media'] = $keyed_media_items[ $item['featured_media'] ];
			}
		}

		return $items;
	}

	/**
	 * Prepares a single theme for the response.
	 *
	 * @since 1.6.0
	 *
	 * @param array $item Post data from the remote REST endpoint.
	 * @return array Response data.
	 */
	public function prepare_theme_for_response( $item ) {
		$prepared_item = [];

		foreach ( $item as $key => $value ) {
			switch ( $key ) {
				case 'content':
				case 'title':
					$prepared_item[ $key ] = wp_strip_all_tags( $value['rendered'], true );
					break;

				case 'meta':
					$prepared_item['ecosystem_url'] = $value['ampps_ecosystem_url'];
					break;

				default:
					$prepared_item[ $key ] = $value;
			}
		}

		return $prepared_item;
	}

	/**
	 * Prepares featured media data for the REST response.
	 *
	 * @since 1.6.0
	 *
	 * @param array $item Media details.
	 * @return array Prepared media details.
	 */
	public function prepare_featured_media_for_response( $item ) {
		$prepared_item = [];

		foreach ( $item as $key => $value ) {
			switch ( $key ) {
				case 'title':
					$prepared_item[ $key ] = wp_strip_all_tags( $value['rendered'], true );
					break;

				case 'media_details':
					$prepared_item[ $key ] = [
						'full'  => empty( $item[ $key ]['sizes']['full'] )
							? null
							: [
								'height'     => $item[ $key ]['sizes']['full']['height'],
								'source_url' => $item[ $key ]['sizes']['full']['source_url'],
								'width'      => $item[ $key ]['sizes']['full']['width'],
							],
						'large' => empty( $item[ $key ]['sizes']['large'] )
							? null
							: [
								'height'     => $item[ $key ]['sizes']['large']['height'],
								'source_url' => $item[ $key ]['sizes']['large']['source_url'],
								'width'      => $item[ $key ]['sizes']['large']['width'],
							],
					];
					break;

				default:
					$prepared_item[ $key ] = $value;
			}
		}

		return $prepared_item;
	}
}
