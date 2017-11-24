<?php
/**
 * Settings post types checkbox template.
 *
 * @package AMP
 */

// Check referrer.
if ( ! $this instanceof AMP_Settings_Post_Types ) {
	return;
}
?>
<fieldset>
	<?php foreach ( $this->get_supported_post_types() as $post_type ) : ?>
		<label>
			<input type="checkbox" value="1" name="<?php echo esc_attr( $this->get_name_attribute( $post_type->name ) ); ?>"<?php checked( true, post_type_supports( $post_type->name, AMP_QUERY_VAR ) ); ?><?php disabled( true, $this->disabled( $post_type->name ) ); ?>> <?php echo esc_html( $post_type->label ); ?>
		</label>
		<br>
	<?php endforeach; ?>
	<p class="description"><?php echo esc_html( $this->setting['description'] ); ?></p>
</fieldset>
