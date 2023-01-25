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
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\PairedUrlStructure\LegacyReaderUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\LegacyTransitionalUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\QueryVarUrlStructure;
use AmpProject\AmpWP\PairedUrlStructure\PathSuffixUrlStructure;
use WP_Query;
use WP_Rewrite;
use WP;
use WP_Hook;
use WP_Post;
use WP_Term;
use WP_Term_Query;
use WP_User;

/**
 * Service for routing users to and from paired AMP URLs.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class PairedRouting implements Service, Registerable {

	/**
	 * Paired URL structures.
	 *
	 * Mapping of the option key to the corresponding paired URL structure class.
	 *
	 * @var string[]
	 */
	const PAIRED_URL_STRUCTURES = [
		Option::PAIRED_URL_STRUCTURE_QUERY_VAR           => QueryVarUrlStructure::class,
		Option::PAIRED_URL_STRUCTURE_PATH_SUFFIX         => PathSuffixUrlStructure::class,
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
	const ENDPOINT_PATH_SLUG_CONFLICTS = 'endpoint_path_slug_conflicts';

	/**
	 * REST API field name for whether permalinks are being used in rewrite rules.
	 *
	 * @see WP_Rewrite::using_permalinks()
	 * @var string
	 */
	const REWRITE_USING_PERMALINKS = 'rewrite_using_permalinks';

	/**
	 * Key for the custom paired structure sources.
	 *
	 * @var string
	 */
	const CUSTOM_PAIRED_ENDPOINT_SOURCES = 'custom_paired_endpoint_sources';

	/**
	 * Action which is triggered when the late-defined slug needs to be updated in options.
	 *
	 * @var string
	 */
	const ACTION_UPDATE_LATE_DEFINED_SLUG_OPTION = 'amp_update_late_defined_slug_option';

	/**
	 * Paired URL service.
	 *
	 * @var PairedUrl
	 */
	private $paired_url;

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
	 * AMP slug customization watcher.
	 *
	 * @var AmpSlugCustomizationWatcher
	 */
	private $amp_slug_customization_watcher;

	/**
	 * Plugin registry.
	 *
	 * @var PluginRegistry
	 */
	private $plugin_registry;

	/**
	 * Injector.
	 *
	 * @var Injector
	 */
	private $injector;

	/**
	 * Whether the request had the /amp/ endpoint suffix.
	 *
	 * @var bool
	 */
	private $did_request_endpoint;

	/**
	 * Current nesting level for the request.
	 *
	 * This is used to capture cases where `WP::parse_request()` is called from inside of a `request`
	 * filter, a case of a nested/recursive request. It is similar in concept to `ob_get_level()` for
	 * output buffering.
	 *
	 * @var int
	 */
	private $current_request_nesting_level = 0;

	/**
	 * Original environment variables that were rewritten before parsing the request.
	 *
	 * @see PairedRouting::detect_endpoint_in_environment()
	 * @see PairedRouting::restore_path_endpoint_in_environment()
	 * @var array
	 */
	private $suspended_environment_variables = [];

	/**
	 * PairedRouting constructor.
	 *
	 * @param Injector                    $injector                       Injector.
	 * @param CallbackReflection          $callback_reflection            Callback reflection.
	 * @param PluginRegistry              $plugin_registry                Plugin registry.
	 * @param PairedUrl                   $paired_url                     Paired URL service.
	 * @param AmpSlugCustomizationWatcher $amp_slug_customization_watcher AMP slug customization watcher.
	 */
	public function __construct( Injector $injector, CallbackReflection $callback_reflection, PluginRegistry $plugin_registry, PairedUrl $paired_url, AmpSlugCustomizationWatcher $amp_slug_customization_watcher ) {
		$this->injector                       = $injector;
		$this->callback_reflection            = $callback_reflection;
		$this->plugin_registry                = $plugin_registry;
		$this->paired_url                     = $paired_url;
		$this->amp_slug_customization_watcher = $amp_slug_customization_watcher;
	}

	/**
	 * Register.
	 */
	public function register() {
		add_filter( 'amp_rest_options_schema', [ $this, 'filter_rest_options_schema' ] );
		add_filter( 'amp_rest_options', [ $this, 'filter_rest_options' ] );

		add_filter( 'amp_default_options', [ $this, 'filter_default_options' ], 10, 2 );
		add_filter( 'amp_options_updating', [ $this, 'sanitize_options' ], 10, 2 );

		add_action( self::ACTION_UPDATE_LATE_DEFINED_SLUG_OPTION, [ $this, 'update_late_defined_slug_option' ] );
		add_action( AmpSlugCustomizationWatcher::LATE_DETERMINATION_ACTION, [ $this, 'check_stale_late_defined_slug_option' ] );

		add_action( 'template_redirect', [ $this, 'redirect_extraneous_paired_endpoint' ], 9 );

		// Priority 7 needed to run before PluginSuppression::initialize() at priority 8.
		add_action( 'plugins_loaded', [ $this, 'initialize_paired_request' ], 7 );
	}

	/**
	 * Get the late defined slug, or null if it was not defined late.
	 *
	 * @return string|null Slug or null.
	 */
	public function get_late_defined_slug() {
		return $this->amp_slug_customization_watcher->did_customize_late() ? amp_get_slug() : null;
	}

	/**
	 * Update late-defined slug option.
	 */
	public function update_late_defined_slug_option() {
		AMP_Options_Manager::update_option( Option::LATE_DEFINED_SLUG, $this->get_late_defined_slug() );
	}

	/**
	 * Check whether the late-defined slug option is stale and a single event needs to be scheduled to update it.
	 */
	public function check_stale_late_defined_slug_option() {
		$late_defined_slug = $this->get_late_defined_slug();
		if ( AMP_Options_Manager::get_option( Option::LATE_DEFINED_SLUG ) !== $late_defined_slug ) {
			wp_schedule_single_event( time(), self::ACTION_UPDATE_LATE_DEFINED_SLUG_OPTION );
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
				if ( array_key_exists( $structure_slug, self::PAIRED_URL_STRUCTURES ) ) {
					$structure_class = self::PAIRED_URL_STRUCTURES[ $structure_slug ];
				} else {
					$structure_class = QueryVarUrlStructure::class;
				}
			}

			$this->paired_url_structure = $this->injector->make( $structure_class );
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
				Option::PAIRED_URL_STRUCTURE       => [
					'type' => 'string',
					'enum' => array_keys( self::PAIRED_URL_STRUCTURES ),
				],
				self::PAIRED_URL_EXAMPLES          => [
					'type'     => 'object',
					'readonly' => true,
				],
				self::AMP_SLUG                     => [
					'type'     => 'string',
					'readonly' => true,
				],
				self::ENDPOINT_PATH_SLUG_CONFLICTS => [
					'type'     => 'object',
					'readonly' => true,
				],
				self::REWRITE_USING_PERMALINKS     => [
					'type'     => 'boolean',
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

			// Handle edge case where an unrecognized paired URL structure was saved.
			if ( ! in_array( $options[ Option::PAIRED_URL_STRUCTURE ], array_keys( self::PAIRED_URL_STRUCTURES ), true ) ) {
				$defaults = $this->filter_default_options( [], $options );

				$options[ Option::PAIRED_URL_STRUCTURE ] = $defaults[ Option::PAIRED_URL_STRUCTURE ];
			}
		}

		$options[ self::PAIRED_URL_EXAMPLES ] = $this->get_paired_url_examples();

		$options[ self::CUSTOM_PAIRED_ENDPOINT_SOURCES ] = $this->get_custom_paired_structure_sources();

		$options[ self::ENDPOINT_PATH_SLUG_CONFLICTS ] = $this->get_endpoint_path_slug_conflicts();

		$options[ self::REWRITE_USING_PERMALINKS ] = $this->is_using_permalinks();

		return $options;
	}

	/**
	 * Get the entities that are already using the AMP slug.
	 *
	 * @return array|null Conflict data or null if there are no conflicts.
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function get_endpoint_path_slug_conflicts() {
		global $wp_rewrite;

		$conflicts = [];
		$amp_slug  = amp_get_slug();

		$post_query = new WP_Query(
			[
				'post_type'      => 'any',
				'name'           => $amp_slug,
				'posts_per_page' => 100,
			]
		);
		if ( $post_query->post_count > 0 ) {
			$conflicts['posts'] = array_map(
				static function ( WP_Post $post ) {
					$post_type = get_post_type_object( $post->post_type );
					return [
						'id'        => $post->ID,
						'edit_link' => get_edit_post_link( $post->ID, 'raw' ),
						'title'     => $post->post_title,
						'post_type' => $post->post_type,
						'label'     => isset( $post_type->labels->singular_name )
									? $post_type->labels->singular_name
									: null,
					];
				},
				$post_query->posts
			);
		}

		$term_query = new WP_Term_Query(
			[
				'slug'       => $amp_slug,
				'hide_empty' => false,
			]
		);
		if ( $term_query->terms ) {
			$conflicts['terms'] = array_map(
				static function ( WP_Term $term ) {
					$taxonomy = get_taxonomy( $term->taxonomy );
					return [
						'id'        => $term->term_id,
						'edit_link' => get_edit_term_link( $term->term_id, $term->taxonomy ),
						'taxonomy'  => $term->taxonomy,
						'name'      => $term->name,
						'label'     => isset( $taxonomy->labels->singular_name )
									? $taxonomy->labels->singular_name
									: null,
					];
				},
				$term_query->terms
			);
		}

		$user = get_user_by( 'slug', $amp_slug );
		if ( $user instanceof WP_User ) {
			$conflicts['user'] = [
				'id'        => $user->ID,
				'edit_link' => get_edit_user_link( $user->ID ),
				'name'      => $user->display_name,
			];
		}

		foreach ( get_post_types( [], 'objects' ) as $post_type ) {
			if (
				$amp_slug === $post_type->query_var
				||
				( isset( $post_type->rewrite['slug'] ) && $post_type->rewrite['slug'] === $amp_slug )
			) {
				$conflicts['post_type'] = [
					'name'  => $post_type->name,
					'label' => isset( $post_type->labels->name )
							? $post_type->labels->name
							: null,
				];
				break;
			}
		}

		foreach ( get_taxonomies( [], 'objects' ) as $taxonomy ) {
			if (
				$amp_slug === $taxonomy->query_var
				||
				( isset( $taxonomy->rewrite['slug'] ) && $taxonomy->rewrite['slug'] === $amp_slug )
			) {
				$conflicts['taxonomy'] = [
					'name'  => $taxonomy->name,
					'label' => isset( $taxonomy->labels->name )
							? $taxonomy->labels->name
							: null,

				];
				break;
			}
		}

		foreach ( $wp_rewrite->endpoints as $endpoint ) {
			if ( isset( $endpoint[1] ) && $amp_slug === $endpoint[1] ) {
				$conflicts['rewrite'][] = 'endpoint';
				break;
			}
		}
		foreach (
			[
				'author_base',
				'comments_base',
				'search_base',
				'pagination_base',
				'feed_base',
				'comments_pagination_base',
			]
			as
			$key
		) {
			if ( isset( $wp_rewrite->$key ) && $amp_slug === $wp_rewrite->$key ) {
				$conflicts['rewrite'][] = $key;
			}
		}

		if ( empty( $conflicts ) ) {
			return null;
		}

		return $conflicts;
	}

	/**
	 * Add paired hooks.
	 */
	public function initialize_paired_request() {
		if ( amp_is_canonical() ) {
			return;
		}

		// Run necessary logic to properly route a request using the registered paired URL structures.
		$this->detect_endpoint_in_environment();
		add_filter( 'do_parse_request', [ $this, 'extract_endpoint_from_environment_before_parse_request' ], PHP_INT_MAX );
		add_filter( 'request', [ $this, 'filter_request_after_endpoint_extraction' ] );
		add_action( 'parse_request', [ $this, 'restore_path_endpoint_in_environment' ], defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX ); // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound

		// Reserve the 'amp' slug for paired URL structures that use paths.
		if ( $this->is_using_path_suffix() ) {
			// Note that the wp_unique_term_slug filter does not work in the same way. It will only be applied if there
			// is actually a duplicate, whereas the wp_unique_post_slug filter applies regardless.
			add_filter( 'wp_unique_post_slug', [ $this, 'filter_unique_post_slug' ], 10, 4 );
		}

		add_action( 'parse_query', [ $this, 'correct_query_when_is_front_page' ] );
		add_action( 'wp', [ $this, 'add_paired_request_hooks' ] );
		add_action( 'admin_notices', [ $this, 'add_permalink_settings_notice' ] );
	}

	/**
	 * Determine whether the paired URL structure is using a path suffix (including the legacy Reader structure).
	 *
	 * @return bool
	 */
	public function is_using_path_suffix() {
		return in_array(
			AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE ),
			[
				Option::PAIRED_URL_STRUCTURE_PATH_SUFFIX,
				Option::PAIRED_URL_STRUCTURE_LEGACY_READER,
			],
			true
		);
	}

	/**
	 * Detect the paired endpoint from the PATH_INFO or REQUEST_URI.
	 *
	 * This is necessary to avoid needing to rely on WordPress's rewrite rules to identify AMP requests.
	 * Rewrite rules are not suitable because rewrite endpoints can't be used across all URLs,
	 * and the request is parsed too late in order to switch to the Reader theme.
	 *
	 * The environment variables containing the endpoint are scrubbed of it during `WP::parse_request()`
	 * by means of the `PairedRouting::extract_endpoint_from_environment_before_parse_request()` method which runs
	 * at the `do_parse_request` filter.
	 *
	 * @see PairedRouting::extract_endpoint_from_environment_before_parse_request()
	 */
	public function detect_endpoint_in_environment() {
		$this->did_request_endpoint = false;

		// Detect and purge the AMP endpoint from the request.
		foreach ( [ 'REQUEST_URI', 'PATH_INFO' ] as $var_name ) {
			if ( empty( $_SERVER[ $var_name ] ) ) {
				continue;
			}

			$paired_url_structure = $this->get_paired_url_structure();

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$old_path = wp_unslash( $_SERVER[ $var_name ] ); // Because of wp_magic_quotes().
			if ( ! $paired_url_structure->has_endpoint( $old_path ) ) {
				continue;
			}

			$new_path = $paired_url_structure->remove_endpoint( $old_path );

			$this->suspended_environment_variables[ $var_name ] = [ $old_path, $new_path ];

			$this->did_request_endpoint = true;
		}
	}

	/**
	 * Override environment before parsing the request.
	 *
	 * This happens at the beginning of `WP::parse_request()` and then it is reset when it finishes
	 * via the `PairedRouting::restore_path_endpoint_in_environment()` method at the `parse_request`
	 * action.
	 *
	 * @see WP::parse_request()
	 *
	 * @param bool $do_parse_request Whether or not to parse the request.
	 * @return bool Passed-through argument.
	 */
	public function extract_endpoint_from_environment_before_parse_request( $do_parse_request ) {
		// If request parsing was aborted, then there's nothing for us to do.
		if ( ! $do_parse_request ) {
			return $do_parse_request;
		}

		// Only do something if doing the outermost request. Note that this function runs with the
		// latest priority in order to make sure that any other `do_parse_request` filter will have
		// already applied, and thus we know that the request is indeed going to be parsed.
		if (
			$this->did_request_endpoint
			&&
			0 === $this->current_request_nesting_level
		) {
			foreach ( $this->suspended_environment_variables as $var_name => list( , $new_path ) ) {
				$_SERVER[ $var_name ] = wp_slash( $new_path ); // Because of wp_magic_quotes().
			}
		}

		// Increase the nesting level so we can prevent calling ourselves for any recursive calls
		// to `WP::parse_request()` during the `request` filter.
		$this->current_request_nesting_level++;

		return $do_parse_request;
	}

	/**
	 * Filter the request to add the AMP query var if endpoint was detected in the environment.
	 *
	 * @param array $query_vars Query vars.
	 * @return array Query vars.
	 */
	public function filter_request_after_endpoint_extraction( $query_vars ) {
		if ( $this->did_request_endpoint ) {
			$query_vars[ amp_get_slug() ] = true;
		}
		return $query_vars;
	}

	/**
	 * Restore the path endpoint in environment.
	 *
	 * @see PairedRouting::detect_endpoint_in_environment()
	 *
	 * @param WP $wp WP object.
	 */
	public function restore_path_endpoint_in_environment( WP $wp ) {
		// Since the request has finished the parsing which was detected above in the
		// `PairedRouting::extract_endpoint_from_environment_before_parse_request()`
		// method, now decrement the level.
		$this->current_request_nesting_level--;

		// Only run for the outermost/root request when an AMP endpoint was requested.
		if (
			! $this->did_request_endpoint
			||
			0 !== $this->current_request_nesting_level
		) {
			return;
		}
		foreach ( $this->suspended_environment_variables as $var_name => list( $old_path, ) ) {
			$_SERVER[ $var_name ] = wp_slash( $old_path ); // Because of wp_magic_quotes().
		}
		$this->suspended_environment_variables = [];

		// In case a plugin is looking at $wp->request to see if it is AMP, ensure the path endpoint is added.
		// WordPress is not including it because it was removed in extract_endpoint_from_environment_before_parse_request.

		$request_path = '/';
		if ( $wp->request ) {
			$request_path .= trailingslashit( $wp->request );
		}
		$endpoint_url = $this->add_endpoint( $request_path );
		$request_path = wp_parse_url( $endpoint_url, PHP_URL_PATH );
		$wp->request  = trim( $request_path, '/' );
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
			$slug_check = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Logic adapted from wp_unique_post_slug().
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
		if ( $this->has_endpoint() ) {
			add_filter( 'old_slug_redirect_url', [ $this, 'maybe_add_paired_endpoint' ], 1000 );
			add_filter( 'redirect_canonical', [ $this, 'maybe_add_paired_endpoint' ], 1000 );

			if ( $this->is_using_path_suffix() ) {
				// Filter priority of 0 to purge /amp/ before other filters manipulate it.
				add_filter( 'get_pagenum_link', [ $this, 'filter_get_pagenum_link' ], 0 );
				add_filter( 'redirect_canonical', [ $this, 'filter_redirect_canonical_to_fix_cpage_requests' ], 0 );
			}
		} else {
			add_action( 'wp_head', 'amp_add_amphtml_link' );
		}
	}

	/**
	 * Fix pagenum link when using a path suffix.
	 *
	 * When paired AMP URLs end in /amp/, on the blog index page a call to `the_posts_pagination()` will result in
	 * unexpected links being added to the page. For example, `get_pagenum_link(2)` will result in `/blog/amp/page/2/`
	 * instead of the expected `/blog/page/2/amp/`. And then, when on the 2nd page of results (`/blog/page/2/amp/`), a
	 * call to `get_pagenum_link(3)` will result in an even more unexpected link pointing to `/blog/page/2/amp/page/3/`
	 * instead of `/blog/page/3/amp/`, whereas `get_pagenum_link(1)` will return `/blog/page/2/amp/` as opposed to the
	 * expected `/blog/amp/`. Note that `get_pagenum_link()` is used as the `base` for `paginate_links()` in
	 * `get_the_posts_pagination()`, and it uses as its base `remove_query_arg('paged')` which returns the `REQUEST_URI`.
	 *
	 * @see get_pagenum_link()
	 * @see get_the_posts_pagination()
	 *
	 * @param string $link Pagenum link.
	 * @return string Fixed pagenum link.
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function filter_get_pagenum_link( $link ) {
		global $wp_rewrite;

		// Only relevant when using permalinks.
		if ( ! $wp_rewrite instanceof WP_Rewrite || ! $wp_rewrite->using_permalinks() ) {
			return $link;
		}

		$delimiter = ':';
		$pattern   = $delimiter;

		// If the current page is a paged request (e.g. /page/2/), then we need to first strip that out from the link.
		if ( is_paged() ) {
			$pattern .= sprintf(
				'/%s/%d',
				preg_quote( $wp_rewrite->pagination_base, $delimiter ),
				get_query_var( 'paged' )
			);
		}

		// Now we remove the AMP path segment followed by any additional paged path segments, if they are present.
		// This matches paths like:
		// * /blog/amp/
		// * /blog/amp/page/2/
		// As well as allowing for the lack of a trailing slash and/or the presence of query args and/or a hash target.
		$pattern .= sprintf(
			'/%s((/%s/\d+)?/?(\?.*?)?(#.*)?)$',
			preg_quote( amp_get_slug(), ':' ),
			preg_quote( $wp_rewrite->pagination_base, ':' )
		);

		$pattern .= $delimiter;

		return preg_replace(
			$pattern,
			'$1',
			$link
		);
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
	 * Fix up canonical redirect URLs to put the comments pagination base in the right place.
	 *
	 * @param string $redirect_url Canonical redirect URL.
	 * @return string Updated canonical URL.
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function filter_redirect_canonical_to_fix_cpage_requests( $redirect_url ) {
		global $wp_rewrite;

		if (
			! empty( $redirect_url )
			&&
			get_query_var( 'cpage' )
			&&
			$wp_rewrite instanceof WP_Rewrite
			&&
			! empty( $wp_rewrite->comments_pagination_base )
		) {
			$amp_slug = amp_get_slug();
			$regex    = sprintf(
				":/(%s-[0-9]+)/%s/\\1(/*)($|\?|\#):",
				preg_quote( $wp_rewrite->comments_pagination_base, ':' ),
				preg_quote( $amp_slug, ':' )
			);

			$redirect_url = preg_replace( $regex, "/\\1/{$amp_slug}\\2\\3", $redirect_url );
		}

		return $redirect_url;
	}

	/**
	 * Determine whether permalinks are being used.
	 *
	 * @return bool If permalinks are enabled.
	 */
	public function is_using_permalinks() {
		return ! empty( get_option( 'permalink_structure' ) );
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
				$this->is_using_permalinks()
			) {
				$value = Option::PAIRED_URL_STRUCTURE_LEGACY_READER;
			} elseif ( AMP_Theme_Support::STANDARD_MODE_SLUG !== $options[ Option::THEME_SUPPORT ] ) {
				$value = Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL;
			}
		}

		$defaults[ Option::PAIRED_URL_STRUCTURE ] = $value;
		$defaults[ Option::LATE_DEFINED_SLUG ]    = null;

		return $defaults;
	}

	/**
	 * Sanitize options.
	 *
	 * Note that in a REST API context this is redundant with the enum defined in the schema.
	 *
	 * @param array $options     Existing options with already-sanitized values for updating.
	 * @param array $new_options Unsanitized options being submitted for updating.
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $options, $new_options ) {
		// Cache the AMP slug in options when it is defined late so that it can be referred to early at plugins_loaded.
		$options[ Option::LATE_DEFINED_SLUG ] = $this->get_late_defined_slug();

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
	 * Determine a given URL is for a paired AMP request.
	 *
	 * If no URL is provided, then it will check whether WordPress has already parsed the AMP
	 * query var as part of the request. If still not present, then it will get the current URL
	 * and check if it has an endpoint.
	 *
	 * @param string $url URL to examine. If empty, will use the current URL.
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 * @global WP_Query $wp_the_query
	 */
	public function has_endpoint( $url = '' ) {
		if ( empty( $url ) ) {
			// This is a shortcut to avoid needing to re-parse the current URL.
			if ( $this->did_request_endpoint ) {
				return true;
			}

			$slug = amp_get_slug();

			// On frontend, continue support case where the query var has been (manually) set.
			global $wp_the_query;
			if (
				$wp_the_query instanceof WP_Query
				&&
				false !== $wp_the_query->get( $slug, false )
			) {
				return true;
			}

			// When not in a frontend context (e.g. the Customizer), the query var is the only possibility.
			if (
				is_admin()
				&&
				isset( $_GET[ $slug ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			) {
				return true;
			}

			$url = amp_get_current_url();
		}
		return $this->get_paired_url_structure()->has_endpoint( $url );
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
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL.
	 * @return string URL with AMP stripped.
	 */
	public function remove_endpoint( $url ) {
		return $this->get_paired_url_structure()->remove_endpoint( $url );
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
			$structure = $this->injector->make( $structure_class );

			$paired_urls[ $structure_slug ] = $structure->add_endpoint( $url );
		}

		if ( $this->has_custom_paired_url_structure() ) {
			$paired_urls[ self::PAIRED_URL_STRUCTURE_CUSTOM ] = $this->add_endpoint( $url );
		}

		return $paired_urls;
	}

	/**
	 * Get paired URL examples.
	 *
	 * @return array[] Keys are the structures, values are arrays of paired URLs using the structure.
	 */
	public function get_paired_url_examples() {
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
	public function get_custom_paired_structure_sources() {
		global $wp_filter;
		if ( ! $this->has_custom_paired_url_structure() ) {
			return [];
		}

		if ( ! isset( $wp_filter['amp_custom_paired_url_structure'] ) ) {
			return []; // @codeCoverageIgnore
		}
		$hook = $wp_filter['amp_custom_paired_url_structure'];
		if ( ! $hook instanceof WP_Hook ) {
			return []; // @codeCoverageIgnore
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
			// Is query not yet fixed up to be front page.
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
	 * Redirect to remove the extraneous/erroneous paired endpoint from the requested URI.
	 *
	 * When in Standard mode, the behavior is to strip off /amp/ if it is present on the requested URL when it is a 404.
	 * This ensures that sites switching to AMP-first will have their /amp/ URLs redirecting to the non-AMP, rather than
	 * attempting to redirect to some post that has 'amp' beginning their post slug. Otherwise, in Standard mode a
	 * redirect happens to remove the 'amp' query var if present.
	 *
	 * When in a Paired AMP mode, this handles a case where an AMP page that has a link to `./amp/` can inadvertently
	 * cause an infinite URL space such as `./amp/amp/amp/amp/…`. It also handles the case where the AMP endpoint is
	 * requested but AMP is not available.
	 */
	public function redirect_extraneous_paired_endpoint() {
		$requested_url = amp_get_current_url();
		$redirect_url  = null;

		$has_path_suffix = $this->paired_url->has_path_suffix( $requested_url );
		$has_query_var   = $this->paired_url->has_query_var( $requested_url );
		if ( amp_is_canonical() ) {
			if ( is_404() && $has_path_suffix ) {
				// Always redirect to strip off /amp/ in the case of a 404.
				$redirect_url = $this->paired_url->remove_path_suffix( $requested_url );
			} elseif ( $has_query_var ) {
				// Strip extraneous query var from AMP-first sites.
				$redirect_url = $this->paired_url->remove_query_var( $requested_url );
			}
		} else {
			// Calling wp_old_slug_redirect() here is to account for a site that does not have AMP enabled for the 404 template.
			// This method is running at template_redirect priority 9 in order to run before redirect_canonical() which runs at
			// priority 10. However, wp_old_slug_redirect() also runs at priority 10 (normally), and it needs to run before the
			// redirection happens here since it could be that the 404 template would actually not be getting served but rather
			// the user should be getting redirected to the new permalink where a singular template is served. For this reason,
			// wp_old_slug_redirect() is called just-in-time, and maybe_add_paired_endpoint is added as a filter for
			// old_slug_redirect_url which ensures that the AMP endpoint will persist the slug redirect.
			wp_old_slug_redirect(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_old_slug_redirect_wp_old_slug_redirect

			if ( is_404() && $has_path_suffix ) {
				// To account for switching the paired URL structure from `/amp/` to `?amp=1`, add the query var if in Paired
				// AMP mode. Note this is not necessary to do when sites have switched from a query var to an endpoint suffix
				// because the query var will always be recognized whereas the reverse is not always true.
				// This also prevents an infinite URL space under /amp/ endpoint.
				$redirect_url = $this->add_endpoint( $this->paired_url->remove_path_suffix( $requested_url ) );
			} elseif ( $this->has_endpoint() && ! amp_is_available() ) {
				// Redirect to non-AMP URL if AMP is not available.
				$redirect_url = $this->remove_endpoint( $requested_url );
			}
		}

		if ( $redirect_url && $redirect_url !== $requested_url ) {
			$status_code = current_user_can( 'manage_options' ) ? 302 : 301;
			if ( wp_safe_redirect( $redirect_url, $status_code ) ) {
				exit; // @codeCoverageIgnore
			}
		}
	}
}
