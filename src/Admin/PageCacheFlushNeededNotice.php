<?php
/**
 * Class PageCacheFlushNeededNotice.
 *
 * Adds an admin notice for admin user to flush page cache.
 *
 * @since   2.2
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Class PageCacheFlushNeededNotice
 *
 * @since 2.2
 * @internal
 */
final class PageCacheFlushNeededNotice implements Service, Registerable {

	/**
	 * The ID of the plugin activation notice.
	 *
	 * @var string
	 */
	const NOTICE_ID = 'amp-page-cache-flush-needed';

	/**
	 * The option key for plugin's admin notices.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'amp_admin_notices';

	/**
	 * The ajax action.
	 *
	 * @var string
	 */
	const AJAX_ACTION = 'dismiss-amp-notice';

	/**
	 * Runs on instantiation.
	 */
	public function register() {

		add_action( 'amp_page_cache_flush_needed', [ $this, 'trigger_admin_notice' ] );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, [ $this, 'ajax_dismiss_amp_notice' ] );
		add_action( 'admin_notices', [ $this, 'render_notice' ] );
	}

	/**
	 * Trigger admin notice for page cache flush.
	 *
	 * @return void
	 */
	public function trigger_admin_notice() {

		$notices   = get_option( self::OPTION_NAME, [] );
		$notices[] = self::NOTICE_ID;

		update_option( self::OPTION_NAME, array_unique( $notices ) );
	}

	/**
	 * Dismiss plugin's admin notice.
	 *
	 * @return void
	 */
	public function ajax_dismiss_amp_notice() {

		check_ajax_referer( self::AJAX_ACTION, 'nonce' );

		$notice = isset( $_POST['notice'] ) ? sanitize_key( $_POST['notice'] ) : '';

		if ( empty( $notice ) ) {
			wp_die( 0 );
			return;
		}

		$notices = get_option( self::OPTION_NAME, [] );

		if ( ! in_array( $notice, $notices, true ) ) {
			wp_die( 0 );
			return;
		}

		$notices = array_diff( $notices, [ $notice ] );

		update_option( self::OPTION_NAME, $notices );
		wp_die( 1 );
	}

	/**
	 * Renders a notice on setting page.
	 *
	 * @return void
	 */
	public function render_notice() {

		$notices = get_option( self::OPTION_NAME, [] );

		if ( ! in_array( self::NOTICE_ID, $notices, true ) ) {
			return;
		}

		// Do not render notice on AMP Setting page. Since it will be render by JS.
		$current_screen = get_current_screen();
		if ( ! empty( $current_screen ) && 'toplevel_page_amp-options' === $current_screen->base ) {
			return;
		}

		$nonce = wp_create_nonce( self::AJAX_ACTION );

		?>
		<div class="amp-plugin-notice notice notice-error is-dismissible" id="<?php echo esc_attr( self::NOTICE_ID ); ?>">
			<div class="notice-dismiss"></div>
			<div>
				<p><?php esc_html_e( 'Please flush page cache.', 'amp' ); ?></p>
			</div>
		</div>
		<script>
			jQuery( function ( $ ) {
				var element = $( <?php echo wp_json_encode( '#' . self::NOTICE_ID ); ?> );
				// On dismissing the notice, make a POST request to store this notice with the dismissed WP pointers so it doesn't display again.
				element.on( 'click', '.notice-dismiss', function () {
					$.post( ajaxurl, {
						notice: <?php echo wp_json_encode( self::NOTICE_ID ); ?>,
						action: <?php echo wp_json_encode( self::AJAX_ACTION ); ?>,
						nonce: <?php echo wp_json_encode( $nonce ); ?>,
					} ).done( function ( response ) {
						if ( '1' === response ) {
							element.remove();
						}
					} );
				} );
			} );
		</script>
		<?php
	}
}
