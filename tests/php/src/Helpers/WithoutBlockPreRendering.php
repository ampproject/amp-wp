<?php
/**
 * Trait WithoutBlockPreRendering.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

/**
 * Helper trait to skip block pre-rendering for tests that are not block related.
 *
 * @package AmpProject\AmpWP
 */
trait WithoutBlockPreRendering {

	/**
	 * Set up.
	 */
	public function set_up() { //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		parent::set_up();

		add_filter(
			'render_block_data',
			static function( $parsed_block ) {
				global $post;

				if (
					null === $post &&
					defined( 'GUTENBERG_VERSION' ) &&
					version_compare( '8.1.0', GUTENBERG_VERSION, '<=' )
				) {
					// Add a marker to indicate that this is not a block to be pre-rendered.
					$parsed_block['remove_post_data'] = true;
					// Create a dummy post so that there is a post available for the pre-rendering to utilize.
					$post = self::factory()->post->create_and_get();
				}

				return $parsed_block;
			}
		);

		add_filter(
			'render_block_context',
			static function( $context, $parsed_block ) {
				global $post;

				// Remove the dummy post from the global scope and unset post related data from the context.
				if ( isset( $parsed_block['remove_post_data'] ) ) {
					$post = null;
					unset( $context['postId'], $context['postType'], $parsed_block['remove_post_data'] );
				}

				return $context;
			},
			10,
			2
		);
	}
}
