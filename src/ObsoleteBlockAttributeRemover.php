<?php
/**
 * Class ObsoleteBlockAttributeRemover.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_REST_Response;

/**
 * Removes obsolete data-amp-* attributes from block markup in post content.
 *
 * These HTML attributes serve as processing instructions to control how the sanitizers handle converting the HTML to AMP.
 * For each HTML attribute there is also a block attribute, so if there is a data-amp-carousel HTML attribute then there
 * is also an ampCarousel block attribute. The block attributes were originally mirrored onto the HTML attributes because
 * the 'render_block' filter was not available in Gutenberg (or WordPress Core) when this was first implemented; now that
 * this filter is available, there is no need to duplicate the mirror the attributes, and so they are injected into the
 * root HTML element via `AMP_Core_Block_Handler::filter_rendered_block()`. In hindsight, instead of having the data
 * mirrored between block attributes and HTML attributes, the block attributes should have perhaps used an 'attribute'
 * as the block attribute 'source'. Then again, that may have complicated things yet further to migrate away from using
 * these data attributes. A key reason for why these HTML data-* attributes are bad is that they cause block validation
 * errors. If someone creates a Gallery block and enables a carousel, then if they go and deactivate the AMP plugin,
 * this block will then show as having a block validation error. If, however, we restrict the block attributes to only
 * be in the block comment, then no block validation errors occurs. Also, since the 'render_block' filter is now
 * available, the reason for storing these attributes as data-amp-* HTML attributes in post_content is now obsolete.
 *
 * @see AMP_Core_Block_Handler::filter_rendered_block()
 * @link https://github.com/ampproject/amp-wp/pull/4775
 *
 * @package AmpProject\AmpWP
 */
final class ObsoleteBlockAttributeRemover implements Service, Registerable, Delayed {

	/**
	 * Obsolete attributes.
	 *
	 * @var string[]
	 */
	const OBSOLETE_ATTRIBUTES = [
		'data-amp-carousel',
		'data-amp-lightbox',
		'data-amp-noloading',
		'data-amp-layout',
	];

	/**
	 * Pattern matching the obsolete attribute key/value pair in an HTML start tag.
	 *
	 * @var string
	 */
	protected $obsolete_attribute_pattern;

	/**
	 * Get registration action.
	 *
	 * @return string
	 */
	public static function get_registration_action() {
		return 'rest_api_init';
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		$this->obsolete_attribute_pattern = sprintf( '/\s(%s)="[^"]*+"/', implode( '|', self::OBSOLETE_ATTRIBUTES ) );
		foreach ( get_post_types_by_support( 'editor' ) as $post_type ) {
			add_filter( "rest_prepare_{$post_type}", [ $this, 'filter_rest_prepare_post' ] );
		}
	}

	/**
	 * Filter post response object to purge obsolete attributes from the raw content.
	 *
	 * @param WP_REST_Response $response Response.
	 * @return WP_REST_Response Response.
	 */
	public function filter_rest_prepare_post( WP_REST_Response $response ) {
		if ( isset( $response->data['content']['raw'] ) ) {
			$response->data['content']['raw'] = preg_replace_callback(
				'#(?P<before_html_attrs><!--\s*wp:\w+.*?-->\s*<\w+\S*?)(?P<html_attrs>\s+[^>]*+)\s*>#s',
				function ( $matches ) {
					return $matches['before_html_attrs'] . preg_replace( $this->obsolete_attribute_pattern, '', $matches['html_attrs'] ) . '>';
				},
				$response->data['content']['raw']
			);
		}
		return $response;
	}
}
