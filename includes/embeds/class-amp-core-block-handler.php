<?php
/**
 * Class AMP_Core_Block_Handler
 *
 * @package AMP
 */

/**
 * Class AMP_Core_Block_Handler
 *
 * @since 1.0
 */
class AMP_Core_Block_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Render methods for blocks.
	 *
	 * @var array
	 */
	protected $block_render_methods = array(
		'core/categories' => 'render_categories_block',
		'core/archives'   => 'render_archives_block',
	);

	/**
	 * Register embed.
	 */
	public function register_embed() {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return;
		}

		$registry = WP_Block_Type_Registry::get_instance();
		foreach ( $this->block_render_methods as $block_name => $render_method ) {
			$block = $registry->get_registered( $block_name );
			if ( $block ) {
				$block->original_render_callback = $block->render_callback;
				$block->render_callback          = array( $this, $render_method );
			}
		}
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return;
		}

		$registry = WP_Block_Type_Registry::get_instance();
		foreach ( array_keys( $this->block_render_methods ) as $block_name ) {
			$block = $registry->get_registered( $block_name );
			if ( $block && isset( $block->original_render_callback ) ) {
				$block->render_callback = $block->original_render_callback;
			}
		}
	}

	/**
	 * Render categories block when displayAsDropdown.
	 *
	 * This excludes the disallowed JS scrips, adds <form> tags, and uses on:change for <select>.
	 *
	 * @see render_block_core_categories()
	 *
	 * @param array $attributes Attributes.
	 * @return string Rendered.
	 */
	public function render_categories_block( $attributes ) {
		$block = WP_Block_Type_Registry::get_instance()->get_registered( 'core/categories' );
		if ( ! isset( $block->original_render_callback ) ) {
			return '';
		}

		$rendered = call_user_func( $block->original_render_callback, $attributes );

		static $block_id = 0;
		$block_id++;

		$form_id = "wp-block-categories-dropdown-{$block_id}-form";

		// Remove output of build_dropdown_script_block_core_categories().
		$rendered = preg_replace( '#<script.+?</script>#s', '', $rendered );

		$form = sprintf(
			'<form action="%s" method="get" target="_top" id="%s">',
			esc_url( home_url() ),
			esc_attr( $form_id )
		);

		$rendered = preg_replace(
			'#(<select)(.+</select>)#s',
			$form . '$1' . sprintf( ' on="change:%1$s.submit"', esc_attr( $form_id ) ) . '$2</form>',
			$rendered,
			1
		);

		return $rendered;
	}

	/**
	 * Render archives block when displayAsDropdown.
	 *
	 * This replaces disallowed script with the use of on:change for <select>.
	 *
	 * @see render_block_core_archives()
	 *
	 * @param array $attributes Attributes.
	 * @return string Rendered.
	 */
	public function render_archives_block( $attributes ) {
		$block = WP_Block_Type_Registry::get_instance()->get_registered( 'core/archives' );
		if ( ! isset( $block->original_render_callback ) ) {
			return '';
		}

		$rendered = call_user_func( $block->original_render_callback, $attributes );

		// Eliminate use of uniqid(). Core should be using wp_unique_id() here.
		static $block_id = 0;
		$block_id++;
		$rendered = preg_replace( '/(?<="wp-block-archives-)\w+(?=")/', $block_id, $rendered );

		// Replace onchange with on attribute.
		$rendered = preg_replace(
			'/onchange=".+?"/',
			'on="change:AMP.navigateTo(url=event.value)"',
			$rendered
		);

		return $rendered;
	}

}
