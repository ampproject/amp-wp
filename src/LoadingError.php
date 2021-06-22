<?php
/**
 * Class LoadingError.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * Client-side app loading error markup and styles.
 *
 * @package AmpProject\AmpWP
 * @since 2.1.3
 * @internal
 */
final class LoadingError {

	/**
	 * Print error message along with necessary styles.
	 */
	public static function print() {
		?>
		<div id="amp-loading-failure" class="error-screen-container">
			<div class="error-screen components-panel">
				<h1>
					<?php esc_html_e( 'Something went wrong.', 'amp' ); ?>
				</h1>

				<?php
				printf(
					'<p>%s</p>',
					wp_kses(
						sprintf(
							/* translators: %s is the AMP support forum URL. */
							__( 'Oops! Something went wrong. Please open your browser console to see the error messages and share them on the <a href="%s" target="_blank" rel="noreferrer noopener">support forum</a>', 'amp' ),
							esc_url( __( 'https://wordpress.org/support/plugin/amp/', 'amp' ) )
						),
						[
							'a' => [
								'href'   => true,
								'target' => true,
								'rel'    => true,
							],
						]
					)
				);
				?>

				<noscript>
					<p><?php esc_html_e( 'You must have JavaScript enabled to use this page.', 'amp' ); ?></p>
				</noscript>
			</div>
		</div>
		<style>
			#amp-loading-failure {
				visibility: hidden;
				animation: amp-wp-show-error 5s steps(1, end) 0s 1 normal both;
			}

			@keyframes amp-wp-show-error {
				from {
					visibility: hidden;
				}
				to {
					visibility: visible;
				}
			}
		</style>
		<?php
	}
}
