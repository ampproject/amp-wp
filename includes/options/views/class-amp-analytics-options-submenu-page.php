<?php

require_once( AMP__DIR__ . '/includes/options/views/class-amp-options-manager.php' );

class AMP_Analytics_Options_Submenu_Page {

	private function render_entry( $id = '', $type = '', $config = '' ) {
		$is_existing_entry = ! empty( $id );

		$analytics_title = false;
		if ( $is_existing_entry ) {
			$entry_slug = sprintf( '%s%s', ( $type ? $type . '-' : '' ), substr( $id, -6 ) );
			$analytics_title = sprintf( __( 'Analytics: %s', 'amp' ), $entry_slug );
		} else {
			$analytics_title = __( 'Add new entry:', 'amp' );
		}
		?>
		<div class="analytics-data-container">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<h2>
					<?php echo esc_html( $analytics_title ); ?>
				</h2>
				<div class="options">
					<p>
						<label>
							<?php echo __( 'Type:', 'amp' ) ?>
							<input class="option-input" type="text" name=vendor-type value="<?php echo esc_attr( $type ); ?>" />
						</label>
						<label>
							<?php echo __( 'ID:', 'amp' ) ?>
							<input type="text" name=id value="<?php echo esc_attr( $id ); ?>" readonly />
						</label>
						<input type="hidden" name=id-value value="<?php echo esc_attr( $id ); ?>" />
					</p>
					<p>
						<label>
							<?php echo __( 'JSON Configuration:', 'amp' ) ?>
							<br />
							<textarea rows="10" cols="100" name="config"><?php echo esc_textarea( $config ); ?></textarea>
						</label>
					</p>
					<input type="hidden" name="action" value="amp_analytics_options">
				</div>
				<p>
					<?php
					wp_nonce_field( 'analytics-options', 'analytics-options' );
					submit_button( __( 'Save', 'amp' ), 'primary', 'save', false );
					if ( $is_existing_entry ) {
						submit_button( __( 'Delete', 'amp' ), 'delete button-primary', 'delete', false );
					}
					?>
				</p>
			</form>
		</div><!-- #analytics-data-container -->
		<?php
	}

	public function render_title() {
		$admin_notice_text = false;
		$admin_notice_type = false;
		if ( isset( $_GET['valid'] ) ) {
			$is_valid = (bool) $_GET['valid'];

			if ( $is_valid ) {
				$admin_notice_text = __( 'The analytics entry was successfully saved!', 'amp' );
				$admin_notice_type = 'success';
			} else {
				$admin_notice_text = __( 'Failed to save the analytics entry. Please make sure that the JSON configuration is valid and unique.', 'amp' );
				$admin_notice_type = 'error';
			}
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			// If redirected from serializer, check if action succeeded
			if ( $admin_notice_text ) : ?>
				<div class="amp-analytics-options notice <?php echo esc_attr( 'notice-' . $admin_notice_type ); ?> is-dismissible">
					<p><?php echo esc_html( $admin_notice_text ); ?></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text"><?php __( 'Dismiss this notice.', 'amp' ) ?></span>
					</button>
				</div>
			<?php endif; ?>
		</div><!-- .wrap -->
		<?php
	}

	public function render() {
		$analytics_entries = AMP_Options_Manager::get_option( 'analytics', array() );

		$this->render_title();

		// Render entries stored in the DB
		foreach ( $analytics_entries as $entry_id => $entry ) {
			$this->render_entry( $entry_id, $entry['type'], $entry['config'] );
		}
		// Empty form for adding more entries
		$this->render_entry();
	}
}
