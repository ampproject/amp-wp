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

/**
 * Inherited template vars.
 *
 * @var array  $labels         Labels for enabled or disabled.
 * @var string $status         Enabled or disabled.
 * @var array  $errors         Support errors.
 * @var array  $error_messages AMP enabled error messages.
 */
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
		<?php if ( empty( $errors ) ) : ?>
			<fieldset>
				<input id="amp-status-enabled" type="radio" name="<?php echo esc_attr( AMP_Post_Meta_Box::STATUS_INPUT_NAME ); ?>" value="<?php echo esc_attr( AMP_Post_Meta_Box::ENABLED_STATUS ); ?>" <?php checked( AMP_Post_Meta_Box::ENABLED_STATUS, $status ); ?>>
				<label for="amp-status-enabled" class="selectit"><?php echo esc_html( $labels['enabled'] ); ?></label>
				<br />
				<input id="amp-status-disabled" type="radio" name="<?php echo esc_attr( AMP_Post_Meta_Box::STATUS_INPUT_NAME ); ?>" value="<?php echo esc_attr( AMP_Post_Meta_Box::DISABLED_STATUS ); ?>" <?php checked( AMP_Post_Meta_Box::DISABLED_STATUS, $status ); ?>>
				<label for="amp-status-disabled" class="selectit"><?php echo esc_html( $labels['disabled'] ); ?></label>
				<br />
				<?php wp_nonce_field( AMP_Post_Meta_Box::NONCE_ACTION, AMP_Post_Meta_Box::NONCE_NAME ); ?>
			</fieldset>
		<?php else : ?>
			<div class="inline notice notice-info notice-alt">
				<p>
					<strong><?php esc_html_e( 'AMP Unavailable', 'amp' ); ?></strong>
				</p>
				<?php foreach ( $error_messages as $error_message ) : ?>
					<p>
						<?php echo $error_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="amp-status-actions">
			<?php if ( empty( $errors ) ) : ?>
				<a href="#amp_status" class="save-amp-status hide-if-no-js button"><?php esc_html_e( 'OK', 'amp' ); ?></a>
			<?php endif; ?>
			<a href="#amp_status" class="cancel-amp-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'amp' ); ?></a>
		</div>
	</div>
</div>
