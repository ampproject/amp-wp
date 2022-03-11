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
	 * Check whether the Gutenberg plugin is present and if its one of the affected versions.
	 *
	 * Elements was added in 10.7 via WordPress/gutenberg#31524
	 * Layout was added in 11.2 via WordPress/gutenberg#33359
	 * Duotone was added in 11.7 via WordPress/gutenberg#34667
	 * `uniqid` has been replaced by `wp_unique_id` in 12.7 via WordPress/gutenberg#38891
	 *
	 * @return bool
	 */
	public static function is_affected_gutenberg_version() {
		return (
			defined( 'GUTENBERG_VERSION' )
			&&
			version_compare( GUTENBERG_VERSION, '10.7', '>=' )
			&&
			version_compare( GUTENBERG_VERSION, '12.7', '<' )
		);
	}

	/**
	 * Check whether WordPress version is affected by the `uniqid` issue.
	 *
	 * The affected WordPress version is 5.9. However, the duotone filter was first
	 * introduced in WordPress 5.8 and it makes use of the `uniqid`, too.
	 *
	 * @todo Once the `uniqid` to `wp_unique_id` fix is backported to core, upper version boundary should be updated (it's set to 6.0 for now).
	 *
	 * @return bool
	 */
	public static function is_affected_wordpress_version() {
		return (
			version_compare( get_bloginfo( 'version' ), '5.8', '>=' )
			&&
			version_compare( get_bloginfo( 'version' ), '6.0', '<' )
		);
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return (
			self::is_affected_gutenberg_version()
			||
			self::is_affected_wordpress_version()
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
