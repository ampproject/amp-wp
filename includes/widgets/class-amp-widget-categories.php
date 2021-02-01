<?php
/**
 * Class AMP_Widget_Categories
 *
 * @since 0.7.0
 * @package AMP
 * @codeCoverageIgnore
 */

_deprecated_file( __FILE__, '2.0.0' );

/**
 * Class AMP_Widget_Categories
 *
 * @deprecated As of 2.0 the AMP_Core_Block_Handler will sanitize the core widgets instead.
 * @internal
 * @since 0.7.0
 * @package AMP
 */
class AMP_Widget_Categories extends WP_Widget_Categories {

	/**
	 * Echoes the markup of the widget.
	 *
	 * Mainly copied from WP_Widget_Categories::widget()
	 * There's now an id for the <form>.
	 * And the dropdown is now filtered with 'wp_dropdown_cats.'
	 * This enables adding an 'on' attribute, with the id of the form.
	 * So changing the dropdown value will redirect to the category page, with valid AMP.
	 *
	 * @since 0.7.0
	 *
	 * @param array $args Widget display data.
	 * @param array $instance Data for widget.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		if ( ! amp_is_request() ) {
			parent::widget( $args, $instance );
			return;
		}

		static $first_dropdown = true;
		$title                 = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Categories', 'default' );
		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$c     = ! empty( $instance['count'] ) ? '1' : '0';
		$h     = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d     = ! empty( $instance['dropdown'] ) ? '1' : '0';
		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}
		$cat_args = [
			'orderby'      => 'name',
			'show_count'   => $c,
			'hierarchical' => $h,
		];
		if ( $d ) :
			$form_id = sprintf( 'widget-categories-dropdown-%d', $this->number );
			printf( '<form action="%s" method="get" target="_top" id="%s">', esc_url( home_url() ), esc_attr( $form_id ) );
			$dropdown_id    = $first_dropdown ? 'cat' : "{$this->id_base}-dropdown-{$this->number}";
			$first_dropdown = false;
			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . esc_html( $title ) . '</label>';
			$cat_args['show_option_none'] = __( 'Select Category', 'default' );
			$cat_args['id']               = $dropdown_id;

			$dropdown = wp_dropdown_categories(
				array_merge(
					/** This filter is documented in wp-includes/widgets/class-wp-widget-categories.php */
					apply_filters( 'widget_categories_dropdown_args', $cat_args, $instance ),
					[ 'echo' => false ]
				)
			);
			$dropdown = preg_replace(
				'/(?<=<select\b)/',
				sprintf( '<select on="change:%s.submit"', esc_attr( $form_id ) ),
				$dropdown,
				1
			);
			echo $dropdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</form>';
		else :
			?>
			<ul>
			<?php
			$cat_args['title_li'] = '';

			/** This filter is documented in wp-includes/widgets/class-wp-widget-categories.php */
			wp_list_categories( apply_filters( 'widget_categories_args', $cat_args, $instance ) );
			?>
			</ul>
			<?php
		endif;
		echo wp_kses_post( $args['after_widget'] );
	}
}
