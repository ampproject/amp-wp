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
					<?php
					echo esc_html( $analytics_title );
					?>
				</h2>
				<div class="options">
					<p>
						<label><?php echo __( 'Type:', 'amp' ) ?></label>
						<input class="option-input" type="text" name=vendor-type value="<?php echo esc_attr( $type ); ?>" />
						<label><?php echo __( 'ID:', 'amp' ) ?></label>
						<input type="text" name=id value="<?php echo esc_attr( substr( $id, -6 ) ); ?>" readonly />
						<input type="hidden" name=id-value value="<?php echo esc_attr( $id ); ?>" />
					</p>
					<p>
						<label><?php echo __( 'JSON Configuration:', 'amp' ) ?></label>
						<br />
						<textarea rows="10" cols="100" name="config"><?php echo esc_textarea( $config ); ?></textarea>
					</p>
					<input type="hidden" name="action" value="amp_analytics_options">
				</div><!-- #analytics-data-container -->
			<p>
				<?php
				wp_nonce_field( 'analytics-options', 'analytics-options' );
				submit_button( 'Save', 'primary', 'save', false );
				if ( $is_existing_entry ) {
					submit_button( 'Delete', 'delete button-primary', 'delete', false );
				}
				?>
			</p>
			</form>
		</div><!-- .wrap -->
		<?php
	}

	public function render_title() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php

			// If redirected from serializer, check if action succeeded
			if ( isset( $_GET['valid'] ) ) : ?>
				<div class="amp-analytics-options notice notice-error is-dismissible">
					<p><strong>Action not taken: invalid input!</strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">Dismiss this notice.</span>
					</button>
				</div>
			<?php else : ?>
				<div class="amp-analytics-options notice notice-success is-dismissible">
					<p><strong>Analytics options saved!</strong></p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">Dismiss this notice.</span>
					</button>
				</div>
			<?php endif;
	}

	public function render() {
		$analytics_entries = AMP_Options_Manager::get_option( 'amp-analytics', array() );

		$this->render_title();

		foreach ( $analytics_entries as $entries ) {
			list( $id, $type, $config ) = $entries;
			$this->render_entry( $id, $type, $config );
		}

		$this->render_entry();
	}
}
