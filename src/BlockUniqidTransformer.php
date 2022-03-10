<?php
/**
 * Class BlockUniqidTransformer.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AMP_Block_Uniqid_Sanitizer;

/**
 * Transform uniqid-based IDs into cacheable IDs based on wp_unique_id.
 *
 * Random strings based on `uniqid()` should not be used to generate CSS class
 * names and used in inline styles so that parsed CSS transient caching is not
 * automatically disabled.
 *
 * Instead, `wp_unique_id()` should be used so that the IDs in the class names
 * are more predictable and the CSS transient caching works as expected.
 *
 * @link https://github.com/ampproject/amp-wp/pull/6925
 *
 * @package AmpProject\AmpWP
 * @since 2.2.2
 * @internal
 */
final class BlockUniqidTransformer implements Conditional, Service, Registerable {

	/**
	 * Check whether the Gutenberg plugin is present.
	 *
	 * @return bool
	 */
	public static function has_gutenberg_plugin() {
		return defined( 'GUTENBERG_VERSION' );
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return (
			(
				self::has_gutenberg_plugin()
				||
				version_compare( get_bloginfo( 'version' ), '5.9', '>=' )
			)
		);
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		add_filter(
			'amp_content_sanitizers',
			static function ( $sanitizers ) {
				$sanitizers = array_merge(
					[ AMP_Block_Uniqid_Sanitizer::class => [] ],
					$sanitizers
				);
				return $sanitizers;
			}
		);
	}
}
