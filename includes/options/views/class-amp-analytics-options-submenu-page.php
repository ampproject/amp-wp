<?php

require_once( AMP__DIR__ . '/includes/options/views/class-amp-analytics-options-serializer.php' );

class AMP_Analytics_Options_Submenu_Page {

	private function render_option($id = "", $type = "", $config = "") {
			?>
			<div class="analytics-data-container">
				<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
					<h2><?php echo __( 'Analytics Component', 'amp' ) ?>: <?php echo ($type ? $type . ':' : '') . substr($id, -6) ?></h2>
					<div class="options">
						<p>
							<label><?php echo __( 'Type', 'amp' ) ?>: </label>
							<input class="option-input" type="text" name=vendor-type value="<?php echo esc_attr( $type ); ?>" />
							<label><?php echo __( 'Id', 'amp' ) ?>: </label>
							<input type="text" name=id value="<?php echo substr(esc_attr( $id ), -6); ?>" readonly />
                            <input type="hidden" name=id-value value="<?php echo $id; ?>" />
						</p>
						<p>
							<label><?php echo __( 'JSON Configuration', 'amp' ) ?>:</label>
							<br />
							<textarea rows="10" cols="100" name="config"><?php echo esc_textarea( $config ); ?></textarea>
						</p>
						<input type="hidden" name="action" value="analytics_options">
					</div><!-- #analytics-data-container -->
				<p>
			<?php
			    wp_nonce_field( 'analytics-options', 'analytics-options');
				submit_button('Save', 'primary', 'save', false);
				submit_button('Delete', 'delete button-primary', 'delete', false);
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
		global $_GET;
		if ( isset( $_GET['valid'] ) ) {
            if ( ! esc_attr( $_GET['valid'] ) ) {
                ?>
                <h3 style='color:red'><?php echo 'Action not taken: invalid input!'; ?></h3>
                <?php
            } else {
                ?>
                <h3 style='color:green'><?php echo 'Option(s) saved!'; ?></h3>
                <?php
            }
            unset( $_GET['valid'] );
		}
	}

	public function render() {

	    $this->render_title();

		$amp_options = get_option('amp-options');
		if ( $amp_options ) {
			$analytics_options = $amp_options[ 'amp-analytics'];
		}
		if ( $analytics_options ) {
			foreach ( $analytics_options as $option ) {
			    list( $id, $type, $config ) = $option;
				$this->render_option( $id, $type, $config );
			}
		}
		$this->render_option();
	}
}
