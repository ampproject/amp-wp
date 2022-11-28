<?php
/**
 * Class BlockUniqidTransformer.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Block_Uniqid_Sanitizer;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

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
 * @link https://github.com/WordPress/gutenberg/issues/38889
 *
 * @package AmpProject\AmpWP
 * @since 2.2.2
 * @internal
 */
final class BlockUniqidTransformer implements Service, Registerable {

	/**
	 * Gutenberg version.
	 *
	 * @var string
	 */
	private $gutenberg_version = null;

	/**
	 * Construct.
	 */
	public function __construct() {
		if ( defined( 'GUTENBERG_VERSION' ) ) {
			$this->gutenberg_version = GUTENBERG_VERSION;
		}
	}

	/**
	 * Check whether the Gutenberg plugin is present and if its one of the affected versions.
	 *
	 * Elements was added in 10.7 via WordPress/gutenberg#31524
	 * Layout was added in 11.2 via WordPress/gutenberg#33359
	 * Duotone was added in 11.7 via WordPress/gutenberg#34667
	 * `uniqid` has been replaced by `wp_unique_id` in 12.7 via WordPress/gutenberg#38891
	 *
	 * @param string|null $version Gutenberg version to check. If null, current version is used.
	 * @return bool Whether affected Gutenberg version.
	 */
	public function is_affected_gutenberg_version( $version = null ) {
		if ( empty( $version ) ) {
			$version = $this->gutenberg_version;
		}

		if ( empty( $version ) ) {
			return false;
		}

		return (
			version_compare( $version, '10.7', '>=' )
			&&
			version_compare( $version, '12.7', '<' )
		);
	}

	/**
	 * Check whether WordPress version is affected by the `uniqid` issue.
	 *
	 * The affected WordPress version is 5.9. However, the duotone filter was first
	 * introduced in WordPress 5.8 and it makes use of the `uniqid`, too.
	 *
	 * @param string|null $version WordPress core version to check. If null, current version is used.
	 * @return bool Whether affected WP version.
	 */
	public function is_affected_wordpress_version( $version = null ) {
		if ( empty( $version ) ) {
			$version = get_bloginfo( 'version' );
		}
		return (
			version_compare( $version, '5.8', '>=' )
			&&
			version_compare( $version, '5.9.3', '<' )
		);
	}

	/**
	 * Check whether the transformer is necessary.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public function is_necessary() {
		return (
			$this->is_affected_gutenberg_version()
			||
			$this->is_affected_wordpress_version()
		);
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		if ( ! $this->is_necessary() ) {
			return;
		}

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
