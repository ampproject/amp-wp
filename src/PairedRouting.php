<?php
/**
 * Class PairedRouting.
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
use AmpProject\AmpWP\PairedUrlStructure\LegacyReaderUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\LegacyTransitionalUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\QueryVarUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\SuffixUrlStructure;
use WP_Query;
use WP_Rewrite;
use WP;
use WP_Hook;
use WP_Term_Query;

/**
 * Service for routing users to and from paired AMP URLs.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class PairedRouting implements Service, Registerable, Activateable, Deactivateable {

	/**
	 * Paired URL structures.
	 *
	 * Mapping of the option key to the corresponding paired URL structure class.
	 *
	 * @var string[]
	 */
	const PAIRED_URL_STRUCTURES = [
		Option::PAIRED_URL_STRUCTURE_QUERY_VAR           => QueryVarUrlStructure::class,
		Option::PAIRED_URL_STRUCTURE_SUFFIX_ENDPOINT     => SuffixUrlStructure::class,
		Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL => LegacyTransitionalUrlStructure::class,
		Option::PAIRED_URL_STRUCTURE_LEGACY_READER       => LegacyReaderUrlStructure::class,
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
	 * REST API field name for entities already using the AMP slug as name.
	 *
	 * @see amp_get_slug()
	 * @var string
	 */
	const ENDPOINT_SUFFIX_CONFLICTS = 'endpoint_suffix_conflicts';

	/**
	 * REST API field name for whether permalinks are being used.
	 *
	 * @see WP_Rewrite::using_permalinks()
	 * @var string
	 */
	const ENDPOINT_USING_PERMALINKS = 'using_permalinks';

	/**
	 * Key for the custom paired structure sources.
	 *
	 * @var string
	 */
	const CUSTOM_PAIRED_ENDPOINT_SOURCES = 'custom_paired_endpoint_sources';

	/**
	 * Paired URL structure.
	 *
	 * @var PairedUrlStructure
	 */
	private $paired_url_structure;

	/**
	 * Callback reflection.
	 *
	 * @var CallbackReflection
	 */
	private $callback_reflection;

	/**
	 * Plugin registry.
	 *
	 * @var PluginRegistry
	 */
	private $plugin_registry;

	/**
	 * Whether the request had the /amp/ endpoint suffix.
	 *
	 * @var bool
	 */
	private $did_request_endpoint;

	/**
	 * Original environment variables that were rewritten before parsing the request.
	 *
	 * @see PairedRouting::detect_endpoint_before_request_parsing()
	 * @see PairedRouting::restore_endpoint_in_environment_variables()
	 * @var array
	 */
	private $suspended_environment_variables = [];

	/**
	 * PairedRouting constructor.
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

		$this->remove_rewrite_endpoint();
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

		add_action( 'init', [ $this, 'update_rewrite_endpoint' ], 0 );

		add_filter( 'template_redirect', [ $this, 'redirect_extraneous_paired_endpoint' ], 8 ); // Must be before redirect_paired_amp_unavailable() runs at priority 9.

		if ( ! amp_is_canonical() ) {
			$this->add_paired_hooks();
		}
	}

	/**
	 * Get the paired URL structure.
	 *
	 * @return PairedUrlStructure Paired URL structure.
	 */
	public function get_paired_url_structure() {
		if ( ! $this->paired_url_structure instanceof PairedUrlStructure ) {
			/**
			 * Filters to allow a custom paired URL structure to be used.
			 *
			 * @param string $structure_class Paired URL structure class.
			 */
			$structure_class = apply_filters( 'amp_custom_paired_url_structure', null );

			if ( ! $structure_class || ! is_subclass_of( $structure_class, PairedUrlStructure::class ) ) {
				$structure_slug = AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE );
				if ( array_key_exists( $structure_slug, static::PAIRED_URL_STRUCTURES ) ) {
					$structure_class = static::PAIRED_URL_STRUCTURES[ $structure_slug ];
				} else {
					$structure_class = QueryVarUrlStructure::class;
				}
			}

			// @todo PhpStan error: Instantiated class AmpProject\AmpWP\PairedUrlStructure is abstract.
			$this->paired_url_structure = new $structure_class();
		}
		return $this->paired_url_structure;
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
				Option::PAIRED_URL_STRUCTURE    => [
					'type' => 'string',
					'enum' => array_keys( self::PAIRED_URL_STRUCTURES ),
				],
				self::PAIRED_URL_EXAMPLES       => [
					'type'     => 'object',
					'readonly' => true,
				],
				self::AMP_SLUG                  => [
					'type'     => 'string',
					'readonly' => true,
				],
				self::ENDPOINT_SUFFIX_CONFLICTS => [
					'type'     => 'array',
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

		if ( $this->has_custom_paired_url_structure() ) {
			$options[ Option::PAIRED_URL_STRUCTURE ] = self::PAIRED_URL_STRUCTURE_CUSTOM;
		} else {
			$options[ Option::PAIRED_URL_STRUCTURE ] = AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE );
		}

		$options[ self::PAIRED_URL_EXAMPLES ] = $this->get_paired_url_examples();

		$options[ self::CUSTOM_PAIRED_ENDPOINT_SOURCES ] = $this->get_custom_paired_structure_sources();

		$options[ self::ENDPOINT_SUFFIX_CONFLICTS ] = $this->get_endpoint_suffix_conflicts();

		$options[ self::ENDPOINT_USING_PERMALINKS ] = $this->get_wp_rewrite()->using_permalinks();

		return $options;
	}

	/**
	 * Add paired hooks.
	 */
	public function add_paired_hooks() {
		add_filter( 'query_vars', [ $this, 'filter_query_vars' ] );

		add_action( 'wp_loaded', [ $this, 'add_request_parsing_hooks' ] );

		if ( Option::PAIRED_URL_STRUCTURE_SUFFIX_ENDPOINT === AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE ) ) {

			// Note that the wp_unique_term_slug filter does not work in the same way. It will only be applied if there
			// is actually a duplicate, whereas the wp_unique_post_slug filter applies regardless.
			add_filter( 'wp_unique_post_slug', [ $this, 'filter_unique_post_slug' ], 10, 4 );
		}

		add_filter( 'template_redirect', [ $this, 'redirect_paired_amp_unavailable' ], 9 ); // Must be before redirect_canonical() runs at priority 10.
		add_action( 'parse_query', [ $this, 'correct_query_when_is_front_page' ] );
		add_action( 'wp', [ $this, 'add_paired_request_hooks' ] );

		add_action( 'admin_notices', [ $this, 'add_permalink_settings_notice' ] );
	}

	/**
	 * Added hooks needed for some paired structures.
	 */
	public function add_request_parsing_hooks() {
		$paired_structure = $this->get_paired_url_structure();
		if ( ! $paired_structure->needs_request_parsing() ) {
			return;
		}

		add_filter( 'do_parse_request', [ $this, 'detect_endpoint_before_request_parsing' ], PHP_INT_MAX );
		add_filter( 'request', [ $this, 'set_query_var_during_request_parsing' ] );
		add_action( 'parse_request', [ $this, 'restore_endpoint_in_environment_variables' ] );
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
	 * Get the entities that are already using the AMP slug.
	 *
	 * @return array Conflict data.
	 */
	public function get_endpoint_suffix_conflicts() {
		$conflicts = [];
		$amp_slug  = amp_get_slug();

		$post_query = new WP_Query(
			[
				'post_type'      => 'any',
				'name'           => $amp_slug,
				'fields'         => 'ids',
				'posts_per_page' => 100,
			]
		);
		if ( $post_query->post_count > 0 ) {
			$conflicts['posts'] = $post_query->posts;
		}

		$term_query = new WP_Term_Query(
			[
				'slug'   => $amp_slug,
				'fields' => 'ids',
			]
		);
		if ( $term_query->terms ) {
			$conflicts['terms'] = $term_query->terms;
		}

		$user = get_user_by( 'slug', $amp_slug );
		if ( $user ) {
			$conflicts['users'] = [ $user->ID ];
		}

		foreach ( get_post_types( [], 'objects' ) as $post_type ) {
			if ( isset( $post_type->rewrite['slug'] ) && $post_type->rewrite['slug'] === $amp_slug ) {
				$conflicts['post_types'][] = $post_type->name;
			}
		}

		foreach ( get_taxonomies( [], 'objects' ) as $taxonomy ) {
			if ( isset( $taxonomy->rewrite['slug'] ) && $taxonomy->rewrite['slug'] === $amp_slug ) {
				$conflicts['taxonomies'][] = $taxonomy->name;
			}
		}

		return $conflicts;
	}

	/**
	 * Detect the AMP rewrite endpoint from the PATH_INFO or REQUEST_URI and purge from those environment variables.
	 *
	 * @see WP::parse_request()
	 *
	 * @param bool $should_parse_request Whether or not to parse the request. Default true.
	 * @return bool Should parse request.
	 */
	public function detect_endpoint_before_request_parsing( $should_parse_request ) {
		$this->did_request_endpoint = false;

		if ( ! $should_parse_request ) {
			return false;
		}

		// Detect and purge the AMP endpoint from the request.
		foreach ( [ 'REQUEST_URI', 'PATH_INFO' ] as $var_name ) {
			if ( empty( $_SERVER[ $var_name ] ) ) {
				continue;
			}

			$this->suspended_environment_variables[ $var_name ] = $_SERVER[ $var_name ];

			$old_path = wp_unslash( $_SERVER[ $var_name ] ); // Because of wp_magic_quotes().

			$new_path = $this->get_paired_url_structure()->remove_endpoint( $old_path );

			if ( $old_path === $new_path ) {
				continue;
			}

			$_SERVER[ $var_name ] = wp_slash( $new_path ); // Because of wp_magic_quotes().

			$this->did_request_endpoint = true;
		}

		return $should_parse_request;
	}

	/**
	 * Set query var for endpoint suffix.
	 *
	 * @param array $query_vars Query vars.
	 * @return array Query vars.
	 */
	public function set_query_var_during_request_parsing( $query_vars ) {
		if ( $this->did_request_endpoint ) {
			$query_vars[ amp_get_slug() ] = true;
		}
		return $query_vars;
	}

	/**
	 * Restore the endpoint suffix on environment variables.
	 *
	 * @see PairedRouting::detect_endpoint_before_request_parsing()
	 *
	 * @param WP $wp WP object.
	 */
	public function restore_endpoint_in_environment_variables( WP $wp ) {
		if ( $this->did_request_endpoint ) {
			foreach ( $this->suspended_environment_variables as $var_name => $value ) {
				$_SERVER[ $var_name ] = $value;
			}
			$this->suspended_environment_variables = [];

			// In case a plugin is looking at $wp->request to see if it is AMP, ensure the path suffix is added.
			// WordPress is not including it because it was removed from the REQUEST_URI during parse_request.
			$wp->request = trim( $this->add_endpoint( '/' . ltrim( $wp->request, '/' ) ), '/' );
		}
	}

	/**
	 * Filters the post slug to prevent conflicting with the 'amp' slug.
	 *
	 * @see wp_unique_post_slug()
	 *
	 * @param string $slug        Slug.
	 * @param int    $post_id     Post ID.
	 * @param string $post_status The post status.
	 * @param string $post_type   Post type.
	 * @return string Slug.
	 * @global \wpdb $wpdb WP DB.
	 */
	public function filter_unique_post_slug( $slug, $post_id, /** @noinspection PhpUnusedParameterInspection */ $post_status, $post_type ) {
		global $wpdb;

		$amp_slug = amp_get_slug();
		if ( $amp_slug !== $slug ) {
			return $slug;
		}

		$suffix = 2;
		do {
			$alt_slug   = "$slug-$suffix";
			$slug_check = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d LIMIT 1",
					$alt_slug,
					$post_type,
					$post_id
				)
			);
			$suffix++;
		} while ( $slug_check );
		$slug = $alt_slug;

		return $slug;
	}

	/**
	 * Add hooks based for AMP pages and other hooks for non-AMP pages.
	 */
	public function add_paired_request_hooks() {
		if ( amp_is_request() ) {
			add_filter( 'old_slug_redirect_url', [ $this, 'maybe_add_paired_endpoint' ], 1000 );
			add_filter( 'redirect_canonical', [ $this, 'maybe_add_paired_endpoint' ], 1000 );
		} else {
			add_action( 'wp_head', 'amp_add_amphtml_link' );
		}
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
	 * Remove rewrite endpoint.
	 */
	private function remove_rewrite_endpoint() {
		$rewrite = $this->get_wp_rewrite();
		foreach ( $rewrite->endpoints as $index => $endpoint ) {
			if ( amp_get_slug() === $endpoint[1] ) {
				unset( $rewrite->endpoints[ $index ] );
				break;
			}
		}
	}

	/**
	 * Update rewrite endpoint.
	 */
	public function update_rewrite_endpoint() {
		if (
			! amp_is_canonical()
			&&
			Option::PAIRED_URL_STRUCTURE_LEGACY_READER === AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE )
		) {
			$this->get_wp_rewrite()->add_endpoint( amp_get_slug(), EP_PERMALINK );
		} elseif ( ! $this->has_custom_paired_url_structure() ) {
			// This is not done for a custom structure which may involve registering rewrite endpoints.
			$this->remove_rewrite_endpoint();
		}
	}

	/**
	 * Filter query vars to add AMP.
	 *
	 * This is necessary when the rewrite endpoint is not added, since it is only added when using the legacy reader paired URL structure.
	 *
	 * @param string[] $query_vars Query vars.
	 * @return string[] Amended query vars.
	 */
	public function filter_query_vars( $query_vars ) {
		$slug = amp_get_slug();
		if ( ! in_array( $slug, $query_vars, true ) ) {
			$query_vars[] = $slug;
		}
		return $query_vars;
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
				&&
				$this->get_wp_rewrite()->using_permalinks()
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
			in_array( $new_options[ Option::PAIRED_URL_STRUCTURE ], array_keys( self::PAIRED_URL_STRUCTURES ), true )
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
			(
				isset( $old_options[ Option::THEME_SUPPORT ], $new_options[ Option::THEME_SUPPORT ] )
				&&
				$old_options[ Option::THEME_SUPPORT ] !== $new_options[ Option::THEME_SUPPORT ]
			)
			||
			(
				isset( $old_options[ Option::PAIRED_URL_STRUCTURE ], $new_options[ Option::PAIRED_URL_STRUCTURE ] )
				&&
				$old_options[ Option::PAIRED_URL_STRUCTURE ] !== $new_options[ Option::PAIRED_URL_STRUCTURE ]
			)
		) {
			$this->update_rewrite_endpoint();
			$this->flush_rewrite_rules();
		}
	}

	/**
	 * Determine whether a custom paired URL structure is being used.
	 *
	 * @return bool Whether custom paired URL structure is used.
	 */
	public function has_custom_paired_url_structure() {
		return has_filter( 'amp_custom_paired_url_structure' );
	}

	/**
	 * Get paired URLs for all available structures.
	 *
	 * @param string $url URL.
	 * @return array Paired URLs keyed by structure.
	 */
	public function get_all_structure_paired_urls( $url ) {
		$paired_urls = [];
		foreach ( self::PAIRED_URL_STRUCTURES as $structure_slug => $structure_class ) {
			/** @var PairedUrlStructure $structure */
			$structure = new $structure_class();

			$paired_urls[ $structure_slug ] = $structure->add_endpoint( $url );
		}

		if ( $this->has_custom_paired_url_structure() ) {
			$paired_urls[ self::PAIRED_URL_STRUCTURE_CUSTOM ] = $this->add_endpoint( $url );
		}

		return $paired_urls;
	}

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	public function add_endpoint( $url ) {
		return $this->get_paired_url_structure()->add_endpoint( $url );
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
	 * Get sources for the custom paired URL structure (if any).
	 *
	 * @return array Sources. Each item is an array with keys for type, slug, and name.
	 * @global WP_Hook[] $wp_filter Filter registry.
	 */
	private function get_custom_paired_structure_sources() {
		global $wp_filter;
		if ( ! $this->has_custom_paired_url_structure() ) {
			return [];
		}

		if ( ! isset( $wp_filter['amp_custom_paired_url_structure'] ) ) {
			return [];
		}
		$hook = $wp_filter['amp_custom_paired_url_structure'];
		if ( ! $hook instanceof WP_Hook ) {
			return [];
		}

		$sources = [];
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

		return $sources;
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * If no URL was is provided, then it will check whether WordPress has already parsed the AMP
	 * query var as part of the request. If still not present, then it will use get the current URL
	 * and check if it has an endpoint.
	 *
	 * @param string $url URL to examine. If empty, will use the current URL.
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 * @global WP_Query $wp_the_query
	 */
	public function has_endpoint( $url = '' ) {
		if ( empty( $url ) ) {
			global $wp_the_query;
			if (
				(
					$wp_the_query instanceof WP_Query
					&&
					false !== $wp_the_query->get( amp_get_slug(), false )
				)
			) {
				return true;
			}

			$url = amp_get_current_url();
		}
		return $this->get_paired_url_structure()->has_endpoint( $url );
	}

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL.
	 * @return string URL with AMP stripped.
	 */
	public function remove_endpoint( $url ) {
		return $this->get_paired_url_structure()->remove_endpoint( $url );
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
	 * Add the paired endpoint to a URL.
	 *
	 * This is used with the `redirect_canonical` and `old_slug_redirect_url` filters to prevent removal of the `/amp/`
	 * endpoint.
	 *
	 * @param string|false $url URL. This may be false if another filter is attempting to stop redirection.
	 * @return string Resulting URL with AMP endpoint added if needed.
	 */
	public function maybe_add_paired_endpoint( $url ) {
		if ( $url ) {
			$url = $this->add_endpoint( $url );
		}
		return $url;
	}

	/**
	 * Redirect to remove the extraneous paired endpoint from the requested URI.
	 *
	 * When in Standard mode, the behavior is to strip off /amp/ if it is present on the requested URL when it is a 404.
	 * This ensures that sites switching to AMP-first will have their /amp/ URLs redirecting to the non-AMP, rather than
	 * attempting to redirect to some post that has 'amp' beginning their post slug. Otherwise, in Standard mode a
	 * redirect happens to remove the 'amp' query var if present.
	 *
	 * When in a Paired AMP mode, this handles a case where an AMP page that has a link to `./amp/` can inadvertently
	 * cause an infinite URL space such as `./amp/amp/amp/amp/…`.
	 *
	 * This happens before `PairedRouting::redirect_paired_amp_unavailable()`.
	 *
	 * @see PairedRouting::redirect_paired_amp_unavailable()
	 */
	public function redirect_extraneous_paired_endpoint() {
		$requested_url = amp_get_current_url();
		$redirect_url  = null;

		$endpoint_suffix_removed = PairedUrlStructure::remove_path_suffix( $requested_url );
		$query_var_removed       = PairedUrlStructure::remove_query_var( $requested_url );
		if ( amp_is_canonical() ) {
			if ( is_404() && $endpoint_suffix_removed !== $requested_url ) {
				// Always redirect to strip off /amp/ in the case of a 404.
				$redirect_url = $endpoint_suffix_removed;
			} elseif ( $query_var_removed !== $requested_url ) {
				// Strip extraneous query var from AMP-first sites.
				$redirect_url = $query_var_removed;
			}
		} elseif ( $endpoint_suffix_removed !== $requested_url ) {
			if ( is_404() ) {
				// To account for switching the paired URL structure from `/amp/` to `?amp=1`, add the query var if in Paired
				// AMP mode. Note this is not necessary to do when sites have switched from a query var to an endpoint suffix
				// because the query var will always be recognized whereas the reverse is not always true.
				$redirect_url = PairedUrlStructure::add_query_var( $endpoint_suffix_removed );
			} elseif ( Option::PAIRED_URL_STRUCTURE_LEGACY_READER === AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE ) ) {
				global $wp;
				$path_args = [];
				wp_parse_str( $wp->matched_query, $path_args );

				// The URL has an /amp/ rewrite endpoint.
				if ( isset( $path_args[ amp_get_slug() ] ) ) {
					if ( '' !== $path_args[ amp_get_slug() ] ) {
						// In the one case where a WordPress rewrite endpoint is added, prevent infinite URL space under /amp/ endpoint.
						// Note that WordPress allows endpoints to have a value, such as the case of /feed/ where /feed/atom/ is the
						// same as saying ?feed=atom. In this case, we need to check for /amp/x/ to protect against links like
						// `<a href="./amp/">AMP!</a>`. See https://github.com/ampproject/amp-wp/pull/1846.
						// In the case where the paired URL structure is "suffix endpoint" (where rewrite rules are not used), then
						// then this is handled by another condition.
						$redirect_url = $endpoint_suffix_removed;
					} elseif ( $query_var_removed !== $requested_url ) {
						// Redirect `/amp/?amp=1` to `/amp/`.
						$redirect_url = $query_var_removed;
					}
				}
			} elseif ( $this->did_request_endpoint && $query_var_removed !== $requested_url ) {
				// Redirect /amp/?amp=1 to /amp/, removing redundant endpoints.
				$redirect_url = $query_var_removed;
			}
		}

		if ( $redirect_url ) {
			$this->redirect_location( $redirect_url );
		}
	}

	/**
	 * Redirect to non-AMP URL if paired AMP is not available for this URL and yet the query var is present.
	 *
	 * AMP may not be available either because either it is disabled for the template type or the site has been put into
	 * Standard mode. In the latter case, when AMP-first/canonical then when there is an ?amp query param, then a
	 * redirect needs to be done to the URL without any AMP indicator in the URL. Note that URLs with an endpoint suffix
	 * like /amp/ will redirect to strip the endpoint on Standard mode sites via the `redirect_extraneous_paired_endpoint`
	 * method above.
	 *
	 * This happens after `PairedRouting::redirect_extraneous_paired_endpoint()`.
	 *
	 * @see PairedRouting::redirect_extraneous_paired_endpoint()
	 */
	public function redirect_paired_amp_unavailable() {
		if ( $this->has_endpoint() && ( amp_is_canonical() || ! amp_is_available() ) ) {
			$request_url  = amp_get_current_url();
			$redirect_url = $this->remove_endpoint( $request_url );
			if ( $redirect_url !== $request_url ) {
				$this->redirect_location( $redirect_url );
			}
		}
	}

	/**
	 * Redirect the location to a given URL.
	 *
	 * Temporary redirect is used for admin users because implied transitional mode and template support can be
	 * enabled by user ay any time, so they will be able to make AMP available for this URL and see the change
	 * without wrestling with the redirect cache.
	 *
	 * @param string $url URL.
	 */
	private function redirect_location( $url ) {
		$status_code = current_user_can( 'manage_options' ) ? 302 : 301;
		if ( wp_safe_redirect( $url, $status_code ) ) {
			// @codeCoverageIgnoreStart
			exit;
			// @codeCoverageIgnoreEnd
		}
	}
}
