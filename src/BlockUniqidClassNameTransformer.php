<?php
/**
 * Class BlockUniqidClassNameTransformer.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
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
 *
 * @package AmpProject\AmpWP
 * @since 2.2.2
 * @internal
 */
final class BlockUniqidClassNameTransformer implements Conditional, Service, Registerable, Delayed {

	/**
	 * Prefixes of class names that should be transformed.
	 *
	 * @var string[]
	 */
	const CLASS_NAME_PREFIXES = [
		'wp-container-',
		'wp-duotone-',
	];

	/**
	 * The mapping between uniqid- and wp_unique_id-based class names.
	 *
	 * @var array
	 */
	private $class_name_mapping = [];

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'wp';
	}

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
			&&
			amp_is_request()
		);
	}

	/**
	 * Return a name of an action hook used to register block inline styles.
	 *
	 * For block based themes, inline block styles are loaded in head.
	 * For classic themes, styles are loaded in the body because the `wp_head`
	 * action (and `wp_enqueue_scripts`) happens before the `render_block`.
	 *
	 * @return string Action hook name.
	 */
	private static function get_block_inline_style_registration_hook_name() {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			return 'wp_enqueue_scripts';
		}

		return 'wp_footer';
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		add_filter(
			'render_block',
			[ $this, 'transform_class_names_in_block_content' ],
			PHP_INT_MAX,
			1
		);
		add_filter(
			self::get_block_inline_style_registration_hook_name(),
			[ $this, 'transform_class_names_in_inline_styles' ],
			defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound
		);
	}

	/**
	 * Get regular expression pattern for matching uniqid-based class name.
	 *
	 * Note that `uniqid()` returns a string consisting of 13 hex characters.
	 *
	 * @return string Regular expression pattern.
	 */
	private static function get_class_name_regexp_pattern() {
		static $pattern = null;
		if ( null === $pattern ) {
			$combined_block_prefixes = implode( '|', self::CLASS_NAME_PREFIXES );

			$pattern = sprintf(
				'/(?P<start_tag_prefix><\w+[^>]*?\sclass="[^"]*?)(?P<class_name_prefix>%s)(?P<uniqid>[a-f0-9]{13})\b/',
				$combined_block_prefixes
			);
		}
		return $pattern;
	}

	/**
	 * Transform uniqid-based class names in block content.
	 *
	 * @param string $block_content Block content.
	 * @return string Transformed block content.
	 */
	public function transform_class_names_in_block_content( $block_content ) {
		return preg_replace_callback(
			self::get_class_name_regexp_pattern(),
			function( $matches ) {
				$old_class_name = $matches['class_name_prefix'] . $matches['uniqid'];
				$new_class_name = $this->unique_id( $matches['class_name_prefix'] );

				$this->class_name_mapping[ $old_class_name ] = $new_class_name;

				return $matches['start_tag_prefix'] . $new_class_name;
			},
			$block_content
		);
	}

	/**
	 * Transform uniqid-based class names in inline styles.
	 *
	 * @return void
	 */
	public function transform_class_names_in_inline_styles() {
		global $wp_styles;

		$mapped_handles = array_intersect(
			$wp_styles->queue,
			array_keys( $this->class_name_mapping )
		);

		foreach ( $mapped_handles as $handle ) {
			if ( empty( $wp_styles->registered[ $handle ]->extra['after'] ) ) {
				continue;
			}

			$new_class_name = $this->class_name_mapping[ $handle ];

			foreach ( $wp_styles->registered[ $handle ]->extra['after'] as &$inline_styles ) {
				$inline_styles = str_replace( "{$handle}", "{$new_class_name}", $inline_styles );
			}
		}
	}

	/**
	 * Gets unique ID.
	 *
	 * This is a polyfill for WordPress <5.0.3.
	 *
	 * @see wp_unique_id()
	 *
	 * @param string $prefix Prefix for the returned ID.
	 * @return string Unique ID.
	 */
	private static function unique_id( $prefix = '' ) {
		if ( function_exists( 'wp_unique_id' ) ) {
			return wp_unique_id( $prefix );
		} else {
			static $id_counter = 0;
			return $prefix . (string) ++$id_counter;
		}
	}
}
