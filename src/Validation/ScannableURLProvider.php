<?php
/**
 * Provides URLs to scan.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Theme_Support;
use WP_Query;

/**
 * ScannableURLProvider class.
 *
 * @since 2.1
 * @internal
 */
final class ScannableURLProvider {

	/**
	 * Instance of URLScanningContext.
	 *
	 * @var URLScanningContext
	 */
	private $context;

	/**
	 * Class constructor.
	 *
	 * @param URLScanningContext $context Instance of URLScanningContext.
	 */
	public function __construct( URLScanningContext $context ) {
		$this->context = $context;
	}

	/**
	 * Provides the array of URLs to check.
	 *
	 * Each URL is an array with two elements, with the URL at index 0 and the type at index 1.
	 *
	 * @param int|null $offset Optional. The number of URLs to offset by, where applicable. Defaults to 0.
	 * @return array Array of URLs and types.
	 */
	public function get_urls( $offset = 0 ) {
		$urls = [];

		/*
		 * If 'Your homepage displays' is set to 'Your latest posts', include the homepage.
		 */
		if ( 'posts' === get_option( 'show_on_front' ) && $this->is_template_supported( 'is_home' ) ) {
			$urls[] = [
				'url'  => home_url( '/' ),
				'type' => 'home',
			];
		}

		$amp_enabled_taxonomies = array_filter(
			get_taxonomies( [ 'public' => true ] ),
			[ $this, 'does_taxonomy_support_amp' ]
		);
		$public_post_types      = get_post_types( [ 'public' => true ] );
		$limit_per_type         = $this->context->get_limit_per_type();

		// Include one URL of each template/content type, then another URL of each type on the next iteration.
		for ( $i = $offset; $i < $limit_per_type + $offset; $i++ ) {
			// Include all public, published posts.
			foreach ( $public_post_types as $post_type ) {
				$post_ids = $this->get_posts_that_support_amp( $this->get_posts_by_type( $post_type, $i, 1 ) );
				if ( ! empty( $post_ids[0] ) ) {
					$urls[] = [
						'url'  => get_permalink( $post_ids[0] ),
						'type' => $post_type,
					];
				}
			}

			foreach ( $amp_enabled_taxonomies as $taxonomy ) {
				$taxonomy_links = $this->get_taxonomy_links( $taxonomy, $i, 1 );
				$link           = reset( $taxonomy_links );
				if ( ! empty( $link ) ) {
					$urls[] = [
						'url'  => $link,
						'type' => $taxonomy,
					];
				}
			}

			$author_page_urls = $this->get_author_page_urls( $i, 1 );
			if ( ! empty( $author_page_urls[0] ) ) {
				$urls[] = [
					'url'  => $author_page_urls[0],
					'type' => 'author',
				];
			}
		}

		// Only validate 1 date and 1 search page.
		$url = $this->get_date_page();
		if ( $url ) {
			$urls[] = [
				'url'  => $url,
				'type' => 'date',
			];
		}
		$url = $this->get_search_page();
		if ( $url ) {
			$urls[] = [
				'url'  => $url,
				'type' => 'search',
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
		$include_conditionals = $this->context->get_include_conditionals();

		// If we received an allowlist of conditionals, this template conditional must be present in it.
		if ( ! empty( $include_conditionals ) ) {
			return in_array( $template, $include_conditionals, true );
		}
		if ( $this->context->get_include_unsupported() ) {
			return true;
		}

		$supportable_templates = AMP_Theme_Support::get_supportable_templates();

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
		if ( ! $this->is_template_supported( 'is_singular' ) ) {
			return [];
		}

		if ( $this->context->get_include_unsupported() ) {
			return $ids;
		}

		return array_filter(
			$ids,
			'amp_is_post_supported'
		);
	}

	/**
	 * Gets the IDs of public, published posts.
	 *
	 * @param string   $post_type The post type.
	 * @param int|null $offset The offset of the query (optional).
	 * @param int|null $number The number of posts to query for (optional).
	 * @return int[]   $post_ids The post IDs in an array.
	 */
	private function get_posts_by_type( $post_type, $offset = null, $number = null ) {
		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => is_int( $number ) ? $number : $this->context->get_limit_per_type(),
			'post_status'    => 'publish',
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'fields'         => 'ids',
		];
		if ( is_int( $offset ) ) {
			$args['offset'] = $offset;
		}

		// Attachment posts usually have the post_status of 'inherit,' so they can use the status of the post they're attached to.
		if ( 'attachment' === $post_type ) {
			$args['post_status'] = 'inherit';
		}
		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Gets the author page URLs, like https://example.com/author/admin/.
	 *
	 * Accepts an $offset parameter, for the query of authors.
	 * 0 is the first author in the query, and 1 is the second.
	 *
	 * @param int|string $offset The offset for the URL to query for, should be an int if passing an argument.
	 * @param int|string $number The total number to query for, should be an int if passing an argument.
	 * @return string[] The author page URLs, or an empty array.
	 */
	private function get_author_page_urls( $offset = '', $number = '' ) {
		$author_page_urls = [];
		if ( ! $this->is_template_supported( 'is_author' ) ) {
			return $author_page_urls;
		}

		$number = ! empty( $number ) ? $number : $this->context->get_limit_per_type();
		foreach ( get_users( compact( 'offset', 'number' ) ) as $author ) {
			$author_page_urls[] = get_author_posts_url( $author->ID, $author->user_nicename );
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
	 * Gets a single date page URL, like https://example.com/?year=2018.
	 *
	 * @return string|null An example search page, or null.
	 */
	private function get_date_page() {
		if ( ! $this->is_template_supported( 'is_date' ) ) {
			return null;
		}

		return add_query_arg( 'year', gmdate( 'Y' ), home_url( '/' ) );
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
	 * @param string     $taxonomy The name of the taxonomy, like 'category' or 'post_tag'.
	 * @param int|string $offset The number at which to offset the query (optional).
	 * @param int        $number The maximum amount of links to get (optional).
	 * @return string[]  The term links, as an array of strings.
	 */
	private function get_taxonomy_links( $taxonomy, $offset = '', $number = null ) {
		if ( is_null( $number ) ) {
			$number = $this->context->get_limit_per_type();
		}

		return array_map(
			'get_term_link',
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
