<?php
/**
 * Class PairedAmpRouting.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\DevTools\CallbackReflection;
use AMP_Post_Type_Support;
use AmpProject\AmpWP\Infrastructure\Activateable;
use AmpProject\AmpWP\Infrastructure\Deactivateable;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Admin\ReaderThemes;
use WP_Query;
use WP_Rewrite;
use WP;
use WP_Hook;

/**
 * Service for routing users to and from paired AMP URLs.
 *
 * @todo Add 404 redirection to non-AMP version.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class PairedAmpRouting implements Service, Registerable, Activateable, Deactivateable {

	/**
	 * Paired URL structures.
	 *
	 * @var string[]
	 */
	const PAIRED_URL_STRUCTURES = [
		Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
		Option::PAIRED_URL_STRUCTURE_SUFFIX_ENDPOINT,
		Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL,
		Option::PAIRED_URL_STRUCTURE_LEGACY_READER,
	];

	/**
	 * Custom paired URL structure.
	 *
	 * This involves a site adding the necessary filters to implement their own paired URL structure.
	 *
	 * @var string
	 */
	const PAIRED_URL_STRUCTURE_CUSTOM = 'custom';

	/**
	 * Key for AMP paired examples.
	 *
	 * @see amp_get_slug()
	 * @var string
	 */
	const PAIRED_URL_EXAMPLES = 'paired_url_examples';

	/**
	 * Key for the AMP slug.
	 *
	 * @see amp_get_slug()
	 * @var string
	 */
	const AMP_SLUG = 'amp_slug';

	/**
	 * Key for the custom paired structure sources.
	 *
	 * @var string
	 */
	const CUSTOM_PAIRED_ENDPOINT_SOURCES = 'custom_paired_endpoint_sources';

	/**
	 * Callback refelction.
	 *
	 * @var CallbackReflection
	 */
	protected $callback_reflection;

	/**
	 * Plugin registry.
	 *
	 * @var PluginRegistry
	 */
	protected $plugin_registry;

	/**
	 * Whether the request had the /amp/ endpoint.
	 *
	 * @var bool
	 */
	private $has_amp_endpoint;

	/**
	 * PairedAmpRouting constructor.
	 *
	 * @param CallbackReflection $callback_reflection Callback reflection.
	 * @param PluginRegistry     $plugin_registry     Plugin registry.
	 */
	public function __construct( CallbackReflection $callback_reflection, PluginRegistry $plugin_registry ) {
		$this->callback_reflection = $callback_reflection;
		$this->plugin_registry     = $plugin_registry;
	}

	/**
	 * Activate.
	 *
	 * @param bool $network_wide Network-wide.
	 */
	public function activate( $network_wide ) {
		unset( $network_wide );
		if ( did_action( 'init' ) ) {
			$this->flush_rewrite_rules();
		} else {
			add_action( 'init', [ $this, 'flush_rewrite_rules' ], 0 );
		}
	}

	/**
	 * Deactivate.
	 *
	 * @param bool $network_wide Network-wide.
	 */
	public function deactivate( $network_wide ) {
		unset( $network_wide );

		// We need to manually remove the amp endpoint.
		$rewrite = $this->get_wp_rewrite();
		foreach ( $rewrite->endpoints as $index => $endpoint ) {
			if ( amp_get_slug() === $endpoint[1] ) {
				unset( $rewrite->endpoints[ $index ] );
				break;
			}
		}

		$this->flush_rewrite_rules();
	}

	/**
	 * Register.
	 */
	public function register() {
		add_filter( 'amp_rest_options_schema', [ $this, 'filter_rest_options_schema' ] );
		add_filter( 'amp_rest_options', [ $this, 'filter_rest_options' ] );

		add_filter( 'amp_default_options', [ $this, 'filter_default_options' ], 10, 2 );
		add_filter( 'amp_options_updating', [ $this, 'sanitize_options' ], 10, 2 );
		add_action( 'update_option_' . AMP_Options_Manager::OPTION_NAME, [ $this, 'handle_options_update' ], 10, 2 );

		add_action( 'init', [ $this, 'add_rewrite_endpoint' ], 0 );

		if ( ! amp_is_canonical() ) {
			$this->add_paired_hooks();
		}
	}

	/**
	 * Filter the REST options schema to add items.
	 *
	 * @param array $schema Schema.
	 * @return array Schema.
	 */
	public function filter_rest_options_schema( $schema ) {
		return array_merge(
			$schema,
			[
				Option::PAIRED_URL_STRUCTURE => [
					'type' => 'string',
					'enum' => self::PAIRED_URL_STRUCTURES,
				],
				self::PAIRED_URL_EXAMPLES    => [
					'type'     => 'object',
					'readonly' => true,
				],
				self::AMP_SLUG               => [
					'type'     => 'string',
					'readonly' => true,
				],
			]
		);
	}

	/**
	 * Filter the REST options to add items.
	 *
	 * @param array $options Options.
	 * @return array Options.
	 */
	public function filter_rest_options( $options ) {
		$options[ self::AMP_SLUG ] = amp_get_slug();

		$options[ Option::PAIRED_URL_STRUCTURE ] = $this->get_paired_url_structure();

		$options[ self::PAIRED_URL_EXAMPLES ] = $this->get_paired_url_examples();

		$options[ self::CUSTOM_PAIRED_ENDPOINT_SOURCES ] = $this->get_custom_paired_structure_sources();

		return $options;
	}

	/**
	 * Add paired hooks.
	 */
	public function add_paired_hooks() {
		if ( Option::PAIRED_URL_STRUCTURE_SUFFIX_ENDPOINT === AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE ) ) {
			add_filter( 'do_parse_request', [ $this, 'detect_rewrite_endpoint' ], PHP_INT_MAX );
			add_filter( 'request', [ $this, 'set_query_var_for_endpoint' ] );
		}

		add_action( 'parse_query', [ $this, 'correct_query_when_is_front_page' ] );
		add_action( 'wp', [ $this, 'add_amp_request_hooks' ] );

		add_action( 'admin_notices', [ $this, 'add_permalink_settings_notice' ] );
	}

	/**
	 * Add notice to permalink settings screen for where to customize the paired URL structure.
	 */
	public function add_permalink_settings_notice() {
		if ( 'options-permalink' !== get_current_screen()->id ) {
			return;
		}
		?>
		<div class="notice notice-info">
			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %s is the URL to the settings screen */
						__( 'To customize the structure of the paired AMP URLs (given the site is not using the Standard template mode), go to the <a href="%s">Paired URL Structure</a> section on the AMP settings screen.', 'amp' ),
						esc_url( admin_url( add_query_arg( 'page', AMP_Options_Manager::OPTION_NAME, 'admin.php' ) ) . '#paired-url-structure' )
					),
					[ 'a' => array_fill_keys( [ 'href' ], true ) ]
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Detect the AMP rewrite endpoint from the PATH_INFO or REQUEST_URI and purge from those environment variables.
	 *
	 * @see WP::parse_request()
	 *
	 * @param bool $should_parse_request Whether or not to parse the request. Default true.
	 * @return bool Should parse request.
	 */
	public function detect_rewrite_endpoint( $should_parse_request ) {
		$this->has_amp_endpoint = false;

		if ( ! $should_parse_request ) {
			return false;
		}

		$amp_slug = amp_get_slug();
		$pattern  = sprintf( '#(/%s)(?=/?(\?.*)?$)#', preg_quote( $amp_slug, '#' ) );

		// Detect and purge the AMP endpoint from the request.
		foreach ( [ 'PATH_INFO', 'REQUEST_URI' ] as $var ) {
			if ( ! isset( $_SERVER[ $var ] ) ) {
				continue;
			}

			$path = wp_unslash( $_SERVER[ $var ] ); // Because of wp_magic_quotes().

			$count = 0;
			$path  = preg_replace(
				$pattern,
				'',
				$path,
				1,
				$count
			);

			$_SERVER[ $var ] = wp_slash( $path ); // Because of wp_magic_quotes().

			if ( $count > 0 ) {
				$this->has_amp_endpoint = true;
			}
		}

		return $should_parse_request;
	}

	/**
	 * Set query var for endpoint.
	 *
	 * @param array $query_vars Query vars.
	 * @return array Query vars.
	 */
	public function set_query_var_for_endpoint( $query_vars ) {
		if ( $this->has_amp_endpoint ) {
			$query_vars[ amp_get_slug() ] = true;
		}
		return $query_vars;
	}

	/**
	 * Add AMP hooks if it is an AMP request.
	 */
	public function add_amp_request_hooks() {
		if ( ! amp_is_request() ) {
			return;
		}

		add_filter( 'old_slug_redirect_url', [ $this, 'maybe_add_paired_endpoint' ], 1000 );
		add_filter( 'redirect_canonical', [ $this, 'maybe_add_paired_endpoint' ], 1000 );
	}

	/**
	 * Get the WP_Rewrite object.
	 *
	 * @return WP_Rewrite Object.
	 */
	private function get_wp_rewrite() {
		global $wp_rewrite;
		return $wp_rewrite;
	}

	/**
	 * Flush rewrite rules.
	 */
	public function flush_rewrite_rules() {
		$this->get_wp_rewrite()->flush_rules( false );
	}

	/**
	 * Add rewrite endpoint.
	 */
	public function add_rewrite_endpoint() {
		if ( Option::PAIRED_URL_STRUCTURE_LEGACY_READER === AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE ) ) {
			$this->get_wp_rewrite()->add_endpoint( amp_get_slug(), EP_PERMALINK );
		}
	}

	/**
	 * Add default option.
	 *
	 * @param array $defaults Default options.
	 * @param array $options  Current options.
	 * @return array Defaults.
	 */
	public function filter_default_options( $defaults, $options ) {
		$value = Option::PAIRED_URL_STRUCTURE_QUERY_VAR;

		if (
			isset( $options[ Option::VERSION ], $options[ Option::THEME_SUPPORT ], $options[ Option::READER_THEME ] )
			&&
			version_compare( $options[ Option::VERSION ], '2.1', '<' )
		) {
			if (
				AMP_Theme_Support::READER_MODE_SLUG === $options[ Option::THEME_SUPPORT ]
				&&
				ReaderThemes::DEFAULT_READER_THEME === $options[ Option::READER_THEME ]
			) {
				$value = Option::PAIRED_URL_STRUCTURE_LEGACY_READER;
			} elseif ( AMP_Theme_Support::STANDARD_MODE_SLUG !== $options[ Option::THEME_SUPPORT ] ) {
				$value = Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL;
			}
		}

		$defaults[ Option::PAIRED_URL_STRUCTURE ] = $value;

		return $defaults;
	}

	/**
	 * Sanitize options.
	 *
	 * @todo This is redundant with the enum defined in the schema.
	 *
	 * @param array $options     Existing options with already-sanitized values for updating.
	 * @param array $new_options Unsanitized options being submitted for updating.
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $options, $new_options ) {
		if (
			isset( $new_options[ Option::PAIRED_URL_STRUCTURE ] )
			&&
			in_array( $new_options[ Option::PAIRED_URL_STRUCTURE ], self::PAIRED_URL_STRUCTURES, true )
		) {
			$options[ Option::PAIRED_URL_STRUCTURE ] = $new_options[ Option::PAIRED_URL_STRUCTURE ];
		}
		return $options;
	}

	/**
	 * Handle options update.
	 *
	 * @param array $old_options Old options.
	 * @param array $new_options New options.
	 */
	public function handle_options_update( $old_options, $new_options ) {
		if (
			isset( $old_options[ Option::PAIRED_URL_STRUCTURE ], $new_options[ Option::PAIRED_URL_STRUCTURE ] )
			&&
			$old_options[ Option::PAIRED_URL_STRUCTURE ] !== $new_options[ Option::PAIRED_URL_STRUCTURE ]
		) {
			$this->add_rewrite_endpoint();
			$this->flush_rewrite_rules();
		}
	}

	/**
	 * Determine whether a custom paired URL structure is being used.
	 *
	 * @return bool Whether custom paired URL structure is used.
	 */
	public function has_custom_paired_url_structure() {
		$has_filters      = [
			has_filter( 'amp_has_paired_endpoint' ),
			has_filter( 'amp_add_paired_endpoint' ),
			has_filter( 'amp_remove_paired_endpoint' ),
		];
		$has_filter_count = count( array_filter( $has_filters ) );
		if ( 3 === $has_filter_count ) {
			return true;
		} elseif ( $has_filter_count > 0 ) {
			_doing_it_wrong(
				'add_filter',
				esc_html__( 'In order to implement a custom paired AMP URL structure, you must add three filters:', 'amp' ) . ' amp_has_paired_endpoint, amp_add_paired_endpoint, amp_remove_paired_endpoint',
				'2.1'
			);
		}
		return false;
	}

	/**
	 * Get the current paired AMP paired URL structure.
	 *
	 * @return string Paired AMP paired URL structure.
	 */
	public function get_paired_url_structure() {
		if ( $this->has_custom_paired_url_structure() ) {
			return self::PAIRED_URL_STRUCTURE_CUSTOM;
		}
		return AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE );
	}

	/**
	 * Get paired URLs for all available structures.
	 *
	 * @param string $url URL.
	 * @return array Paired URLs keyed by structure.
	 */
	public function get_all_structure_paired_urls( $url ) {
		$paired_urls = [];
		$structures  = self::PAIRED_URL_STRUCTURES;
		if ( $this->has_custom_paired_url_structure() ) {
			$structures[] = self::PAIRED_URL_STRUCTURE_CUSTOM;
		}
		foreach ( $structures as $structure ) {
			$paired_urls[ $structure ] = $this->add_paired_endpoint( $url, $structure );
		}
		return $paired_urls;
	}

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string      $url       URL.
	 * @param string|null $structure Structure. Defaults to the current paired structure.
	 * @return string AMP URL.
	 */
	public function add_paired_endpoint( $url, $structure = null ) {
		if ( null === $structure ) {
			$structure = self::get_paired_url_structure();
		}
		switch ( $structure ) {
			case Option::PAIRED_URL_STRUCTURE_SUFFIX_ENDPOINT:
				return $this->get_rewrite_endpoint_paired_amp_url( $url );
			case Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL:
				return $this->get_legacy_transitional_paired_amp_url( $url );
			case Option::PAIRED_URL_STRUCTURE_LEGACY_READER:
				return $this->get_legacy_reader_paired_amp_url( $url );
		}

		// This is the PAIRED_URL_STRUCTURE_QUERY_VAR case, the default.
		$amp_url = $this->get_query_var_paired_amp_url( $url );

		if ( self::PAIRED_URL_STRUCTURE_CUSTOM === $structure ) {
			/**
			 * Filters paired AMP URL to apply a custom paired URL structure.
			 *
			 * @since 2.1
			 *
			 * @param string $amp_url AMP URL. By default the AMP query var is added.
			 * @param string $url     Original URL.
			 */
			$amp_url = apply_filters( 'amp_add_paired_endpoint', $amp_url, $url );
		}

		return $amp_url;
	}

	/**
	 * Get paired AMP URL using query var (`?amp=1`).
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	public function get_query_var_paired_amp_url( $url ) {
		return add_query_arg( amp_get_slug(), '1', $url );
	}

	/**
	 * Get paired AMP URL using a rewrite endpoint.
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	public function get_rewrite_endpoint_paired_amp_url( $url ) {
		$url = $this->remove_paired_endpoint( $url );

		$parsed_url = array_merge(
			wp_parse_url( home_url( '/' ) ),
			wp_parse_url( $url )
		);

		$rewrite = $this->get_wp_rewrite();

		$query_var_required = (
			! $rewrite->using_permalinks()
			||
			isset( $parsed_url['query'] )
		);

		if ( empty( $parsed_url['scheme'] ) ) {
			$parsed_url['scheme'] = is_ssl() ? 'https' : 'http';
		}
		if ( ! isset( $parsed_url['host'] ) ) {
			$parsed_url['host'] = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : 'localhost';
		}

		if ( ! $query_var_required ) {
			$parsed_url['path']  = trailingslashit( $parsed_url['path'] );
			$parsed_url['path'] .= user_trailingslashit( amp_get_slug(), 'amp' );
		}

		$amp_url = $parsed_url['scheme'] . '://';
		if ( isset( $parsed_url['user'] ) ) {
			$amp_url .= $parsed_url['user'];
			if ( isset( $parsed_url['pass'] ) ) {
				$amp_url .= ':' . $parsed_url['pass'];
			}
			$amp_url .= '@';
		}
		$amp_url .= $parsed_url['host'];
		if ( isset( $parsed_url['port'] ) ) {
			$amp_url .= ':' . $parsed_url['port'];
		}
		$amp_url .= $parsed_url['path'];
		if ( isset( $parsed_url['query'] ) ) {
			$amp_url .= '?' . $parsed_url['query'];
		}
		if ( $query_var_required ) {
			$amp_url = $this->get_query_var_paired_amp_url( $amp_url );
		}
		if ( isset( $parsed_url['fragment'] ) ) {
			$amp_url .= '#' . $parsed_url['fragment'];
		}
		return $amp_url;
	}

	/**
	 * Get paired AMP URL using the legacy transitional scheme (`?amp`).
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	public function get_legacy_transitional_paired_amp_url( $url ) {
		return add_query_arg( amp_get_slug(), '', $url );
	}

	/**
	 * Get paired AMP URL using the legacy reader scheme (`/amp/` or else `?amp`).
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	public function get_legacy_reader_paired_amp_url( $url ) {
		$post_id = url_to_postid( $url );

		if ( $post_id ) {
			/**
			 * Filters the AMP permalink to short-circuit normal generation.
			 *
			 * Returning a string value in this filter will bypass the `get_permalink()` from being called and the `amp_get_permalink` filter will not apply.
			 *
			 * @since 0.4
			 * @since 1.0 This filter only applies when using the legacy reader paired URL structure.
			 *
			 * @param false $url     Short-circuited URL.
			 * @param int   $post_id Post ID.
			 */
			$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );

			if ( is_string( $pre_url ) ) {
				return $pre_url;
			}
		}

		// Make sure any existing AMP endpoint is removed.
		$url = $this->remove_paired_endpoint( $url );

		$parsed_url    = wp_parse_url( $url );
		$use_query_var = (
			// If pretty permalinks aren't available, then query var must be used.
			! $this->get_wp_rewrite()->using_permalinks()
			||
			// If there are existing query vars, then always use the amp query var as well.
			! empty( $parsed_url['query'] )
			||
			// If no post was found for the URL.
			! $post_id
			||
			// If the post type is hierarchical then the /amp/ endpoint isn't available.
			is_post_type_hierarchical( get_post_type( $post_id ) )
			||
			// Attachment pages don't accept the /amp/ endpoint.
			'attachment' === get_post_type( $post_id )
		);
		if ( $use_query_var ) {
			$amp_url = add_query_arg( amp_get_slug(), '', $url );
		} else {
			$amp_url = preg_replace( '/#.*/', '', $url );
			$amp_url = trailingslashit( $amp_url ) . user_trailingslashit( amp_get_slug(), 'single_amp' );
			if ( ! empty( $parsed_url['fragment'] ) ) {
				$amp_url .= '#' . $parsed_url['fragment'];
			}
		}

		if ( $post_id ) {
			/**
			 * Filters AMP permalink.
			 *
			 * @since 0.2
			 * @since 1.0 This filter only applies when using the legacy reader paired URL structure.
			 *
			 * @param string $amp_url AMP URL.
			 * @param int    $post_id Post ID.
			 */
			$amp_url = apply_filters( 'amp_get_permalink', $amp_url, $post_id );
		}

		return $amp_url;
	}

	/**
	 * Get paired URL examples.
	 *
	 * @return array[] Keys are the structures, values are arrays of paired URLs using the structure.
	 */
	private function get_paired_url_examples() {
		$supported_post_types     = AMP_Post_Type_Support::get_supported_post_types();
		$hierarchical_post_types  = array_intersect(
			$supported_post_types,
			get_post_types( [ 'hierarchical' => true ] )
		);
		$chronological_post_types = array_intersect(
			$supported_post_types,
			get_post_types( [ 'hierarchical' => false ] )
		);

		$examples = [];
		foreach ( [ $chronological_post_types, $hierarchical_post_types ] as $post_types ) {
			if ( empty( $post_types ) ) {
				continue;
			}
			$posts = get_posts(
				[
					'post_type'   => $post_types,
					'post_status' => 'publish',
				]
			);
			foreach ( $posts as $post ) {
				if ( count( AMP_Post_Type_Support::get_support_errors( $post ) ) !== 0 ) {
					continue;
				}
				$paired_urls = $this->get_all_structure_paired_urls( get_permalink( $post ) );
				foreach ( $paired_urls as $structure => $paired_url ) {
					$examples[ $structure ][] = $paired_url;
				}
				continue 2;
			}
		}
		return $examples;
	}

	/**
	 * Get sources for the current paired URL structure.
	 *
	 * @return array Sources. Each item is an array with keys for type, slug, and name.
	 * @global WP_Hook[] $wp_filter Filter registry.
	 */
	private function get_custom_paired_structure_sources() {
		global $wp_filter;
		if ( ! $this->has_custom_paired_url_structure() ) {
			return [];
		}

		$sources = [];

		$filter_names = [ 'amp_has_paired_endpoint', 'amp_add_paired_endpoint', 'amp_remove_paired_endpoint' ];
		foreach ( $filter_names as $filter_name ) {
			if ( ! isset( $wp_filter[ $filter_name ] ) ) {
				continue;
			}
			$hook = $wp_filter[ $filter_name ];
			if ( ! $hook instanceof WP_Hook ) {
				continue;
			}
			foreach ( $hook->callbacks as $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$source = $this->callback_reflection->get_source( $callback['function'] );
					if ( ! $source ) {
						continue;
					}

					$type = $source['type'];
					$slug = $source['name'];
					$name = null;

					if ( 'plugin' === $type ) {
						$plugin = $this->plugin_registry->get_plugin_from_slug( $slug );
						if ( isset( $plugin['data']['Name'] ) ) {
							$name = $plugin['data']['Name'];
						}
					} elseif ( 'theme' === $type ) {
						$theme = wp_get_theme( $slug );
						if ( ! $theme->errors() ) {
							$name = $theme->get( 'Name' );
						}
					}

					$source = compact( 'type', 'slug', 'name' );
					if ( in_array( $source, $sources, true ) ) {
						continue;
					}

					$sources[] = $source;
				}
			}
		}

		return $sources;
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL to examine. If empty, will use the current URL.
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 * @global WP_Query $wp_the_query
	 */
	public function has_paired_endpoint( $url = '' ) {
		$slug = amp_get_slug();

		// If the URL was not provided, then use the environment which is already parsed.
		if ( empty( $url ) ) {
			global $wp_the_query;
			$has_endpoint = (
				isset( $_GET[ $slug ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				||
				(
					$wp_the_query instanceof WP_Query
					&&
					false !== $wp_the_query->get( $slug, false )
				)
			);
		} else {
			$has_endpoint = false;

			$parsed_url = wp_parse_url( $url );
			if ( ! empty( $parsed_url['query'] ) ) {
				$query_vars = [];
				wp_parse_str( $parsed_url['query'], $query_vars );
				if ( isset( $query_vars[ $slug ] ) ) {
					$has_endpoint = true;
				}
			}

			if ( ! $has_endpoint && ! empty( $parsed_url['path'] ) ) {
				$pattern = sprintf(
					'#/%s(/[^/^])?/?$#',
					preg_quote( $slug, '#' )
				);
				if ( preg_match( $pattern, $parsed_url['path'] ) ) {
					$has_endpoint = true;
				}
			}
		}

		if ( $this->has_custom_paired_url_structure() ) {
			/**
			 * Filters whether the URL has a paired AMP paired URL structure.
			 *
			 * @since 2.1
			 *
			 * @param bool   $has_endpoint Had endpoint. By default true if the AMP query var or rewrite endpoint is present.
			 * @param string $url          The URL.
			 */
			$has_endpoint = apply_filters( 'amp_has_paired_endpoint', $has_endpoint, $url ?: amp_get_current_url() );
		}

		return $has_endpoint;
	}

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL.
	 * @return string URL with AMP stripped.
	 */
	public function remove_paired_endpoint( $url ) {
		$slug = amp_get_slug();

		// Strip endpoint, including /amp/, /amp/amp/, /amp/foo/.
		$non_amp_url = preg_replace(
			sprintf(
				':(/%s(/[^/?#]+)?)+(?=/?(\?|#|$)):',
				preg_quote( $slug, ':' )
			),
			'',
			$url
		);

		// Strip query var, including ?amp, ?amp=1, etc.
		$non_amp_url = remove_query_arg( $slug, $non_amp_url );

		if ( $this->has_custom_paired_url_structure() ) {
			/**
			 * Filters paired AMP URL to remove a custom paired URL structure.
			 *
			 * @since 2.1
			 *
			 * @param string $non_amp_url AMP URL. By default the rewrite endpoint and query var is removed.
			 * @param string $url         Original URL.
			 */
			$non_amp_url = apply_filters( 'amp_remove_paired_endpoint', $non_amp_url, $url );
		}

		return $non_amp_url;
	}

	/**
	 * Fix up WP_Query for front page when amp query var is present.
	 *
	 * Normally the front page would not get served if a query var is present other than preview, page, paged, and cpage.
	 *
	 * @see WP_Query::parse_query()
	 * @link https://github.com/WordPress/wordpress-develop/blob/0baa8ae85c670d338e78e408f8d6e301c6410c86/src/wp-includes/class-wp-query.php#L951-L971
	 *
	 * @param WP_Query $query Query.
	 */
	public function correct_query_when_is_front_page( WP_Query $query ) {
		$is_front_page_query = (
			$query->is_main_query()
			&&
			$query->is_home()
			&&
			// Is AMP endpoint.
			false !== $query->get( amp_get_slug(), false )
			&&
			// Is query not yet fixed uo up to be front page.
			! $query->is_front_page()
			&&
			// Is showing pages on front.
			'page' === get_option( 'show_on_front' )
			&&
			// Has page on front set.
			get_option( 'page_on_front' )
			&&
			// See line in WP_Query::parse_query() at <https://github.com/WordPress/wordpress-develop/blob/0baa8ae/src/wp-includes/class-wp-query.php#L961>.
			0 === count( array_diff( array_keys( wp_parse_args( $query->query ) ), [ amp_get_slug(), 'preview', 'page', 'paged', 'cpage' ] ) )
		);
		if ( $is_front_page_query ) {
			$query->is_home     = false;
			$query->is_page     = true;
			$query->is_singular = true;
			$query->set( 'page_id', get_option( 'page_on_front' ) );
		}
	}

	/**
	 * Add the paired endpoint to a URL if the request is for an AMP page and the Standard mode is not selected.
	 *
	 * This is used with the `redirect_canonical` and `old_slug_redirect_url` filters to prevent removal of the `/amp/`
	 * endpoint.
	 *
	 * @param string $url URL.
	 * @return string Resulting URL with AMP endpoint added if needed.
	 */
	public function maybe_add_paired_endpoint( $url ) {
		if ( $url ) {
			$url = $this->add_paired_endpoint( $url );
		}
		return $url;
	}
}
