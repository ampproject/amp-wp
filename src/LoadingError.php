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
	 * Render error message along with necessary styles.
	 */
	public static function render() {
		?>
		<div id="amp-pre-loading-spinner" class="amp-spinner-container">
			<img src="<?php echo esc_url( admin_url( 'images/loading.gif' ) ); ?>" alt="Loading" width="16" height="16">
		</div>

		<div id="amp-loading-failure" class="error-screen-container">
			<div class="error-screen components-panel">
				<h1>
					<?php esc_html_e( 'Something went wrong.', 'amp' ); ?>
				</h1>

				<?php
				printf(
					'<p class="amp-loading-failure-script">%s</p>',
					wp_kses(
						sprintf(
							/* translators: %s is the AMP support forum URL. */
							__( 'Oops! Something went wrong. Please open your browser console to see the error messages and share them on the <a href="%s" target="_blank" rel="noreferrer noopener">support forum</a>.', 'amp' ),
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
					<p class="amp-loading-failure-noscript"><?php esc_html_e( 'You must have JavaScript enabled to use this page.', 'amp' ); ?></p>
				</noscript>
			</div>
		</div>
		<style>
			#amp-loading-failure {
				visibility: hidden;
				animation: amp-wp-show-element 5s steps(1, end) 0s 1 normal both;
			}

			#amp-pre-loading-spinner {
				visibility: visible;
				animation: amp-wp-hide-element 5s steps(1, end) 0s 1 normal both; /* This could probably reuse amp-wp-show-element if reversed. */
			}

			.amp-loading-failure-noscript {
				display: none;
			}

			@keyframes amp-wp-show-element {
				from {
					visibility: hidden;
				}
				to {
					visibility: visible;
				}
			}

			@keyframes amp-wp-hide-element {
				from {
					visibility: visible;
				}
				to {
					visibility: hidden;
				}
			}

			body.no-js #amp-loading-failure {
				animation: none;
				visibility: visible;
			}

			body.no-js .amp-loading-failure-noscript {
				display: block;
			}

			body.no-js #amp-pre-loading-spinner,
			body.no-js .amp-loading-failure-script {
				display: none;
			}
		</style>
		<?php
	}
}
