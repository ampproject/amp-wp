<?php
/**
 * Settings template.
 *
 * @package AMP
 */

// Check referrer.
if ( ! $this instanceof AMP_Settings ) {
	return;
}
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php settings_errors(); ?>
	<form action="options.php" method="post">
		<?php
		settings_fields( AMP_Settings::SETTINGS_KEY );
		do_settings_sections( AMP_Settings::MENU_SLUG );
		submit_button();
		?>
	</form>
</div>
