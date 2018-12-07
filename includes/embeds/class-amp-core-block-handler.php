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
	 * Original block callback.
	 *
	 * @var array
	 */
	public $original_categories_callback;

	/**
	 * Block name.
	 *
	 * @var string
	 */
	public $block_name = 'core/categories';

	/**
	 * Register embed.
	 */
	public function register_embed() {
		if ( class_exists( 'WP_Block_Type_Registry' ) ) {
			$registry = WP_Block_Type_Registry::get_instance();
			$block    = $registry->get_registered( $this->block_name );

			if ( $block ) {
				$this->original_categories_callback = $block->render_callback;
				$block->render_callback             = array( $this, 'render' );
			}
		}
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		if ( class_exists( 'WP_Block_Type_Registry' ) ) {
			$registry = WP_Block_Type_Registry::get_instance();
			$block    = $registry->get_registered( $this->block_name );

			if ( $block && ! empty( $this->original_categories_callback ) ) {
				$block->render_callback             = $this->original_categories_callback;
				$this->original_categories_callback = null;
			}
		}
	}

	/**
	 * Render Gutenberg block. This is essentially the same method as the original.
	 * Difference is excluding the disallowed JS script, adding <form> tags, and using on:change for <select>.
	 *
	 * @param array $attributes Attributes.
	 * @return string Rendered.
	 */
	public function render( $attributes ) {
		static $block_id = 0;
		$block_id++;

		$align = 'center';
		if ( isset( $attributes['align'] ) && in_array( $attributes['align'], array( 'left', 'right', 'full' ), true ) ) {
			$align = $attributes['align'];
		}

		$args = array(
			'echo'         => false,
			'hierarchical' => ! empty( $attributes['showHierarchy'] ),
			'orderby'      => 'name',
			'show_count'   => ! empty( $attributes['showPostCounts'] ),
			'title_li'     => '',
		);

		if ( ! empty( $attributes['displayAsDropdown'] ) ) {
			$id                       = 'wp-block-categories-dropdown-' . $block_id;
			$form_id                  = $id . '-form';
			$args['id']               = $id;
			$args['show_option_none'] = __( 'Select Category', 'amp' );
			$wrapper_markup           = '<div class="%1$s">%2$s</div>';
			$items_markup             = wp_dropdown_categories( $args );
			$type                     = 'dropdown';

			$items_markup = preg_replace(
				'/(?<=<select\b)/',
				sprintf( ' on="change:%s.submit"', esc_attr( $form_id ) ),
				$items_markup,
				1
			);
		} else {
			$wrapper_markup = '<div class="%1$s"><ul>%2$s</ul></div>';
			$items_markup   = wp_list_categories( $args );
			$type           = 'list';
		}

		$class = "wp-block-categories wp-block-categories-{$type} align{$align}";

		$block_content = sprintf(
			$wrapper_markup,
			esc_attr( $class ),
			$items_markup
		);

		if ( ! empty( $attributes['displayAsDropdown'] ) ) {
			$block_content = sprintf( '<form action="%s" method="get" target="_top" id="%s">%s</form>', esc_url( home_url() ), esc_attr( $form_id ), $block_content );
		}
		return $block_content;
	}
}
