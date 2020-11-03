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
use WP_Post;

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
	 * Query var permalink structure.
	 *
	 * This is the default, where all AMP URLs end in `?amp=1`.
	 *
	 * @var string
	 */
	const PERMALINK_STRUCTURE_QUERY_VAR = 'query_var';

	/**
	 * Rewrite endpoint permalink structure.
	 *
	 * This adds `/amp/` to all URLs, even pages and archives. This is a popular option for those who feel query params
	 * are bad for SEO.
	 *
	 * @var string
	 */
	const PERMALINK_STRUCTURE_REWRITE_ENDPOINT = 'rewrite_endpoint';

	/**
	 * Legacy transitional permalink structure.
	 *
	 * This involves using `?amp` for all paired AMP URLs.
	 *
	 * @var string
	 */
	const PERMALINK_STRUCTURE_LEGACY_TRANSITIONAL = 'legacy_transitional';

	/**
	 * Legacy transitional permalink structure.
	 *
	 * This involves using `/amp/` for all non-hierarchical post URLs which lack endpoints or query vars, or else using
	 * the same `?amp` as used by legacy transitional.
	 *
	 * @var string
	 */
	const PERMALINK_STRUCTURE_LEGACY_READER = 'legacy_reader';

	/**
	 * Custom permalink structure.
	 *
	 * This involves a site adding the necessary filters to implement their own permalink structure.
	 *
	 * @var string
	 */
	const PERMALINK_STRUCTURE_CUSTOM = 'custom';

	/**
	 * Permalink structures.
	 *
	 * @var string[]
	 */
	const PERMALINK_STRUCTURES = [
		self::PERMALINK_STRUCTURE_QUERY_VAR,
		self::PERMALINK_STRUCTURE_REWRITE_ENDPOINT,
		self::PERMALINK_STRUCTURE_LEGACY_TRANSITIONAL,
		self::PERMALINK_STRUCTURE_LEGACY_READER,
		self::PERMALINK_STRUCTURE_CUSTOM,
	];

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

		add_action( 'init', [ $this, 'add_rewrite_endpoint' ], 0 );
		add_action( 'parse_query', [ $this, 'correct_query_when_is_front_page' ] );

		add_filter( 'old_slug_redirect_url', [ $this, 'filter_old_slug_redirect_url' ] );
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
		if ( self::PERMALINK_STRUCTURE_REWRITE_ENDPOINT === AMP_Options_Manager::get_option( Option::PERMALINK_STRUCTURE ) ) {
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
		$value = self::PERMALINK_STRUCTURE_QUERY_VAR;

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
				$value = self::PERMALINK_STRUCTURE_LEGACY_READER;
			} elseif ( AMP_Theme_Support::STANDARD_MODE_SLUG !== $options[ Option::THEME_SUPPORT ] ) {
				$value = self::PERMALINK_STRUCTURE_LEGACY_TRANSITIONAL;
			}
		}

		$defaults[ Option::PERMALINK_STRUCTURE ] = $value;

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
			isset( $new_options[ Option::PERMALINK_STRUCTURE ] )
			&&
			in_array( $new_options[ Option::PERMALINK_STRUCTURE ], self::PERMALINK_STRUCTURES, true )
		) {
			$options[ Option::PERMALINK_STRUCTURE ] = $new_options[ Option::PERMALINK_STRUCTURE ];
		}
		return $options;
	}

	/**
	 * Get the current paired AMP permalink structure.
	 *
	 * @return string Paired AMP permalink structure.
	 */
	public function get_permalink_structure() {
		$has_filters      = [
			has_filter( 'amp_has_paired_endpoint' ),
			has_filter( 'amp_add_paired_endpoint' ),
			has_filter( 'amp_remove_paired_endpoint' ),
		];
		$has_filter_count = count( array_filter( $has_filters ) );
		if ( 3 === $has_filter_count ) {
			return self::PERMALINK_STRUCTURE_CUSTOM;
		} elseif ( $has_filter_count > 0 ) {
			_doing_it_wrong(
				'add_filter',
				esc_html__( 'In order to implement a custom paired AMP permalink structure, you must add three filters:', 'amp' ) . ' amp_has_paired_endpoint, amp_add_paired_endpoint, amp_remove_paired_endpoint',
				'2.1'
			);
		}

		return AMP_Options_Manager::get_option( Option::PERMALINK_STRUCTURE );
	}

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	public function add_paired_endpoint( $url ) {
		$permalink_structure = self::get_permalink_structure();
		switch ( $permalink_structure ) {
			case self::PERMALINK_STRUCTURE_REWRITE_ENDPOINT:
				return $this->get_rewrite_endpoint_paired_amp_url( $url );
			case self::PERMALINK_STRUCTURE_LEGACY_TRANSITIONAL:
				return $this->get_legacy_transitional_paired_amp_url( $url );
			case self::PERMALINK_STRUCTURE_LEGACY_READER:
				return $this->get_legacy_reader_paired_amp_url( $url );
		}

		// This is the PERMALINK_STRUCTURE_QUERY_VAR case, the default.
		$amp_url = $this->get_query_var_paired_amp_url( $url );

		if ( self::PERMALINK_STRUCTURE_CUSTOM === $permalink_structure ) {
			/**
			 * Filters paired AMP URL to apply a custom permalink structure.
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
	 * @todo What are the scenarios where this conflicts with other rewrite endpoints also added?
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

		$parsed_url['path']  = trailingslashit( $parsed_url['path'] );
		$parsed_url['path'] .= user_trailingslashit( amp_get_slug(), 'amp' );

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
			 * @since 1.0 This filter only applies when using the legacy reader permalink structure.
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
		$structure     = get_option( 'permalink_structure' );
		$use_query_var = (
			// If pretty permalinks aren't available, then query var must be used.
			empty( $structure )
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
			 * @since 1.0 This filter only applies when using the legacy reader permalink structure.
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

		if ( self::get_permalink_structure() === self::PERMALINK_STRUCTURE_CUSTOM ) {
			/**
			 * Filters whether the URL has a paired AMP permalink structure.
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

		$permalink_structure = self::get_permalink_structure();
		if ( self::PERMALINK_STRUCTURE_CUSTOM === $permalink_structure ) {
			/**
			 * Filters paired AMP URL to remove a custom permalink structure.
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
	 * Redirects the old AMP URL to the new AMP URL.
	 *
	 * If post slug is updated the amp page with old post slug will be redirected to the updated url.
	 *
	 * @param string $url New URL of the post.
	 * @return string URL to be redirected.
	 */
	public function filter_old_slug_redirect_url( $url ) {

		if ( amp_is_request() && ! amp_is_canonical() ) {
			$url = amp_add_paired_endpoint( $url );
		}

		return $url;
	}
}
