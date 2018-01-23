<?php
/**
 * Class AMP_Widget_Categories
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_Categories
 *
 * @package AMP
 */
class AMP_Widget_Categories extends WP_Widget_Categories {

	/**
	 * Adds an 'on' attribute to the category dropdown's <select>.
	 *
	 * @param string $dropdown The markup of the category dropdown.
	 * @return string $dropdown The filtered markup of the dropdown.
	 */
	public function modify_select( $dropdown ) {
		$new_select = sprintf( '<select on="change:widget-categories-dropdown-%d.submit"', esc_attr( $this->number ) );
		return str_replace( '<select', $new_select, $dropdown );
	}

	/**
	 * Echoes the markup of the widget.
	 *
	 * Mainly copied from WP_Widget_Categories::widget()
	 * There's now an id for the <form>.
	 * And the dropdown is now filtered with 'wp_dropdown_cats.'
	 * This enables adding an 'on' attribute, with the id of the form.
	 * So changing the dropdown value will redirect to the category page, with valid AMP.
	 *
	 * @param array $args Widget display data.
	 * @param array $instance Data for widget.
	 * @return void.
	 */
	public function widget( $args, $instance ) {
		if ( ! is_amp_endpoint() ) {
			parent::widget( $args, $instance );
			return;
		}

		ob_start();
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
		$cat_args = array(
			'orderby'      => 'name',
			'show_count'   => $c,
			'hierarchical' => $h,
		);
		if ( $d ) :
			echo sprintf( '<form action="%s" method="get" id="widget-categories-dropdown-%d">', esc_url( home_url() ), esc_attr( $this->number ) );
			add_filter( 'wp_dropdown_cats', array( $this, 'modify_select' ) );
			$dropdown_id    = ( $first_dropdown ) ? 'cat' : "{$this->id_base}-dropdown-{$this->number}";
			$first_dropdown = false;
			echo '<label class="screen-reader-text" for="' . esc_attr( $dropdown_id ) . '">' . esc_html( $title ) . '</label>';
			$cat_args['show_option_none'] = __( 'Select Category', 'default' );
			$cat_args['id']               = $dropdown_id;

			/** This filter is documented in wp-includes/widgets/class-wp-widget-categories.php */
			wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args', $cat_args, $instance ) );
			remove_filter( 'wp_dropdown_cats', array( $this, 'modify_select' ) );
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
		$output = ob_get_clean();
		echo AMP_Theme_Support::filter_the_content( $output ); // WPCS: XSS ok.
	}

}
