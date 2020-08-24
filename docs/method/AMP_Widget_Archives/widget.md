## Method `AMP_Widget_Archives::widget()`

```php
public function widget( $args, $instance );
```

Echoes the markup of the widget.

Mainly copied from WP_Widget_Archives::widget() Changes include: An id for the &lt;form&gt;. More escaping. The dropdown is now filtered with &#039;wp_dropdown_cats.&#039; This enables adding an &#039;on&#039; attribute, with the id of the form. So changing the dropdown value will redirect to the category page, with valid AMP.

### Arguments

* `array $args` - Widget display data.
* `array $instance` - Data for widget.

### Source

:link: [includes/widgets/class-amp-widget-archives.php:38](../../includes/widgets/class-amp-widget-archives.php#L38-L113)

<details>
<summary>Show Code</summary>

```php
public function widget( $args, $instance ) {
	if ( ! amp_is_request() ) {
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
```

</details>
