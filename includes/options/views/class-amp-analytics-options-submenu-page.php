<?php

require_once( AMP__DIR__ . '/includes/options/views/class-amp-analytics-options-serializer.php' );


class AMP_Analytics_Options_Submenu_Page {

	private function render_option($id = "", $type = "", $config = "") {
			?>
			<div class="analytics-data-container">
				<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
					<h2>Analytics Component: <?php echo ($type ? $type . ':' : '') . substr($id, -6) ?></h2>
					<div class="options">
						<p>
							<label>Type: </label>
							<input class="option-input" type="text" name=vendor-type value="<?php echo $type; ?>" />
							<label>Id: </label>
							<input type="text" name=id value="<?php echo substr($id, -6); ?>" text="alberto" readonly />
                            <input type="hidden" name=id-value value="<?php echo $id; ?>" />
						</p>
						<p>
							<label>JSON Configuration:</label>
							<br />
							<textarea rows="10" cols="100" name="config"><?php echo stripslashes($config); ?></textarea>
						</p>
						<input type="hidden" name="action" value="analytics_options">
					</div><!-- #analytics-data-container -->
				<p>
			<?php
				submit_button('Save', 'primary', 'save', false);
				submit_button('Delete', 'delete button-primary', 'delete', false);
				?>
				</p>
				</form>
			</div><!-- .wrap -->
			<?php
	}

	public function render() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php

		$analytics_options = get_option('analytics');

		if ( $analytics_options ) {
			foreach ( $analytics_options as $option ) {
				$this->render_option( $option[0], $option[1], $option[2] );
			}
		}
		$this->render_option();
	}
}