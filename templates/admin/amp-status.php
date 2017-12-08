<?php
/**
 * AMP status option in the submit meta box.
 *
 * @package AMP
 */

// Check referrer.
if ( ! ( $this instanceof AMP_Post_Meta_Box ) ) {
	return;
}
?>
<div class="misc-pub-section misc-amp-status">
	<span class="amp-icon"></span>
	<?php esc_html_e( 'AMP:', 'amp' ); ?>
	<strong class="amp-status-text"><?php echo esc_html( $labels[ $status ] ); ?></strong>
	<a href="#amp_status" class="edit-amp-status hide-if-no-js" role="button">
		<span aria-hidden="true"><?php esc_html_e( 'Edit', 'amp' ); ?></span>
		<span class="screen-reader-text"><?php esc_html_e( 'Edit Status', 'amp' ); ?></span>
	</a>
	<div id="amp-status-select" class="hide-if-js" data-amp-status="<?php echo esc_attr( $status ); ?>">
		<?php if ( $available ) : ?>
			<fieldset>
				<input id="amp-status-enabled" type="radio" name="<?php echo esc_attr( self::STATUS_INPUT_NAME ); ?>" value="enabled" <?php checked( ! $disabled ); ?>>
				<label for="amp-status-enabled" class="selectit"><?php echo esc_html( $labels['enabled'] ); ?></label>
				<br />
				<input id="amp-status-disabled" type="radio" name="<?php echo esc_attr( self::STATUS_INPUT_NAME ); ?>" value="disabled" <?php checked( $disabled ); ?>>
				<label for="amp-status-disabled" class="selectit"><?php echo esc_html( $labels['disabled'] ); ?></label>
				<br />
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
			</fieldset>
		<?php else : ?>
			<div class="inline notice notice-warning">
				<p><?php esc_html_e( 'AMP cannot be enabled on home page, front page, password protected posts and post types which do not support AMP.', 'amp' ); ?></p>
			</div>
		<?php endif; ?>
		<div class="amp-status-actions">
			<?php if ( $available ) : ?>
				<a href="#amp_status" class="save-amp-status hide-if-no-js button"><?php esc_html_e( 'OK', 'amp' ); ?></a>
			<?php endif; ?>
			<a href="#amp_status" class="cancel-amp-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'amp' ); ?></a>
		</div>
	</div>
</div>
