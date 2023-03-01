<?php
/**
 * Class to prepare and send support data to insights server.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Support;

use AMP_Options_Manager;
use AMP_Validated_URL_Post_Type;
use AMP_Validation_Manager;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_Site_Health;
use WP_Term;
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
	 * @var array [
	 *     @type string[] $urls                   Optional
	 *     @type int[]    $term_ids               Optional
	 *     @type int[]    $post_ids               Optional
	 *     @type int[]    $amp_validated_post_ids Optional
	 * ]
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

		$this->data = [];
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

		$this->urls = array_map(
			static function ( $url ) {

				if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
					return AMP_Validated_URL_Post_Type::normalize_url_for_storage( $url );
				}

				return false;
			},
			$this->urls
		);
		$this->urls = array_values( array_unique( array_filter( $this->urls ) ) );

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
				'timeout'  => 30, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
				'body'     => $data,
				'compress' => true,
			]
		);

		if ( ! is_wp_error( $response ) ) {

			$response_body   = wp_remote_retrieve_body( $response );
			$response        = json_decode( $response_body, true );
			$json_last_error = json_last_error();

			if ( JSON_ERROR_NONE !== $json_last_error ) {
				return new WP_Error(
					'malformed_json_amp_insight_endpoint',
					json_last_error_msg()
				);
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

		if ( is_multisite() && ( defined( 'SUBDOMAIN_INSTALL' ) && SUBDOMAIN_INSTALL ) ) {
			$wp_type = 'subdomain';
		} elseif ( is_multisite() ) {
			$wp_type = 'subdir';
		}

		$active_theme = wp_get_theme();
		$active_theme = static::normalize_theme_info( $active_theme );

		$amp_settings = AMP_Options_Manager::get_options();
		$amp_settings = ( ! empty( $amp_settings ) && is_array( $amp_settings ) ) ? $amp_settings : [];

		$loopback_status = '';

		if ( class_exists( 'WP_Site_Health' ) ) {
			$loopback_status = ( new WP_Site_Health() )->can_perform_loopback();
			$loopback_status = ( ! empty( $loopback_status->status ) ) ? $loopback_status->status : '';
		}

		if ( function_exists( 'wp_is_https_supported' ) ) {
			$is_ssl = wp_is_https_supported();
		} else {
			$is_ssl = is_ssl();
		}

		$site_info = [
			'site_url'                    => static::get_home_url(),
			'site_title'                  => get_bloginfo( 'site_title' ),
			'php_version'                 => phpversion(),
			'mysql_version'               => $wpdb->db_version(),
			'wp_version'                  => get_bloginfo( 'version' ),
			'wp_language'                 => get_bloginfo( 'language' ),
			'wp_https_status'             => $is_ssl,
			'wp_multisite'                => $wp_type,
			'wp_active_theme'             => $active_theme,
			'object_cache_status'         => wp_using_ext_object_cache(),
			'libxml_version'              => ( defined( 'LIBXML_VERSION' ) ) ? LIBXML_VERSION : '',
			'is_defined_curl_multi'       => ( function_exists( 'curl_multi_init' ) ),
			'loopback_requests'           => $loopback_status,
			'amp_mode'                    => ( ! empty( $amp_settings['theme_support'] ) ) ? $amp_settings['theme_support'] : '',
			'amp_version'                 => ( ! empty( $amp_settings['version'] ) ) ? $amp_settings['version'] : '',
			'amp_plugin_configured'       => ( ! empty( $amp_settings['plugin_configured'] ) ) ? true : false,
			'amp_all_templates_supported' => ( ! empty( $amp_settings['all_templates_supported'] ) ) ? true : false,
			'amp_supported_post_types'    => ( ! empty( $amp_settings['supported_post_types'] ) && is_array( $amp_settings['supported_post_types'] ) ) ? $amp_settings['supported_post_types'] : [],
			'amp_supported_templates'     => ( ! empty( $amp_settings['supported_templates'] ) && is_array( $amp_settings['supported_templates'] ) ) ? $amp_settings['supported_templates'] : [],
			'amp_mobile_redirect'         => ( ! empty( $amp_settings['mobile_redirect'] ) ) ? true : false,
			'amp_reader_theme'            => ( ! empty( $amp_settings['reader_theme'] ) ) ? $amp_settings['reader_theme'] : '',
		];

		return $site_info;
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
		$plugin_info    = array_map(
			static function ( $active_plugin ) {
				return self::normalize_plugin_info( $active_plugin );
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

		$themes = [
			wp_get_theme(),
		];
		if ( wp_get_theme()->parent() ) {
			$themes[] = wp_get_theme()->parent();
		}

		$themes = array_filter(
			$themes,
			static function ( WP_Theme $theme ) {
				return ! $theme->errors();
			}
		);

		$response = array_map(
			static function( WP_Theme $theme ) {
				return self::normalize_theme_info( $theme );
			},
			$themes
		);

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

		$max_lines = 200;

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$file         = @fopen( $error_log_path, 'r' );
		$lines        = [];
		$current_line = '';
		$position     = 0;

		if ( is_resource( $file ) ) {

			while ( -1 !== fseek( $file, $position, SEEK_END ) ) {
				$char = fgetc( $file );

				if ( PHP_EOL === $char ) {
					$lines[]      = $current_line;
					$current_line = '';

					if ( count( $lines ) > $max_lines ) {
						break;
					}
				} else {
					$current_line = $char . $current_line;
				}

				$position--;
			}

			$lines[] = $current_line;

			fclose( $file );
		}

		$lines = array_filter( $lines );
		$lines = array_reverse( $lines );

		return [
			'log_errors' => ini_get( 'log_errors' ),
			'contents'   => implode( "\n", $lines ),
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
		if ( 0 !== validate_file( $absolute_plugin_file ) || ! file_exists( $absolute_plugin_file ) ) {
			return [];
		}

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		/** @var array */
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

		$theme_data = [
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
			'parent_theme' => $theme_object->parent() ? $theme_object->get_template() : null,
		];

		return $theme_data;
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

		$error_data = static::remove_domain( $error_data );

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

		/**
		 * All plugin info
		 */
		if ( empty( $plugin_versions ) || ! is_array( $plugin_versions ) ) {

			$plugin_list = get_plugins();
			$plugin_list = array_keys( $plugin_list );
			$plugin_list = array_values( array_unique( $plugin_list ) );
			$plugin_list = array_map(
				static function ( $plugin ) {
					return self::normalize_plugin_info( $plugin );
				},
				$plugin_list
			);

			foreach ( $plugin_list as $plugin ) {
				$plugin_versions[ $plugin['slug'] ] = $plugin['version'];
			}
		}

		/**
		 * Normalize error source.
		 */

		$allowed_types  = [ 'plugin', 'theme' ];
		$source['type'] = ( ! empty( $source['type'] ) ) ? strtolower( trim( $source['type'] ) ) : '';

		if ( ! empty( $source['sources'] ) && is_array( $source['sources'] ) ) {
			foreach ( $source['sources'] as $index => $inner_source ) {
				$source['sources'][ $index ] = self::normalize_error_source( $inner_source );
			}

			$source['sources'] = array_values( array_filter( $source['sources'] ) );
		}


		/**
		 * Do not include wp-core sources.
		 * But allow if source have sub sources.
		 */
		if (
			( empty( $source['type'] ) || ! in_array( $source['type'], $allowed_types, true ) )
			&&
			empty( $source['sources'] )
		) {
			return [];
		}

		if ( ! empty( $source['type'] ) ) {
			if ( 'plugin' === $source['type'] ) {
				$source['version'] = isset( $plugin_versions[ $source['name'] ] ) ? $plugin_versions[ $source['name'] ] : 'n/a';
			} elseif ( 'theme' === $source['type'] ) {
				$theme             = wp_get_theme( $source['name'] );
				$source['version'] = ! $theme->errors() ? $theme->get( 'Version' ) : 'n/a';
			}
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
	 * @return array [
	 *     List amp validated URLs.
	 *     @type array    $errors        List of error for given instance of Validated post.
	 *     @type array    $error_sources List of error sources for given instance of Validated post.
	 *     @type string[] $urls          List of front-end URL.
	 * ]
	 */
	public function get_amp_urls() {

		/**
		 * List of invalid AMP URLs.
		 *
		 * @var string[]
		 */
		$amp_invalid_urls = [];

		/**
		 * Error Information
		 *
		 * @var array
		 */
		$errors = [];

		/**
		 * Error Source information.
		 *
		 * @var array
		 */
		$error_sources = [];

		if ( empty( $this->urls ) ) {

			/**
			 * If argument provided and we don't have URL data.
			 * then return empty values.
			 */
			if (
				! empty( $this->args['post_ids'] )
				||
				! empty( $this->args['term_ids'] )
				||
				! empty( $this->args['urls'] )
				||
				! empty( $this->args['amp_validated_post_ids'] )
			) {
				return [
					'errors'        => [],
					'error_sources' => [],
					'urls'          => [],
				];
			}

			// If no specific URL requested.
			$query_args = [
				'post_type'      => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'meta_key'       => AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY,
				'meta_value'     => maybe_serialize( AMP_Validated_URL_Post_Type::get_validated_environment() ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				's'              => 'term_slug',
			];

			$query               = new WP_Query( $query_args );
			$amp_validated_posts = $query->get_posts();

			foreach ( $amp_validated_posts as $amp_validated_post ) {
				$validation_data = $this->process_raw_post_errors( $amp_validated_post );

				if (
					! empty( $validation_data['errors'] )
					&&
					! empty( $validation_data['error_sources'] )
					&&
					! empty( $validation_data['url'] )
				) {
					$errors             = array_merge( $errors, $validation_data['errors'] );
					$error_sources      = array_merge( $error_sources, $validation_data['error_sources'] );
					$amp_invalid_urls[] = $validation_data['url'];
				}
			}
		} else { // If we have specific URL requested.

			foreach ( $this->urls as $url ) {

				// Check request URL have validated data if not then validate URL.
				$amp_validated_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( $url );

				if ( ! $amp_validated_post ) {
					$validity = AMP_Validation_Manager::validate_url_and_store( $url );

					if ( ! is_array( $validity ) || is_wp_error( $validity ) ) {
						continue;
					}

					$amp_validated_post = get_post( $validity['post_id'] );
				}

				// If validation data is exists for URL then check if it is stale or not. If it is stale, then revalidate.
				$is_stale = ! empty( AMP_Validated_URL_Post_Type::get_post_staleness( $amp_validated_post ) );
				if ( $is_stale ) {
					AMP_Validation_Manager::validate_url_and_store( $url, $amp_validated_post );
				}

				$validation_data = $this->process_raw_post_errors( $amp_validated_post );

				if (
					! empty( $validation_data['errors'] )
					&&
					! empty( $validation_data['error_sources'] )
					&&
					! empty( $validation_data['url'] )
				) {
					$errors             = array_merge( $errors, $validation_data['errors'] );
					$error_sources      = array_merge( $error_sources, $validation_data['error_sources'] );
					$amp_invalid_urls[] = $validation_data['url'];
				}
			}
		}

		return [
			'errors'        => $errors,
			'error_sources' => $error_sources,
			'urls'          => $amp_invalid_urls,
		];

	}

	/**
	 * Process AMP errors for single AMP validated post.
	 *
	 * @param WP_Post $amp_validated_post Instance of AMP Validated Post.
	 *
	 * @return array [
	 *     @type array    $errors             List of error for given instance of Validated post.
	 *     @type array    $error_sources      List of error sources for given instance of Validated post.
	 *     @type string   $url                Front-end URL.
	 * ]
	 */
	protected function process_raw_post_errors( WP_Post $amp_validated_post ) {

		if ( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG !== $amp_validated_post->post_type ) {
			return [];
		}

		// Empty array for post staleness means post is NOT stale.
		if ( ! empty( AMP_Validated_URL_Post_Type::get_post_staleness( $amp_validated_post ) ) ) {
			return [];
		}

		/**
		 * Error Information
		 *
		 * @var array
		 */
		$error_list = [];

		/**
		 * Error Source information.
		 *
		 * @var array
		 */
		$error_source_list = [];

		$validation_errors_raw = json_decode( $amp_validated_post->post_content, true );
		if ( ! is_array( $validation_errors_raw ) ) {
			$validation_errors_raw = [];
		}

		/**
		 * Error loop.
		 *
		 * @var array
		 */
		$validation_errors = [];
		foreach ( $validation_errors_raw as $validation_error ) {

			$error_data    = ( ! empty( $validation_error['data'] ) && is_array( $validation_error['data'] ) ) ? $validation_error['data'] : [];
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

			if ( ! empty( $error_source_slugs ) ) {
				$validation_errors[] = [
					'error_slug' => $error_data['error_slug'],
					'sources'    => $error_source_slugs,
				];
			}
		}

		// Object information.
		$amp_queried_object = get_post_meta( $amp_validated_post->ID, '_amp_queried_object', true );
		$object_type        = ( ! empty( $amp_queried_object['type'] ) ) ? $amp_queried_object['type'] : '';
		$object_subtype     = '';

		if ( empty( $object_type ) ) {
			$amp_validated_post_url = AMP_Validated_URL_Post_Type::get_url_from_post( $amp_validated_post );
			if ( false !== strpos( $amp_validated_post_url, '?s=' ) ) {
				$object_type = 'search';
			}
		}

		switch ( $object_type ) {
			case 'post':
				$post_object    = get_post( $amp_queried_object['id'] );
				$object_subtype = ( ! empty( $post_object ) && $post_object instanceof WP_Post ) ? $post_object->post_type : '';
				break;
			case 'term':
				$term_object    = get_term( $amp_queried_object['id'] );
				$object_subtype = ( ! empty( $term_object ) && $term_object instanceof WP_Term ) ? $term_object->taxonomy : '';
				break;
			case 'user':
				break;
		}

		// Stylesheet info.
		$stylesheet_info       = static::get_stylesheet_info( $amp_validated_post->ID );
		$css_budget_percentage = ( ! empty( $stylesheet_info['css_budget_percentage'] ) ) ? $stylesheet_info['css_budget_percentage'] : 0;
		$css_budget_percentage = intval( $css_budget_percentage );

		if ( empty( $validation_errors ) && $css_budget_percentage < 100 ) {
			return [];
		}

		$amp_invalid_url = [
			'url'                   => AMP_Validated_URL_Post_Type::get_url_from_post( $amp_validated_post ),
			'object_type'           => $object_type,
			'object_subtype'        => $object_subtype,
			'css_size_before'       => ( ! empty( $stylesheet_info['css_size_before'] ) ) ? $stylesheet_info['css_size_before'] : '',
			'css_size_after'        => ( ! empty( $stylesheet_info['css_size_after'] ) ) ? $stylesheet_info['css_size_after'] : '',
			'css_size_excluded'     => ( ! empty( $stylesheet_info['css_size_excluded'] ) ) ? $stylesheet_info['css_size_excluded'] : '',
			'css_budget_percentage' => $css_budget_percentage,
			'errors'                => $validation_errors,
		];

		return [
			'errors'        => $error_list,
			'error_sources' => $error_source_list,
			'url'           => $amp_invalid_url,
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

		foreach ( \AMP_Allowed_Tags_Generated::get_allowed_tag( 'style' ) as $spec_rule ) {
			if ( isset( $spec_rule[ \AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && \AMP_Style_Sanitizer::STYLE_AMP_CUSTOM_SPEC_NAME === $spec_rule[ \AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$style_custom_cdata_spec = $spec_rule[ \AMP_Rule_Spec::CDATA ];
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
		$response               = [
			'css_size_before'       => intval( $included_original_size + $excluded_original_size ),
			'css_size_after'        => intval( $included_final_size + $excluded_final_size ),
			'css_size_excluded'     => intval( $excluded_stylesheets ),
			'css_budget_percentage' => round( $percentage_budget_used, 1 ),
		];

		return $response;

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
	 * @param mixed $content Content from home_url need to remove.
	 *
	 * @return string|array Content after removing home_url.
	 */
	public static function remove_domain( $content ) {

		if ( empty( $content ) ) {
			return '';
		} elseif ( is_numeric( $content ) ) {
			return $content;
		}

		$home_url = static::get_home_url();
		$home_url = str_replace( [ '.', '/' ], [ '\.', '\\\\{1,5}\/' ], $home_url );

		/**
		 * Reference: https://regex101.com/r/c25pNF/1
		 */
		$regex = "/http[s]?:\\\\{0,5}\/\\\\{0,5}\/$home_url/mU";

		if ( is_string( $content ) ) {
			return preg_replace( $regex, '', $content );
		}

		if ( is_object( $content ) ) {
			$content = (array) $content;
		}

		if ( is_array( $content ) ) {
			return array_map(
				static function ( $item ) {
					return self::remove_domain( $item );
				},
				$content
			);
		}

		return $content;
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
		$hash   = hash( 'sha256', $object );

		return $hash;
	}
}
