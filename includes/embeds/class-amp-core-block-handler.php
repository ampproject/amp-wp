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
		$archives_block = WP_Block_Type_Registry::get_instance()->get_registered( 'core/categories' );
		if ( empty( $attributes['displayAsDropdown'] ) && isset( $archives_block->original_render_callback ) ) {
			return call_user_func( $archives_block->original_render_callback, $attributes );
		}

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

		$class = "wp-block-categories wp-block-categories-{$type} align{$align}";

		$block_content = sprintf(
			$wrapper_markup,
			esc_attr( $class ),
			$items_markup
		);

		$block_content = sprintf(
			'<form action="%s" method="get" target="_top" id="%s">%s</form>',
			esc_url( home_url() ),
			esc_attr( $form_id ),
			$block_content
		);

		return $block_content;
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
		$archives_block = WP_Block_Type_Registry::get_instance()->get_registered( 'core/archives' );
		if ( empty( $attributes['displayAsDropdown'] ) && isset( $archives_block->original_render_callback ) ) {
			return call_user_func( $archives_block->original_render_callback, $attributes );
		}

		$show_post_count = ! empty( $attributes['showPostCounts'] );

		static $block_id = 0;
		$block_id++;

		$class = 'wp-block-archives';

		if ( isset( $attributes['align'] ) ) {
			$class .= " align{$attributes['align']}";
		}

		if ( isset( $attributes['className'] ) ) {
			$class .= " {$attributes['className']}";
		}

		$class .= ' wp-block-archives-dropdown';

		$dropdown_id = 'wp-block-categories-dropdown-' . $block_id;
		$title       = __( 'Archives' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
		$dropdown_args = apply_filters(
			'widget_archives_dropdown_args',
			array(
				'type'            => 'monthly',
				'format'          => 'option',
				'show_post_count' => $show_post_count,
			)
		);

		$dropdown_args['echo'] = 0;

		switch ( $dropdown_args['type'] ) {
			case 'yearly':
				$label = __( 'Select Year' );
				break;
			case 'monthly':
				$label = __( 'Select Month' );
				break;
			case 'daily':
				$label = __( 'Select Day' );
				break;
			case 'weekly':
				$label = __( 'Select Week' );
				break;
			default:
				$label = __( 'Select Post' );
				break;
		}

		$block_content  = sprintf(
			'<label class="screen-reader-text" for="%s">%s</label>',
			esc_attr( $dropdown_id ),
			esc_html( $title )
		);
		$block_content .= sprintf(
			'<select id="%s" on="change:AMP.navigateTo(url=event.value)">',
			esc_attr( $dropdown_id )
		);
		$block_content .= sprintf(
			'<option value="">%s</option>',
			esc_html( $label )
		);
		$block_content .= wp_get_archives( $dropdown_args );
		$block_content .= '</select>';

		$block_content = sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( $class ),
			$block_content
		);

		return $block_content;
	}

}
