<?php
/**
 * Class PluginActivationNotice.
 *
 * Adds an admin notice to the plugins screen after the plugin is activated.
 *
 * @since 2.0
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;

/**
 * Class PluginActivationNotice
 *
 * @since 2.0
 * @internal
 */
final class PluginActivationNotice implements Delayed, Service, Registerable {

	/**
	 * The ID of the plugin activation notice.
	 *
	 * @var string
	 */
	const NOTICE_ID = 'amp-plugin-notice-1';

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_init';
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		add_action( 'admin_notices', [ $this, 'render_notice' ] );
	}

	/**
	 * Renders a notice on the plugins screen after the plugin is activated. Persists until it is closed or setup has been completed.
	 */
	public function render_notice() {
		if ( 'plugins' !== get_current_screen()->id ) {
			return;
		}

		if ( AMP_Options_Manager::get_option( Option::PLUGIN_CONFIGURED ) ) {
			return;
		}

		$dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
		if ( in_array( self::NOTICE_ID, explode( ',', (string) $dismissed ), true ) ) {
			return;
		}

		?>
		<div class="amp-plugin-notice notice notice-info is-dismissible" id="<?php echo esc_attr( self::NOTICE_ID ); ?>">
			<div class="notice-dismiss"></div>
			<div class="amp-plugin-notice-icon-holder">
				<svg width="69" height="69" viewBox="0 0 69 69" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M34.4424 68.875C53.2201 68.875 68.4424 53.6527 68.4424 34.875C68.4424 16.0973 53.2201 0.875 34.4424 0.875C15.6647 0.875 0.442383 16.0973 0.442383 34.875C0.442383 53.6527 15.6647 68.875 34.4424 68.875Z" fill="#0479C2"/>
					<path d="M36.9847 29.7355H45.2206C45.2206 29.7355 46.9573 29.7355 46.0621 31.7049L31.8641 55.3384H29.2322L31.7388 39.8871L23.3775 39.8334C23.3775 39.8334 21.8915 39.2426 23.0195 37.3268L36.9847 14.2305H39.724L36.9847 29.7355Z" fill="white"/>
				</svg>
			</div>
			<div>
				<h2><?php esc_html_e( 'Welcome to AMP for WordPress', 'amp' ); ?></h2>
				<p><?php esc_html_e( 'Bring the speed and capabilities of the AMP web framework to your site; support content authoring and website development with the effective tools the AMP plugin provides.', 'amp' ); ?></p>

				<?php if ( amp_should_use_new_onboarding() ) : ?>
					<p><a href="<?php menu_page_url( OnboardingWizardSubmenu::SCREEN_ID ); ?>"><?php esc_html_e( 'Open the onboarding wizard', 'amp' ); ?></a></p>
				<?php else : ?>
					<p><a href="<?php menu_page_url( AMP_Options_Manager::OPTION_NAME ); ?>"><?php esc_html_e( 'Open the onboarding wizard', 'amp' ); ?></a></p>
				<?php endif; ?>
			</div>
		</div>

		<script>
		jQuery( function( $ ) {
			// On dismissing the notice, make a POST request to store this notice with the dismissed WP pointers so it doesn't display again.
			$( <?php echo wp_json_encode( '#' . self::NOTICE_ID ); ?> ).on( 'click', '.notice-dismiss', function() {
				$.post( ajaxurl, {
					pointer: <?php echo wp_json_encode( self::NOTICE_ID ); ?>,
					action: 'dismiss-wp-pointer'
				} );
			} );
		} );
		</script>
		<style type="text/css">
			.amp-plugin-notice {
				background: #E8F5F9;
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1), inset 4px 0 0 #419ECD;
				display: flex;
				padding: 21px 23px;
			}
			.amp-plugin-notice + .notice {
				clear: both;
			}
			.amp-plugin-notice-icon-holder {
				padding-right: 17px;
			}
			.amp-plugin-notice h2 {
				margin-bottom: 8px;
				margin-top: 0;
			}
			.amp-plugin-notice p {
				margin-bottom: 2px;
				margin-top: 2px;
			}

		</style>
		<?php
	}
}
