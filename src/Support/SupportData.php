<?php
/**
 * Class to prepare and send support data to insights server.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Support;

use AMP_Allowed_Tags_Generated;
use AMP_Options_Manager;
use AMP_Rule_Spec;
use AMP_Style_Sanitizer;
use AMP_Validated_URL_Post_Type;
use LimitIterator;
use SplFileObject;
use WP_Error;
use WP_Site_Health;
use WP_Theme;

/**
 * Class SupportData
 * To prepare and send support data to insights server.
 *
 * @internal
 * @since 2.2
 */
class SupportData {

	/**
	 * Endpoint to send diagnostic data.
	 *
	 * @since 2.2
	 *
	 * @var string
	 */
	const SUPPORT_ENDPOINT = 'https://insights.amp-wp.org';

	/**
	 * Args for AMP send data.
	 *
	 * @since 2.2
	 *
	 * @var array
	 */
	public $args = [];

	/**
	 * List of URL to send data.
	 *
	 * @since 2.2
	 *
	 * @var string[]
	 */
	public $urls = [];

	/**
	 * Support Data.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Constructor method.
	 *
	 * @since 2.2
	 *
	 * @param array $args Arguments for AMP Send data.
	 */
	public function __construct( $args = [] ) {
		$this->args = ( ! empty( $args ) && is_array( $args ) ) ? $args : [];

		$this->parse_args();
	}

	/**
	 * To parse args for AMP data that will send.
	 *
	 * @since 2.2
	 *
	 * @return void
	 */
	public function parse_args() {

		if ( ! empty( $this->args['urls'] ) && is_array( $this->args['urls'] ) ) {
			$this->urls = array_merge( $this->urls, $this->args['urls'] );
		}

		if ( ! empty( $this->args['term_ids'] ) && is_array( $this->args['term_ids'] ) ) {
			$this->args['term_ids'] = array_map( 'intval', $this->args['term_ids'] );
			$this->args['term_ids'] = array_filter( $this->args['term_ids'] );

			foreach ( $this->args['term_ids'] as $term_id ) {
				$url = get_term_link( $term_id );

				if ( ! empty( $url ) && ! is_wp_error( $url ) ) {
					$this->urls[] = $url;
				}
			}
		}

		if ( ! empty( $this->args['post_ids'] ) && is_array( $this->args['post_ids'] ) ) {

			$this->args['post_ids'] = array_map( 'intval', $this->args['post_ids'] );
			$this->args['post_ids'] = array_filter( $this->args['post_ids'] );

			foreach ( $this->args['post_ids'] as $post_id ) {

				$url = get_permalink( $post_id );

				if ( ! empty( $url ) && ! is_wp_error( $url ) ) {
					$this->urls[] = $url;
				}
			}
		}

		if ( ! empty( $this->args['amp_validated_post_ids'] ) && is_array( $this->args['amp_validated_post_ids'] ) ) {
			$this->args['amp_validated_post_ids'] = array_map( 'intval', $this->args['amp_validated_post_ids'] );
			$this->args['amp_validated_post_ids'] = array_filter( $this->args['amp_validated_post_ids'] );

			foreach ( $this->args['amp_validated_post_ids'] as $post_id ) {
				$post = get_post( $post_id );

				if ( ! empty( $post->post_title ) ) {
					$this->urls[] = $post->post_title;
				}
			}
		}

		$this->urls = array_map( static function ( $url ) {
			return AMP_Validated_URL_Post_Type::normalize_url_for_storage( $url );
		}, $this->urls );
		$this->urls = array_values( array_unique( $this->urls ) );
	}

	/**
	 * To send support data to insight server.
	 *
	 * @return array|WP_Error WP_Error on fail, Otherwise server response.
	 */
	public function send_data() {

		$data     = ( ! empty( $this->data ) ) ? $this->data : $this->get_data();
		$endpoint = ( ! empty( $this->args['endpoint'] ) ) ? $this->args['endpoint'] : self::SUPPORT_ENDPOINT;
		$endpoint = untrailingslashit( $endpoint );

		// Send data to server.
		$response = wp_remote_post(
			sprintf( '%s/api/v1/support/', $endpoint ),
			[
				// We need long timeout here, in case the data being sent is large or the network connection is slow.
				'timeout'  => 300, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
				'body'     => $data,
				'compress' => true,
			]
		);

		if ( ! is_wp_error( $response ) ) {
			$response = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( JSON_ERROR_NONE !== json_last_error() ) {
				return new WP_Error( 'malformed_json_amp_insight_endpoint', 'Failed to parse response body from AMP insights endpoint' );
			}
		}

		return $response;
	}

	/**
	 * To get amp data to send it to compatibility server.
	 *
	 * @since 2.2
	 *
	 * @return array
	 */
	public function get_data() {

		$amp_urls = $this->get_amp_urls();

		$request_data = [
			'site_url'      => static::get_home_url(),
			'site_info'     => $this->get_site_info(),
			'plugins'       => $this->get_plugin_info(),
			'themes'        => $this->get_theme_info(),
			'errors'        => array_values( $amp_urls['errors'] ),
			'error_sources' => array_values( $amp_urls['error_sources'] ),
			'urls'          => array_values( $amp_urls['urls'] ),
			'error_log'     => $this->get_error_log(),
		];

		if ( ! empty( $this->args['is_synthetic'] ) ) {
			$request_data['site_info']['is_synthetic_data'] = true;
		}

		$this->data = $request_data;

		return $request_data;
	}

	/**
	 * To get site info.
	 *
	 * @since 2.2
	 *
	 * @return array Site information.
	 */
	public function get_site_info() {

		global $wpdb;

		$wp_type = 'single';
		if ( is_multisite() ) {
			$wp_type = defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ? 'subdomain' : 'subdir';
		}

		$active_theme = wp_get_theme();
		$active_theme = static::normalize_theme_info( $active_theme );

		$amp_settings = AMP_Options_Manager::get_options();
		$amp_settings = ( ! empty( $amp_settings ) && is_array( $amp_settings ) ) ? $amp_settings : [];

		$loopback_status = '';

		if ( method_exists( WP_Site_Health::class, 'can_perform_loopback' ) ) {
			if ( method_exists( WP_Site_Health::class, 'get_instance' ) ) {
				$wp_site_health = WP_Site_Health::get_instance();
			} else {
				$wp_site_health = new WP_Site_Health();
			}

			$loopback_status = $wp_site_health->can_perform_loopback();
			$loopback_status = ( ! empty( $loopback_status->status ) ) ? $loopback_status->status : '';
		}

		if ( version_compare( get_bloginfo( 'version' ), '5.7', '>=' ) ) {
			$https_status = wp_is_https_supported();
		} else {
			$https_status = is_ssl();
		}

		return [
			'site_url'                    => static::get_home_url(),
			'site_title'                  => get_bloginfo( 'site_title' ),
			'php_version'                 => PHP_VERSION,
			'mysql_version'               => $wpdb->db_version(), // phpcs:ignore
			'wp_version'                  => get_bloginfo( 'version' ),
			'wp_language'                 => get_bloginfo( 'language' ),
			'wp_https_status'             => $https_status,
			'wp_multisite'                => $wp_type,
			'wp_active_theme'             => $active_theme,
			'object_cache_status'         => wp_using_ext_object_cache(),
			'libxml_version'              => ( defined( 'LIBXML_VERSION' ) ) ? LIBXML_VERSION : '',
			'is_defined_curl_multi'       => ( function_exists( 'curl_multi_init' ) ),
			'loopback_requests'           => $loopback_status,
			'amp_mode'                    => ( ! empty( $amp_settings['theme_support'] ) ) ? $amp_settings['theme_support'] : '',
			'amp_version'                 => ( ! empty( $amp_settings['version'] ) ) ? $amp_settings['version'] : '',
			'amp_plugin_configured'       => ! empty($amp_settings['plugin_configured'] ),
			'amp_all_templates_supported' => ! empty($amp_settings['all_templates_supported'] ),
			'amp_supported_post_types'    => ( ! empty( $amp_settings['supported_post_types'] ) && is_array( $amp_settings['supported_post_types'] ) ) ? $amp_settings['supported_post_types'] : [],
			'amp_supported_templates'     => ( ! empty( $amp_settings['supported_templates'] ) && is_array( $amp_settings['supported_templates'] ) ) ? $amp_settings['supported_templates'] : [],
			'amp_mobile_redirect'         => ! empty($amp_settings['mobile_redirect'] ),
			'amp_reader_theme'            => ( ! empty( $amp_settings['reader_theme'] ) ) ? $amp_settings['reader_theme'] : '',
		];
	}

	/**
	 * To get list of active plugin's information.
	 *
	 * @since 2.2
	 *
	 * @return array List of plugin detail.
	 */
	public function get_plugin_info() {

		$active_plugins = get_option( 'active_plugins' );

		if ( is_multisite() ) {
			$network_wide_activate_plugins = get_site_option( 'active_sitewide_plugins' );
			$active_plugins                = array_merge( $active_plugins, $network_wide_activate_plugins );
		}

		$active_plugins = array_values( array_unique( $active_plugins ) );

		$plugin_info = array_map(
			static function ( $plugin ) {
				return self::normalize_plugin_info( $plugin );
			},
			$active_plugins
		);
		$plugin_info    = array_filter( $plugin_info );

		return array_values( $plugin_info );
	}

	/**
	 * To get active theme info.
	 *
	 * @since 2.2
	 *
	 * @return array List of theme information.
	 */
	public function get_theme_info() {

		$themes   = [ wp_get_theme() ];
		$response = array_map(
			static function ( $theme ) {
				return self::normalize_theme_info( $theme );
			},
			$themes
		);
		$response = array_filter( $response );

		return array_values( $response );
	}

	/**
	 * To get error log.
	 *
	 * @since 2.2
	 *
	 * @return array Error log contents and log_errors ini setting.
	 */
	public function get_error_log() {

		$error_log_path = ini_get( 'error_log' );

		// $error_log_path might be a relative path/filename.
		// In this case, we would have to iterate many directories to find them.
		if ( empty( $error_log_path ) || ! file_exists( $error_log_path ) ) {
			return [
				'log_errors' => ini_get( 'log_errors' ),
				'contents'   => '',
			];
		}

		try {
			$file = new SplFileObject( $error_log_path );
			$file->seek(PHP_INT_MAX);
			$last_line = $file->key();

			$offset          = max( 0,  $last_line - 200 );
			$lines_iterator  = new LimitIterator( $file, $offset, $last_line );
			$lines           = array_map( 'sanitize_text_field', iterator_to_array( $lines_iterator ) );
			$contents        = implode( "\n", $lines );

			// Remove file handle from memory.
			unset( $file );
		} catch ( \Exception $e ) {
			// Set contents to empty string if there was an error reading the error log file.
			$contents = '';
		}

		return [
			'log_errors' => ini_get( 'log_errors' ),
			'contents'   => $contents,
		];
	}

	/**
	 * To get plugin information by plugin file.
	 *
	 * @since 2.2
	 *
	 * @param string $plugin_file Plugin file.
	 *
	 * @return array Plugin detail.
	 */
	public static function normalize_plugin_info( $plugin_file ) {

		$absolute_plugin_file = WP_PLUGIN_DIR . '/' . $plugin_file;
		if (  0 !== validate_file( $absolute_plugin_file ) || ! file_exists( $absolute_plugin_file ) ) {
			return [];
		}

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		$plugin_data = get_plugin_data( $absolute_plugin_file );

		$slug = explode( '/', $plugin_file );
		$slug = $slug[0];

		$amp_options        = get_option( 'amp-options' );
		$suppressed_plugins = ( ! empty( $amp_options['suppressed_plugins'] ) && is_array( $amp_options['suppressed_plugins'] ) ) ? $amp_options['suppressed_plugins'] : [];

		$suppressed_plugin_list = array_keys( $suppressed_plugins );

		if ( empty( $plugin_data['Name'] ) ) {
			return [];
		}

		return [
			'name'              => $plugin_data['Name'],
			'slug'              => $slug,
			'plugin_url'        => array_key_exists( 'PluginURI', $plugin_data ) ? $plugin_data['PluginURI'] : '',
			'version'           => array_key_exists( 'Version', $plugin_data ) ? $plugin_data['Version'] : '',
			'author'            => array_key_exists( 'AuthorName', $plugin_data ) ? $plugin_data['AuthorName'] : '',
			'author_url'        => array_key_exists( 'AuthorURI', $plugin_data ) ? $plugin_data['AuthorURI'] : '',
			'requires_wp'       => array_key_exists( 'RequiresWP', $plugin_data ) ? $plugin_data['RequiresWP'] : '',
			'requires_php'      => array_key_exists( 'RequiresPHP', $plugin_data ) ? $plugin_data['RequiresPHP'] : '',
			'is_active'         => is_plugin_active( $plugin_file ),
			'is_network_active' => is_plugin_active_for_network( $plugin_file ),
			'is_suppressed'     => in_array( $slug, $suppressed_plugin_list, true ) ? $suppressed_plugins[ $slug ]['last_version'] : '',
		];

	}

	/**
	 * To normalize theme information.
	 *
	 * @since 2.2
	 *
	 * @param WP_Theme $theme_object Theme object.
	 *
	 * @return array Normalize theme information.
	 */
	public static function normalize_theme_info( WP_Theme $theme_object ) {
		$active_theme = wp_get_theme();

		$tags = $theme_object->get( 'Tags' );
		$tags = ( ! empty( $tags ) && is_array( $tags ) ) ? $tags : [];

		return [
			'name'         => $theme_object->get( 'Name' ),
			'slug'         => $theme_object->get_stylesheet(),
			'version'      => $theme_object->get( 'Version' ),
			'status'       => $theme_object->get( 'Status' ),
			'tags'         => $tags,
			'text_domain'  => $theme_object->get( 'TextDomain' ),
			'requires_wp'  => $theme_object->get( 'RequiresWP' ),
			'requires_php' => $theme_object->get( 'RequiresPHP' ),
			'theme_url'    => $theme_object->get( 'ThemeURI' ),
			'author'       => $theme_object->get( 'Author' ),
			'author_url'   => $theme_object->get( 'AuthorURI' ),
			'is_active'    => ( $theme_object->get_stylesheet() === $active_theme->get_stylesheet() ),
			'parent_theme' => $active_theme->get_template(),
		];
	}

	/**
	 * Normalize error data.
	 *
	 * @since 2.2
	 *
	 * @param array $error_data Error data array.
	 *
	 * @return array
	 */
	public static function normalize_error( $error_data ) {

		if ( empty( $error_data ) || ! is_array( $error_data ) ) {
			return [];
		}

		unset( $error_data['sources'] );

		$error_data['text'] = ( ! empty( $error_data['text'] ) ) ? trim( $error_data['text'] ) : '';

		$error_data = wp_json_encode( $error_data );
		$error_data = static::remove_domain( $error_data );
		$error_data = json_decode( $error_data, true );

		ksort( $error_data );

		/**
		 * Generate new slug after removing site specific data.
		 */
		$error_data['error_slug'] = static::generate_hash( $error_data );

		return $error_data;
	}

	/**
	 * To normalize the error source data.
	 *
	 * @since 2.2
	 *
	 * @param array $source Error source detail.
	 *
	 * @return array Normalized error source data.
	 */
	public static function normalize_error_source( $source ) {

		if ( empty( $source ) || ! is_array( $source ) ) {
			return [];
		}

		static $plugin_versions = [];
		static $theme_versions  = [];

		/**
		 * All plugin info
		 */
		if ( empty( $plugin_versions ) || ! is_array( $plugin_versions ) ) {

				$plugin_versions[ $slug ] = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
			}
		}

		/**
		 * All theme info.
		 */
		if ( empty( $theme_versions ) || ! is_array( $theme_versions ) ) {

			$theme_list = array_merge( [ wp_get_theme() ], wp_get_themes() );

			foreach ( $theme_list as $theme ) {
				if ( ! $theme->errors() ) {
					$theme_versions[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
				}
			}
		}

		/**
		 * Normalize error source.
		 */

		$allowed_types  = [ 'plugin', 'theme' ];
		$source['type'] = ( ! empty( $source['type'] ) ) ? strtolower( trim( $source['type'] ) ) : '';

		/**
		 * Do not include wp-core sources.
		 */
		if ( empty( $source['type'] ) || ! in_array( $source['type'], $allowed_types, true ) ) {
			return [];
		}

		if ( 'plugin' === $source['type'] ) {
			$source['version'] = $plugin_versions[ $source['name'] ];
		} elseif ( 'theme' === $source['type'] ) {
			$source['version'] = $theme_versions[ $source['name'] ];
		}

		if ( ! empty( $source['text'] ) ) {
			$source['text'] = trim( $source['text'] );
			$source['text'] = static::remove_domain( $source['text'] );
		}

		// Generate error source slug.
		$error_source_slug = self::generate_hash( $source );

		// Update source information. Add error_slug and source_slug.
		$source['error_source_slug'] = $error_source_slug;

		ksort( $source );

		return $source;
	}

	/**
	 * To get amp validated URLs.
	 *
	 * @since 2.2
	 *
	 * @return array List amp validated URLs.
	 */
	public function get_amp_urls() {

		global $wpdb;

		$query = "SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE `post_type`='amp_validated_url'";

		if ( ! empty( $this->urls ) && is_array( $this->urls ) ) {
			$placeholder = implode( ', ', array_fill( 0, count( $this->urls ), '%s' ) );
			$query      .= ' AND post_title IN ( ' . $placeholder . ' ) ';
			$query_data  = $this->urls;

		} else {

			$query     .= ' LIMIT %d, %d';
			$query_data = [ 0, 100 ];

			/**
			 * If argument provided and we don't have URL data.
			 * then return empty values.
			 */
			if ( ! empty( $this->args['post_ids'] ) ||
				! empty( $this->args['term_ids'] ) ||
				! empty( $this->args['urls'] ) ||
				! empty( $this->args['amp_validated_post_ids'] )
			) {
				return [
					'errors'        => [],
					'error_sources' => [],
					'urls'          => [],
				];
			}
		}

		// This query needs to be uncached and it is prepared, yet there's false positive in PHPCS because of using variable instead of string in prepare.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		$amp_error_posts  = $wpdb->get_results( $wpdb->prepare( $query, $query_data ) );
		$amp_invalid_urls = [];

		/**
		 * Error Information
		 */
		$error_list = [];

		/**
		 * Error Source information.
		 */
		$error_source_list = [];

		/**
		 * Post loop.
		 */
		foreach ( $amp_error_posts as $amp_error_post ) {

			if ( empty( $amp_error_post ) ) {
				continue;
			}

			// Empty array for post staleness means post is NOT stale.
			if ( ! empty( \AMP_Validated_URL_Post_Type::get_post_staleness( $amp_error_post->ID ) ) ) {
				continue;
			}

			$post_errors_raw = json_decode( $amp_error_post->post_content, true );
			$post_errors     = [];

			if ( empty( $post_errors_raw ) ) {
				continue;
			}

			/**
			 * Error loop.
			 */
			foreach ( $post_errors_raw as $post_error ) {

				$error_data    = ( ! empty( $post_error['data'] ) && is_array( $post_error['data'] ) ) ? $post_error['data'] : [];
				$error_sources = ( ! empty( $error_data['sources'] ) && is_array( $error_data['sources'] ) ) ? $error_data['sources'] : [];

				if ( empty( $error_data ) || empty( $error_sources ) ) {
					continue;
				}

				unset( $error_data['sources'] );
				$error_data = static::normalize_error( $error_data );

				/**
				 * Store error data in all error list.
				 */
				if ( ! empty( $error_data ) && is_array( $error_data ) ) {
					$error_list[ $error_data['error_slug'] ] = $error_data;
				}

				/**
				 * Source loop.
				 */
				foreach ( $error_sources as $index => $source ) {
					$source['error_slug']    = $error_data['error_slug'];
					$error_sources[ $index ] = static::normalize_error_source( $source );

					/**
					 * Store error source in all error_source list.
					 */
					if ( ! empty( $error_sources[ $index ] ) && is_array( $error_sources[ $index ] ) ) {
						$error_source_list[ $error_sources[ $index ]['error_source_slug'] ] = $error_sources[ $index ];
					}
				}

				$error_sources      = array_filter( $error_sources );
				$error_source_slugs = wp_list_pluck( $error_sources, 'error_source_slug' );
				$error_source_slugs = array_values( array_unique( $error_source_slugs ) );

				if ( ! empty( $error_source_slugs ) && is_array( $error_source_slugs ) ) {
					$post_errors[] = [
						'error_slug' => $error_data['error_slug'],
						'sources'    => $error_source_slugs,
					];
				}
			}

			// Object information.
			$amp_queried_object = get_post_meta( $amp_error_post->ID, '_amp_queried_object', true );
			$object_type        = ( ! empty( $amp_queried_object['type'] ) ) ? $amp_queried_object['type'] : '';
			$object_subtype     = '';

			if ( empty( $object_type ) ) {

				if ( false !== strpos( $amp_error_post->post_title, '?s=' ) ) {
					$object_type = 'search';
				}
			}

			switch ( $object_type ) {
				case 'post':
					$object_subtype = get_post( $amp_queried_object['id'] )->post_type;
					break;
				case 'term':
					$object_subtype = get_term( $amp_queried_object['id'] )->taxonomy;
					break;
				case 'user':
					break;
			}

			// Stylesheet info.
			$stylesheet_info       = static::get_stylesheet_info( $amp_error_post->ID );
			$css_budget_percentage = ( ! empty( $stylesheet_info['css_budget_percentage'] ) ) ? $stylesheet_info['css_budget_percentage'] : 0;
			$css_budget_percentage = (int) $css_budget_percentage;

			if ( empty( $post_errors ) && $css_budget_percentage < 100 ) {
				continue;
			}

			$amp_invalid_urls[] = [
				'url'                   => $amp_error_post->post_title,
				'object_type'           => $object_type,
				'object_subtype'        => $object_subtype,
				'css_size_before'       => ( ! empty( $stylesheet_info['css_size_before'] ) ) ? $stylesheet_info['css_size_before'] : '',
				'css_size_after'        => ( ! empty( $stylesheet_info['css_size_after'] ) ) ? $stylesheet_info['css_size_after'] : '',
				'css_size_excluded'     => ( ! empty( $stylesheet_info['css_size_excluded'] ) ) ? $stylesheet_info['css_size_excluded'] : '',
				'css_budget_percentage' => $css_budget_percentage,
				'errors'                => $post_errors,
			];
		}

		return [
			'errors'        => $error_list,
			'error_sources' => $error_source_list,
			'urls'          => $amp_invalid_urls,
		];

	}

	/**
	 * Get style sheet info of the post.
	 *
	 * @since 2.2
	 *
	 * Reference: AMP_Validated_URL_Post_Type::print_stylesheets_meta_box()
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array AMP stylesheet used info.
	 */
	public static function get_stylesheet_info( $post_id ) {

		$stylesheets = get_post_meta( $post_id, AMP_Validated_URL_Post_Type::STYLESHEETS_POST_META_KEY, true );

		if ( empty( $stylesheets ) ) {
			return [];
		}

		$stylesheets             = json_decode( $stylesheets, true );
		$style_custom_cdata_spec = null;

		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && AMP_Style_Sanitizer::STYLE_AMP_CUSTOM_SPEC_NAME === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$style_custom_cdata_spec = $spec_rule[ AMP_Rule_Spec::CDATA ];
			}
		}

		$included_final_size    = 0;
		$included_original_size = 0;
		$excluded_final_size    = 0;
		$excluded_original_size = 0;
		$excluded_stylesheets   = 0;
		$max_final_size         = 0;

		$included_status  = 1;
		$excessive_status = 2;
		$excluded_status  = 3;

		// Determine which stylesheets are included based on their priorities.
		$pending_stylesheet_indices = array_keys( $stylesheets );
		usort(
			$pending_stylesheet_indices,
			static function ( $a, $b ) use ( $stylesheets ) {

				return $stylesheets[ $a ]['priority'] - $stylesheets[ $b ]['priority'];
			}
		);

		foreach ( $pending_stylesheet_indices as $i ) {

			if ( ! isset( $stylesheets[ $i ]['group'] ) || 'amp-custom' !== $stylesheets[ $i ]['group'] || ! empty( $stylesheets[ $i ]['duplicate'] ) ) {
				continue;
			}

			$max_final_size = max( $max_final_size, $stylesheets[ $i ]['final_size'] );
			if ( $stylesheets[ $i ]['included'] ) {
				$included_final_size    += $stylesheets[ $i ]['final_size'];
				$included_original_size += $stylesheets[ $i ]['original_size'];

				if ( $included_final_size >= $style_custom_cdata_spec['max_bytes'] ) {
					$stylesheets[ $i ]['status'] = $excessive_status;
				} else {
					$stylesheets[ $i ]['status'] = $included_status;
				}
			} else {
				$excluded_final_size    += $stylesheets[ $i ]['final_size'];
				$excluded_original_size += $stylesheets[ $i ]['original_size'];
				$excluded_stylesheets ++;
				$stylesheets[ $i ]['status'] = $excluded_status;
			}
		}

		$percentage_budget_used = ( ( $included_final_size + $excluded_final_size ) / $style_custom_cdata_spec['max_bytes'] ) * 100;

		return [
			'css_size_before'       => $included_original_size + $excluded_original_size,
			'css_size_after'        => $included_final_size + $excluded_final_size,
			'css_size_excluded'     => $excluded_stylesheets,
			'css_budget_percentage' => round( $percentage_budget_used, 1 ),
		];

	}

	/**
	 * To get home url of the site.
	 * Note: It will give home url without protocol.
	 *
	 * @since 2.2
	 *
	 * @return string Home URL.
	 */
	public static function get_home_url() {

		$home_url = home_url();
		$home_url = strtolower( trim( $home_url ) );

		$http_protocol = wp_parse_url( $home_url, PHP_URL_SCHEME );

		$home_url = str_replace( "$http_protocol://", '', $home_url );
		$home_url = untrailingslashit( $home_url );

		return $home_url;
	}

	/**
	 * To remove home url from the content.
	 *
	 * @since 2.2
	 *
	 * @param string $content Content from home_url need to remove.
	 *
	 * @return string Content after removing home_url.
	 */
	public static function remove_domain( $content ) {

		if ( empty( $content ) ) {
			return '';
		}

		$home_url = static::get_home_url();
		$home_url = str_replace( [ '.', '/' ], [ '\.', '\\\\{1,5}\/' ], $home_url );

		/**
		 * Reference: https://regex101.com/r/c25pNF/1
		 */
		$regex = "/http[s]?:\\\\{0,5}\/\\\\{0,5}\/$home_url/mU";

		return preg_replace( $regex, '', $content );
	}

	/**
	 * To generate hash of object.
	 *
	 * @since 2.2
	 *
	 * @param string|array|object $object Object for that hash need to generate.
	 *
	 * @return string Hash value of provided object.
	 */
	public static function generate_hash( $object ) {

		if ( empty( $object ) ) {
			return '';
		}

		if ( is_object( $object ) ) {
			$object = (array) $object;
		}

		if ( is_array( $object ) ) {
			ksort( $object );
			$object = wp_json_encode( $object );
		}

		$object = trim( $object );
		return hash( 'sha256', $object );
	}
}
