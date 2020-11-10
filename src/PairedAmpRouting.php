<?php
/**
 * Class PairedAmpRouting.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Activateable;
use AmpProject\AmpWP\Infrastructure\Deactivateable;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Admin\ReaderThemes;
use WP_Query;
use WP_Rewrite;

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
		Option::PAIRED_URL_STRUCTURE_REWRITE_ENDPOINT,
		Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL,
		Option::PAIRED_URL_STRUCTURE_LEGACY_READER,
		Option::PAIRED_URL_STRUCTURE_CUSTOM,
	];

	/**
	 * Regular expression pattern matching any added endpoints in a URL.
	 *
	 * @var string
	 */
	protected $added_rewrite_endpoints_pattern;

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
		global $wp_rewrite;
		foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
			if ( amp_get_slug() === $endpoint[1] ) {
				unset( $wp_rewrite->endpoints[ $index ] );
				break;
			}
		}

		$this->flush_rewrite_rules();
	}

	/**
	 * Register.
	 */
	public function register() {
		add_filter( 'amp_default_options', [ $this, 'filter_default_options' ], 10, 2 );
		add_filter( 'amp_options_updating', [ $this, 'sanitize_options' ], 10, 2 );
		add_action( 'update_option_' . AMP_Options_Manager::OPTION_NAME, [ $this, 'handle_options_update' ], 10, 2 );

		add_action( 'init', [ $this, 'add_rewrite_endpoint' ], 0 );

		if ( ! amp_is_canonical() ) {
			add_action( 'parse_query', [ $this, 'correct_query_when_is_front_page' ] );
			add_filter( 'old_slug_redirect_url', [ $this, 'maybe_add_paired_endpoint' ], 1000 );
			add_filter( 'redirect_canonical', [ $this, 'maybe_add_paired_endpoint' ], 1000 );
			add_filter( 'paginate_links', [ $this, 'fix_paginate_links' ] );
		}
	}

	/**
	 * Flush rewrite rules.
	 */
	public function flush_rewrite_rules() {
		flush_rewrite_rules( false );
	}

	/**
	 * Add rewrite endpoint.
	 */
	public function add_rewrite_endpoint() {
		if ( Option::PAIRED_URL_STRUCTURE_REWRITE_ENDPOINT === AMP_Options_Manager::get_option( Option::PAIRED_URL_STRUCTURE ) ) {
			$places = EP_ALL;
		} else {
			$places = EP_PERMALINK;
		}
		add_rewrite_endpoint( amp_get_slug(), $places );
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
	 * @param array $options     Existing options with already-sanitized values for updating.
	 * @param array $new_options Unsanitized options being submitted for updating.
	 *
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
			return Option::PAIRED_URL_STRUCTURE_CUSTOM;
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
		if ( ! $this->has_custom_paired_url_structure() ) {
			$structures = array_diff( $structures, [ Option::PAIRED_URL_STRUCTURE_CUSTOM ] );
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
			case Option::PAIRED_URL_STRUCTURE_REWRITE_ENDPOINT:
				return $this->get_rewrite_endpoint_paired_amp_url( $url );
			case Option::PAIRED_URL_STRUCTURE_LEGACY_TRANSITIONAL:
				return $this->get_legacy_transitional_paired_amp_url( $url );
			case Option::PAIRED_URL_STRUCTURE_LEGACY_READER:
				return $this->get_legacy_reader_paired_amp_url( $url );
		}

		// This is the PAIRED_URL_STRUCTURE_QUERY_VAR case, the default.
		$amp_url = $this->get_query_var_paired_amp_url( $url );

		if ( Option::PAIRED_URL_STRUCTURE_CUSTOM === $structure ) {
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
	 * Determine whether a given URL path contains a registered rewrite endpoint.
	 *
	 * This is needed to prevent adding the `/amp/` rewrite endpoint to a URL which already has an existing rewrite
	 * endpoint, as in this case it will fail to match.
	 *
	 * @param string $path URL Path.
	 * @return bool Whether the supplied path contains a registered rewrite endpoint.
	 * @global WP_Rewrite
	 */
	private function is_added_rewrite_endpoint_in_path( $path ) {
		global $wp_rewrite;
		if ( null === $this->added_rewrite_endpoints_pattern ) {
			$pattern_delimiter = '#';
			$endpoint_patterns = [
				preg_quote( $wp_rewrite->pagination_base, $pattern_delimiter ) . '/\d+',
			];
			foreach ( $wp_rewrite->endpoints as $endpoint ) {
				$endpoint_patterns[] = preg_quote( $endpoint[1], $pattern_delimiter );
			}
			$this->added_rewrite_endpoints_pattern = $pattern_delimiter . '/' . implode( '|', $endpoint_patterns ) . '(/|$)' . $pattern_delimiter;
		}
		return (bool) preg_match( $this->added_rewrite_endpoints_pattern, $path );
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
		if ( empty( $parsed_url['scheme'] ) ) {
			$parsed_url['scheme'] = is_ssl() ? 'https' : 'http';
		}
		if ( ! isset( $parsed_url['host'] ) ) {
			$parsed_url['host'] = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : 'localhost';
		}

		$has_existing_rewrite_endpoint = $this->is_added_rewrite_endpoint_in_path( $parsed_url['path'] );
		if ( ! $has_existing_rewrite_endpoint ) {
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
		if ( $has_existing_rewrite_endpoint ) {
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

		$parsed_url          = wp_parse_url( $url );
		$permalink_structure = get_option( 'permalink_structure' );
		$use_query_var       = (
			// If pretty permalinks aren't available, then query var must be used.
			empty( $permalink_structure )
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
		if ( $url && amp_is_request() ) {
			$url = $this->add_paired_endpoint( $url );
		}
		return $url;
	}

	/**
	 * Fix `paginate_links()` erroneously adding the pagination base after the AMP endpoint, e.g. `/amp/page/2/`.
	 *
	 * @see paginate_links()
	 *
	 * @param string $link The paginated link URL.
	 * @return string Fixed paged link.
	 */
	public function fix_paginate_links( $link ) {
		global $wp_rewrite;

		$link = preg_replace(
			sprintf(
				':/%s(?=/%s/\d+):',
				preg_quote( amp_get_slug(), ':' ),
				preg_quote( $wp_rewrite->pagination_base, ':' )
			),
			'',
			$link
		);

		return $link;
	}
}
