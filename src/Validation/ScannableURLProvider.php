<?php
/**
 * Provides URLs to scan.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\Infrastructure\Service;
use AMP_Theme_Support;
use WP_Query;
use AMP_Options_Manager;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Admin\ReaderThemes;

/**
 * ScannableURLProvider class.
 *
 * @since 2.1
 * @internal
 */
final class ScannableURLProvider implements Service {

	/**
	 * Template conditionals to restrict results to.
	 *
	 * @var string[]
	 */
	private $include_conditionals = [];

	/**
	 * Limit for the number of URLs to obtain for each template type.
	 *
	 * @var int
	 */
	private $limit_per_type;

	/**
	 * Construct.
	 *
	 * @param string[] $include_conditionals Template conditionals to restrict results to.
	 * @param int      $limit_per_type       Limit of URLs to obtain per type.
	 */
	public function __construct( $include_conditionals = [], $limit_per_type = 1 ) {
		$this->include_conditionals = $include_conditionals;
		$this->limit_per_type       = $limit_per_type;
	}

	/**
	 * Get supportable templates.
	 *
	 * If the current options are for legacy Reader mode, then the templates not supported by it are disabled.
	 *
	 * @see AMP_Theme_Support::get_supportable_templates()
	 *
	 * @return array Supportable templates.
	 */
	public function get_supportable_templates() {
		$options = AMP_Options_Manager::get_options();

		$supportable_templates = AMP_Theme_Support::get_supportable_templates( $options );

		if (
			AMP_Theme_Support::READER_MODE_SLUG === $options[ Option::THEME_SUPPORT ]
			&&
			ReaderThemes::DEFAULT_READER_THEME === $options[ Option::READER_THEME ]
		) {
			$allowed_templates = [
				'is_singular',
			];
			if ( 'page' === get_option( 'show_on_front' ) ) {
				$page_for_posts = get_option( 'page_for_posts' );
				if ( $page_for_posts && amp_is_post_supported( $page_for_posts ) ) {
					$allowed_templates[] = 'is_home';
				}
				$page_on_front = get_option( 'page_on_front' );
				if ( $page_on_front && amp_is_post_supported( $page_on_front ) ) {
					$allowed_templates[] = 'is_front_page';
				}
			}
			foreach ( array_diff( array_keys( $supportable_templates ), $allowed_templates ) as $template ) {
				$supportable_templates[ $template ]['supported'] = false;
			}
		}
		return $supportable_templates;
	}

	/**
	 * Set include conditionals.
	 *
	 * @param string[] $include_conditionals Include conditionals.
	 */
	public function set_include_conditionals( $include_conditionals ) {
		$this->include_conditionals = $include_conditionals;
	}

	/**
	 * Set limit per type.
	 *
	 * @param int $limit_per_type Limit per type.
	 */
	public function set_limit_per_type( $limit_per_type ) {
		$this->limit_per_type = $limit_per_type;
	}

	/**
	 * Get the array of URLs to check.
	 *
	 * @return array Array of URLs and types.
	 */
	public function get_urls() {
		$urls = [];

		/*
		 * If 'Your homepage displays' is set to 'Your latest posts', include the homepage.
		 */
		if ( 'posts' === get_option( 'show_on_front' ) ) {
			if ( $this->is_template_supported( 'is_home' ) ) {
				$urls[] = [
					'url'   => home_url( '/' ),
					'type'  => 'is_home',
					'label' => __( 'Homepage', 'amp' ),
				];
			}
		} elseif ( 'page' === get_option( 'show_on_front' ) ) {
			if (
				$this->is_template_supported( 'is_front_page' )
				&&
				get_option( 'page_on_front' )
				&&
				amp_is_post_supported( get_option( 'page_on_front' ) )
			) {
				$urls[] = [
					'url'   => get_permalink( get_option( 'page_on_front' ) ),
					'type'  => 'is_front_page',
					'label' => __( 'Homepage', 'amp' ),
				];
			}
			if (
				$this->is_template_supported( 'is_home' )
				&&
				get_option( 'page_for_posts' )
				&&
				amp_is_post_supported( get_option( 'page_for_posts' ) )
			) {
				$urls[] = [
					'url'   => get_permalink( get_option( 'page_for_posts' ) ),
					'type'  => 'is_home',
					'label' => __( 'Blog', 'amp' ),
				];
			}
		}

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( [ 'public' => true ] ),
			function ( $taxonomy ) {
				return $this->does_taxonomy_support_amp( $taxonomy );
			}
		);
		$public_post_types      = get_post_types( [ 'public' => true ] );

		// Include one URL of each template/content type, then another URL of each type on the next iteration.
		for ( $i = 0; $i < $this->limit_per_type; $i++ ) {
			if ( $this->is_template_supported( 'is_singular' ) ) {
				foreach ( $public_post_types as $post_type ) {
					$post_ids = $this->get_posts_by_type( $post_type, $i );
					$post_id  = reset( $post_ids );
					if ( $post_id ) {
						$post_type_object = get_post_type_object( $post_type );
						$urls[]           = [
							'url'   => get_permalink( $post_id ),
							'type'  => sprintf( 'is_singular[%s]', $post_type ),
							'label' => $post_type_object->labels->singular_name ?: $post_type,
						];
					}
				}
			}

			foreach ( $amp_enabled_taxonomies as $taxonomy ) {
				$taxonomy_links = $this->get_taxonomy_links( $taxonomy, $i, 1 );
				$link           = reset( $taxonomy_links );
				if ( $link ) {
					$taxonomy_object = get_taxonomy( $taxonomy );
					$urls[]          = [
						'url'   => $link,
						'type'  => sprintf( 'is_tax[%s]', $taxonomy ),
						'label' => $taxonomy_object->labels->singular_name ?: $taxonomy,
					];
				}
			}

			$author_page_urls = $this->get_author_page_urls( $i, 1 );
			$author_page_url  = reset( $author_page_urls );
			if ( $author_page_url ) {
				$urls[] = [
					'url'   => $author_page_url,
					'type'  => 'is_author',
					'label' => __( 'Author Archive', 'amp' ),
				];
			}
		}

		// Only validate 1 date and 1 search page.
		$url = $this->get_date_page();
		if ( $url ) {
			$urls[] = [
				'url'   => $url,
				'type'  => 'is_date',
				'label' => __( 'Date Archive', 'amp' ),
			];
		}
		$url = $this->get_search_page();
		if ( $url ) {
			$urls[] = [
				'url'   => $url,
				'type'  => 'is_search',
				'label' => __( 'Search Results', 'amp' ),
			];
		}

		return $urls;
	}

	/**
	 * Gets whether the template is supported.
	 *
	 * @param string $template The template to check.
	 * @return bool Whether the template is supported.
	 */
	private function is_template_supported( $template ) {

		// If we received an allowlist of conditionals, this template conditional must be present in it.
		if (
			count( $this->include_conditionals ) > 0
			&&
			! in_array( $template, $this->include_conditionals, true )
		) {
			return false;
		}

		$supportable_templates = $this->get_supportable_templates();

		// Check whether this taxonomy's template is supported, including in the 'AMP Settings' > 'Supported Templates' UI.
		return ! empty( $supportable_templates[ $template ]['supported'] );
	}

	/**
	 * Gets the post IDs that support AMP.
	 *
	 * By default, this only gets the post IDs if they support AMP.
	 * This means that 'Posts' isn't deselected in 'AMP Settings' > 'Supported Templates'
	 * and 'Enable AMP' isn't unchecked in the post's editor.
	 *
	 * @param int[] $ids The post IDs to check for AMP support.
	 * @return array The post IDs that support AMP, or an empty array.
	 */
	private function get_posts_that_support_amp( $ids ) {
		return array_values(
			array_filter(
				$ids,
				static function ( $id ) {
					return amp_is_post_supported( $id );
				}
			)
		);
	}

	/**
	 * Gets the IDs of published posts that support AMP.
	 *
	 * @see \amp_admin_get_preview_permalink()
	 *
	 * @param string   $post_type The post type.
	 * @param int|null $offset The offset of the query (optional).
	 * @return int[]   $post_ids The post IDs in an array.
	 */
	private function get_posts_by_type( $post_type, $offset = null ) {
		// Note that we get 100 posts because it may be that some of them have AMP disabled. It is more
		// efficient to do it this way than to try to do a meta query that looks for posts that have the
		// amp_status meta equal to 'enabled' or else for posts that lack the meta key altogether. In the latter
		// case, the absence of the meta may not mean AMP is enabled since the default-enabled state can be
		// overridden with the `amp_post_status_default_enabled` filter. So in this case, we grab 100 post IDs
		// and then just use the first one.
		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'fields'         => 'ids',
		];
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$args['post__not_in'] = [
				(int) get_option( 'page_for_posts' ),
				(int) get_option( 'page_on_front' ),
			];
		}
		if ( is_int( $offset ) ) {
			$args['offset'] = $offset;
		}

		// Attachment posts usually have the post_status of 'inherit,' so they can use the status of the post they're attached to.
		if ( 'attachment' === $post_type ) {
			$args['post_status'] = 'inherit';
		}
		$query = new WP_Query( $args );

		return $this->get_posts_that_support_amp( $query->posts );
	}

	/**
	 * Gets the author page URLs, like https://example.com/author/admin/.
	 *
	 * Accepts an $offset parameter, for the query of authors.
	 * 0 is the first author in the query, and 1 is the second.
	 *
	 * @param int $offset The offset for the URL to query for, should be an int if passing an argument.
	 * @param int $number The total number to query for, should be an int if passing an argument.
	 * @return string[] The author page URLs, or an empty array.
	 */
	private function get_author_page_urls( $offset, $number ) {
		$author_page_urls = [];
		if ( ! $this->is_template_supported( 'is_author' ) ) {
			return $author_page_urls;
		}

		foreach ( get_users( compact( 'offset', 'number' ) ) as $author ) {
			$authored_post_query = new WP_Query(
				[
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'author'         => $author->ID,
					'posts_per_page' => 1,
				]
			);
			if ( count( $authored_post_query->get_posts() ) > 0 ) {
				$author_page_urls[] = get_author_posts_url( $author->ID, $author->user_nicename );
			}
		}

		return $author_page_urls;
	}

	/**
	 * Gets a single search page URL, like https://example.com/?s=example.
	 *
	 * @return string|null An example search page, or null.
	 */
	private function get_search_page() {
		if ( ! $this->is_template_supported( 'is_search' ) ) {
			return null;
		}

		return add_query_arg( 's', 'example', home_url( '/' ) );
	}

	/**
	 * Gets a single date page URL, like https://example.com/2018/.
	 *
	 * @return string|null An example year archive URL, or null.
	 */
	private function get_date_page() {
		if ( ! $this->is_template_supported( 'is_date' ) ) {
			return null;
		}

		$query = new WP_Query(
			[
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);
		$posts = $query->get_posts();

		$latest_post = array_shift( $posts );
		if ( ! $latest_post ) {
			return null;
		}

		$year = (int) get_the_date( 'Y', $latest_post );
		if ( $year <= 0 ) {
			return null;
		}

		return get_year_link( $year );
	}

	/**
	 * Gets whether the taxonomy supports AMP.
	 *
	 * @param string $taxonomy The taxonomy.
	 * @return boolean Whether the taxonomy supports AMP.
	 */
	private function does_taxonomy_support_amp( $taxonomy ) {
		if ( 'post_tag' === $taxonomy ) {
			$taxonomy = 'tag';
		}
		$taxonomy_key        = 'is_' . $taxonomy;
		$custom_taxonomy_key = sprintf( 'is_tax[%s]', $taxonomy );
		return $this->is_template_supported( $taxonomy_key ) || $this->is_template_supported( $custom_taxonomy_key );
	}

	/**
	 * Gets the front-end links for taxonomy terms.
	 * For example, https://example.org/?cat=2
	 *
	 * @param string $taxonomy The name of the taxonomy, like 'category' or 'post_tag'.
	 * @param int    $offset The number at which to offset the query (optional).
	 * @param int    $number The maximum amount of links to get (optional).
	 * @return string[]  The term links, as an array of strings.
	 */
	private function get_taxonomy_links( $taxonomy, $offset, $number ) {
		return array_map(
			static function ( $term ) {
				return get_term_link( $term );
			},
			get_terms(
				array_merge(
					compact( 'taxonomy', 'offset', 'number' ),
					[
						'orderby' => 'id',
					]
				)
			)
		);
	}
}
