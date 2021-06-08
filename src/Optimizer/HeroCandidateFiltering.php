<?php
/**
 * Class HeroCandidateFiltering.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Optimizer;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\Attribute;
use WP_Post;

/**
 * Service which uses WordPress hooks to inject data-hero-candidate attributes on logos, headers, and the primary featured image.
 *
 * @package AmpProject\AmpWP
 * @since 2.1.0
 * @internal
 */
final class HeroCandidateFiltering implements Service, Delayed, Conditional, Registerable {

	/**
	 * Whether the Custom Logo has been filtered.
	 *
	 * This is set to true after the first filtering to prevent filtering the logo in the footer, for example.
	 *
	 * @var bool
	 */
	protected $custom_logo_filtered = false;

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'wp';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return amp_is_request();
	}

	/**
	 * Register.
	 */
	public function register() {
		add_filter( 'get_custom_logo_image_attributes', [ $this, 'add_custom_logo_data_hero_candidate_attribute' ] );
		add_filter( 'get_header_image_tag', [ $this, 'filter_header_image_tag' ] );
		add_filter( 'wp_get_attachment_image_attributes', [ $this, 'filter_attachment_image_attributes' ], 10, 2 );
	}

	/**
	 * Add data-hero-candidate to attributes array.
	 *
	 * @param array $attrs Attributes.
	 * @return array Amended attributes.
	 */
	public function add_custom_logo_data_hero_candidate_attribute( $attrs ) {
		if ( ! $this->custom_logo_filtered ) {
			$attrs = $this->add_data_hero_candidate_attribute( $attrs );

			$this->custom_logo_filtered = true;
		}
		return $attrs;
	}

	/**
	 * Filter the header image tag HTML to inject the data-hero-candidate attribute.
	 *
	 * @param string $html Header tag.
	 * @return string header image tag.
	 */
	public function filter_header_image_tag( $html ) {
		return preg_replace(
			'/(?<=<img\s)/',
			Attribute::DATA_HERO_CANDIDATE . ' ',
			$html
		);
	}

	/**
	 * Filter attachment image attributes to inject data-hero-candidate for the primary featured image.
	 *
	 * Only the featured image for the singular queried object post or the first post in the loop will be identified.
	 *
	 * @param string[]     $attrs      Array of attribute values for the image markup, keyed by attribute name.
	 * @param WP_Post|null $attachment Image attachment post.
	 * @return string[] Filtered attributes.
	 */
	public function filter_attachment_image_attributes( $attrs, $attachment = null ) {
		global $wp_query;

		$post         = null;
		$queried_post = get_queried_object();
		if ( is_singular() && $queried_post instanceof WP_Post ) {
			$post = $queried_post;
		} elseif ( $wp_query->is_main_query() && isset( $wp_query->posts[0] ) ) {
			$post = $wp_query->posts[0];
		}

		if (
			$post instanceof WP_Post
			&&
			$attachment instanceof WP_Post
			&&
			(int) get_post_thumbnail_id( $post ) === $attachment->ID
		) {
			$attrs = $this->add_data_hero_candidate_attribute( $attrs );
		}
		return $attrs;
	}

	/**
	 * Add data-hero-candidate to attributes array.
	 *
	 * @param array $attrs Attributes.
	 * @return array Amended attributes.
	 */
	public function add_data_hero_candidate_attribute( $attrs ) {
		$attrs[ Attribute::DATA_HERO_CANDIDATE ] = '';
		return $attrs;
	}
}
