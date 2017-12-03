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
		<input id="amp-satus-enabled" type="radio" name="<?php echo esc_attr( self::POST_META_KEY ); ?>" value="enabled"<?php checked( 'enabled', $status ); ?>>
		<label for="amp-satus-enabled" class="selectit"><?php echo esc_html( $labels['enabled'] ); ?></label>
		<br />
		<input id="amp-satus-disabled" type="radio" name="<?php echo esc_attr( self::POST_META_KEY ); ?>" value="disabled"<?php checked( 'disabled', $status ); ?>>
		<label for="amp-satus-disabled" class="selectit"><?php echo esc_html( $labels['disabled'] ); ?></label>
		<br />
		<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
		<div class="amp-status-actions">
			<a href="#amp_status" class="save-amp-status hide-if-no-js button"><?php esc_html_e( 'OK', 'amp' ); ?></a>
			<a href="#amp_status" class="cancel-amp-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'amp' ); ?></a>
		</div>
	</div>
</div>
