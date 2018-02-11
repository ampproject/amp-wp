<?php
/**
 * Class AMP_Analytics_Options_Submenu_Page
 *
 * @package AMP
 */

/**
 * Class AMP_Analytics_Options_Submenu_Page
 */
class AMP_Analytics_Options_Submenu_Page {

	/**
	 * Render entry.
	 *
	 * @param string $id     Entry ID.
	 * @param string $type   Entry type.
	 * @param string $config Entry config as serialized JSON.
	 */
	private function render_entry( $id = '', $type = '', $config = '' ) {
		$is_existing_entry = ! empty( $id );

		if ( $is_existing_entry ) {
			$entry_slug = sprintf( '%s%s', ( $type ? $type . '-' : '' ), substr( $id, - 6 ) );
			/* translators: %s is the entry slug */
			$analytics_title = sprintf( __( 'Analytics: %s', 'amp' ), $entry_slug );
		} else {
			$analytics_title = __( 'Add new entry:', 'amp' );
			$id              = '__new__';
		}

		$id_base = sprintf( '%s[analytics][%s]', AMP_Options_Manager::OPTION_NAME, $id );
		?>
		<div class="analytics-data-container">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<h2>
					<?php echo esc_html( $analytics_title ); ?>
				</h2>
				<div class="options">
					<p>
						<label>
							<?php esc_html_e( 'Type:', 'amp' ); ?>
							<input class="option-input" type="text" name="<?php echo esc_attr( $id_base . '[type]' ); ?>" value="<?php echo esc_attr( $type ); ?>" />
						</label>
						<label>
							<?php esc_html_e( 'ID:', 'amp' ); ?>
							<input type="text" value="<?php echo esc_attr( $is_existing_entry ? $id : '' ); ?>" readonly />
						</label>
							<input type="hidden" name="<?php echo esc_attr( $id_base . '[id]' ); ?>" value="<?php echo esc_attr( $id ); ?>" />
					</p>
					<p>
						<label>
							<?php esc_html_e( 'JSON Configuration:', 'amp' ); ?>
							<br />
							<textarea rows="10" cols="100" name="<?php echo esc_attr( $id_base . '[config]' ); ?>"><?php echo esc_textarea( $config ); ?></textarea>
						</label>
					</p>
					<input type="hidden" name="action" value="amp_analytics_options">
				</div>
				<p>
					<?php
					wp_nonce_field( 'analytics-options', 'analytics-options' );
					submit_button( esc_html__( 'Save', 'amp' ), 'primary', 'save', false );
					if ( $is_existing_entry ) {
						submit_button( esc_html__( 'Delete', 'amp' ), 'delete button-primary', esc_attr( $id_base . '[delete]' ), false );
					}
					?>
				</p>
			</form>
		</div><!-- #analytics-data-container -->
		<?php
	}

	/**
	 * Render title.
	 */
	public function render_title() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Render.
	 */
	public function render() {
		$analytics_entries = AMP_Options_Manager::get_option( 'analytics', array() );

		$this->render_title();

		// Render entries stored in the DB.
		foreach ( $analytics_entries as $entry_id => $entry ) {
			$this->render_entry( $entry_id, $entry['type'], $entry['config'] );
		}

		// Empty form for adding more entries.
		$this->render_entry();
	}
}
