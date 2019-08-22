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
	private function render_entry( $id = '', $type = '', $config = '{}' ) {
		$is_existing_entry = ! empty( $id );

		if ( $is_existing_entry ) {
			$entry_slug = sprintf( '%s%s', ( $type ? $type . '-' : '' ), substr( $id, - 6 ) );
			/* translators: %s: the entry slug. */
			$analytics_title = sprintf( __( 'Analytics: %s', 'amp' ), $entry_slug );
		} else {
			$analytics_title = __( 'Add new entry:', 'amp' );
			$id              = '__new__';
		}

		// Tidy-up the JSON for display.
		if ( $config ) {
			$options = ( 128 /* JSON_PRETTY_PRINT */ | 64 /* JSON_UNESCAPED_SLASHES */ );
			$config  = wp_json_encode( json_decode( $config ), $options );
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
							<input class="option-input" type="text" required name="<?php echo esc_attr( $id_base . '[type]' ); ?>" placeholder="<?php esc_attr_e( 'e.g. googleanalytics', 'amp' ); ?>" value="<?php echo esc_attr( $type ); ?>" />
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
							<textarea
								rows="10"
								cols="100"
								name="<?php echo esc_attr( $id_base . '[config]' ); ?>"
								class="amp-analytics-input"
								placeholder="{...}"
								required
								><?php echo esc_textarea( $config ); ?></textarea>
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
	 *
	 * @param bool $has_entries Whether there are entries.
	 */
	public function render_title( $has_entries = false ) {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>

			<details <?php echo ! $has_entries ? 'open' : ''; ?>>
				<summary>
					<?php esc_html_e( 'Learn about analytics for AMP.', 'amp' ); ?>
				</summary>
				<p>
					<?php
						echo wp_kses_post(
							sprintf(
								/* translators: 1: AMP Analytics docs URL. 2: AMP for WordPress analytics docs URL. 3: AMP analytics code reference. 4: amp-analytics, 5: {. 6: }. 7: <script>, 8: googleanalytics. 9: AMP analytics vendor docs URL. 10: UA-XXXXX-Y. */
								__( 'For Google Analytics, please see <a href="%1$s" target="_blank">Adding Analytics to your AMP pages</a>; see also the <a href="%2$s" target="_blank">Analytics wiki page</a> and the AMP project\'s <a href="%3$s" target="_blank">%4$s documentation</a>. The analytics configuration supplied below must take the form of JSON objects, which begin with a %5$s and end with a %6$s. Do not include any HTML tags like %4$s or %7$s. A common entry would have the type %8$s (see <a href="%9$s" target="_blank">available vendors</a>) and a configuration that looks like the following (where %10$s is replaced with your own site\'s account number):', 'amp' ),
								__( 'https://developers.google.com/analytics/devguides/collection/amp-analytics/', 'amp' ),
								__( 'https://amp-wp.org/documentation/playbooks/analytics/', 'amp' ),
								__( 'https://www.ampproject.org/docs/reference/components/amp-analytics', 'amp' ),
								'<code>amp-analytics</code>',
								'<code>{</code>',
								'<code>}</code>',
								'<code>&lt;script&gt;</code>',
								'<code>googleanalytics</code>',
								__( 'https://www.ampproject.org/docs/analytics/analytics-vendors', 'amp' ),
								'<code>UA-XXXXX-Y</code>'
							)
						);
					?>

					<pre>{
	"vars": {
		"account": "UA-XXXXX-Y"
	},
	"triggers": {
		"trackPageview": {
			"on": "visible",
			"request": "pageview"
		}
	}
}</pre>
				</p>
			</details>
		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Render styles.
	 */
	protected function render_styles() {
		?>
		<style>
			.amp-analytics-input {
				font-family: monospace;
			}
			.amp-analytics-input:invalid {
				border-color: red;
			}
		</style>
		<?php
	}

	/**
	 * Render scripts.
	 */
	protected function render_scripts() {
		?>
		<script>
			Array.prototype.forEach.call( document.querySelectorAll( '.amp-analytics-input' ), function( textarea ) {
				textarea.addEventListener( 'input', function() {
					if ( ! this.value ) {
						this.setCustomValidity( '' );
						return;
					}
					try {
						var value = JSON.parse( this.value );
						if ( null === value || typeof value !== 'object' || Array.isArray( value ) ) {
							this.setCustomValidity( <?php echo wp_json_encode( __( 'A JSON object is required, e.g. {...}', 'amp' ) ); ?> )
						} else {
							this.setCustomValidity( '' );
						}
					} catch ( e ) {
						this.setCustomValidity( e.message )
					}
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Render.
	 */
	public function render() {
		$this->render_styles();

		$analytics_entries = AMP_Options_Manager::get_option( 'analytics', [] );

		$this->render_title( ! empty( $analytics_entries ) );

		// Render entries stored in the DB.
		foreach ( $analytics_entries as $entry_id => $entry ) {
			$this->render_entry( $entry_id, $entry['type'], $entry['config'] );
		}

		// Empty form for adding more entries.
		$this->render_entry();

		$this->render_scripts();
	}
}
