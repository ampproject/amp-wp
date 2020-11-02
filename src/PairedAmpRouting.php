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
	 * Endpoint permalink structure.
	 *
	 * This adds `/amp/` to all URLs, even pages and archives. This is a popular option for those who feel query params
	 * are bad for SEO.
	 *
	 * @var string
	 */
	const PERMALINK_STRUCTURE_ENDPOINT = 'endpoint';

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
	 * Permalink structures.
	 *
	 * @var string[]
	 */
	const PERMALINK_STRUCTURES = [
		self::PERMALINK_STRUCTURE_QUERY_VAR,
		self::PERMALINK_STRUCTURE_ENDPOINT,
		self::PERMALINK_STRUCTURE_LEGACY_TRANSITIONAL,
		self::PERMALINK_STRUCTURE_LEGACY_READER,
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
		if ( self::PERMALINK_STRUCTURE_ENDPOINT === AMP_Options_Manager::get_option( Option::PERMALINK_STRUCTURE ) ) {
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
				ReaderThemes::DEFAULT_READER_THEME === $options[ Option::READER_THEME ]
				&&
				AMP_Theme_Support::READER_MODE_SLUG === $options[ Option::THEME_SUPPORT ]
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
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	public function add_paired_endpoint( $url ) {
		$post = null;
		if ( has_filter( 'amp_pre_get_permalink' ) || has_filter( 'amp_get_permalink' ) ) {
			$post_id = url_to_postid( $url );
			if ( $post_id ) {
				$post = get_post( $post_id );
			}
		}

		if ( $post instanceof WP_Post ) {
			/**
			 * Filters the AMP permalink to short-circuit normal generation.
			 *
			 * Returning a non-false value in this filter will cause the `get_permalink()` to get called and the `amp_get_permalink` filter to not apply.
			 *
			 * @since 0.4
			 * @since 1.0 This filter does not apply when 'amp' theme support is present.
			 * @since 2.1 This filter applies again when in non-legacy Reader mode but only when obtaining an AMP URL for a post.
			 * @todo Deprecate this filter?
			 *
			 * @param false $url     Short-circuited URL.
			 * @param int   $post_id Post ID.
			 */
			$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post->ID );

			if ( false !== $pre_url ) {
				return $pre_url;
			}
		}

		$amp_url = add_query_arg( amp_get_slug(), '1', $url );

		if ( $post instanceof WP_Post ) {
			/**
			 * Filters AMP permalink.
			 *
			 * @since 0.2
			 * @since 1.0 This filter does not apply when 'amp' theme support is present.
			 * @since 2.1 This filter applies again when in non-legacy Reader mode but only when obtaining an AMP URL for a post.
			 * @todo Deprecate this filter?
			 *
			 * @param false $amp_url AMP URL.
			 * @param int $post_id Post ID.
			 */
			$amp_url = apply_filters( 'amp_get_permalink', $amp_url, $post->ID );
		}

		return $amp_url;
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL to examine. If empty, will use the current URL.
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 * @global WP_Query $wp_query
	 */
	public function has_paired_endpoint( $url = '' ) {
		$slug = amp_get_slug();

		// If the URL was not provided, then use the environment which is already parsed.
		if ( empty( $url ) ) {
			global $wp_query;
			return (
				isset( $_GET[ $slug ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				||
				(
					$wp_query instanceof WP_Query
					&&
					false !== $wp_query->get( $slug, false )
				)
			);
		}

		$parsed_url = wp_parse_url( $url );
		if ( ! empty( $parsed_url['query'] ) ) {
			$query_vars = [];
			wp_parse_str( $parsed_url['query'], $query_vars );
			if ( isset( $query_vars[ $slug ] ) ) {
				return true;
			}
		}

		if ( ! empty( $parsed_url['path'] ) ) {
			$pattern = sprintf(
				'#/%s(/[^/^])?/?$#',
				preg_quote( $slug, '#' )
			);
			if ( preg_match( $pattern, $parsed_url['path'] ) ) {
				return true;
			}
		}

		return false;
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
		$url = preg_replace(
			sprintf(
				':(/%s(/[^/?#]+)?)+(?=/?(\?|#|$)):',
				preg_quote( $slug, ':' )
			),
			'',
			$url
		);

		// Strip query var, including ?amp, ?amp=1, etc.
		$url = remove_query_arg( $slug, $url );

		return $url;
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
