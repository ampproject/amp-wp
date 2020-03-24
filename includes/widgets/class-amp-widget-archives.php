<?php
/**
 * Class AMP_Widget_Archives
 *
 * @since 0.7.0
 * @package AMP
 */

/**
 * Class AMP_Widget_Archives
 *
 * @since 0.7.0
 * @package AMP
 */
class AMP_Widget_Archives extends WP_Widget_Archives {

	/**
	 * Echoes the markup of the widget.
	 *
	 * Mainly copied from WP_Widget_Archives::widget()
	 * Changes include:
	 * An id for the <form>.
	 * More escaping.
	 * The dropdown is now filtered with 'wp_dropdown_cats.'
	 * This enables adding an 'on' attribute, with the id of the form.
	 * So changing the dropdown value will redirect to the category page, with valid AMP.
	 *
	 * @since 0.7.0
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

		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Archives', 'default' ) : $instance['title'], $instance, $this->id_base );
		echo wp_kses_post( $args['before_widget'] );
		if ( $title ) :
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		endif;

		if ( $d ) :
			$dropdown_id = "{$this->id_base}-dropdown-{$this->number}";
			?>
			<form action="<?php echo esc_url( home_url() ); ?>" method="get" target="_top">
				<label class="screen-reader-text" for="<?php echo esc_attr( $dropdown_id ); ?>"><?php echo esc_html( $title ); ?></label>
				<select id="<?php echo esc_attr( $dropdown_id ); ?>" name="archive-dropdown" on="change:AMP.navigateTo(url=event.value)">
					<?php

					/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
					$dropdown_args = apply_filters(
						'widget_archives_dropdown_args',
						[
							'type'            => 'monthly',
							'format'          => 'option',
							'show_post_count' => $c,
						]
					);

					switch ( $dropdown_args['type'] ) {
						case 'yearly':
							$label = __( 'Select Year', 'default' );
							break;
						case 'monthly':
							$label = __( 'Select Month', 'default' );
							break;
						case 'daily':
							$label = __( 'Select Day', 'default' );
							break;
						case 'weekly':
							$label = __( 'Select Week', 'default' );
							break;
						default:
							$label = __( 'Select Post', 'default' );
							break;
					}
					?>
					<option value=""><?php echo esc_attr( $label ); ?></option>
					<?php wp_get_archives( $dropdown_args ); ?>
				</select>
			</form>
		<?php else : ?>
			<ul>
				<?php

				/** This filter is documented in wp-includes/widgets/class-wp-widget-archives.php */
				wp_get_archives(
					apply_filters(
						'widget_archives_args',
						[
							'type'            => 'monthly',
							'show_post_count' => $c,
						]
					)
				);
				?>
			</ul>
			<?php
		endif;
		echo wp_kses_post( $args['after_widget'] );
	}
}
