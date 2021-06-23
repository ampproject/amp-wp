<?php
/**
 * Class LoadingError.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Client-side app loading error markup and styles.
 *
 * @package AmpProject\AmpWP
 * @since 2.1.3
 * @internal
 */
final class LoadingError implements Service {

	/**
	 * Render error message along with necessary styles.
	 */
	public function render() {
		?>
		<div id="amp-pre-loading-spinner" class="amp-spinner-container">
			<span class="amp-loading-spinner">
				<span class="screen-reader-text">
					<?php esc_html_e( 'Loading', 'amp' ); ?>
				</span>
			</span>
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
							__( 'Check your connection and open your browser console to see if there are any error messages. You may share them on the <a href="%s" target="_blank" rel="noreferrer noopener">support forum</a>.', 'amp' ),
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
				animation: amp-wp-show-element 30s steps(1, end) 0s 1 normal both;
			}

			#amp-pre-loading-spinner {
				visibility: visible;
				animation: amp-wp-hide-element 30s steps(1, end) 0s 1 normal both; /* This could probably reuse amp-wp-show-element if reversed. */
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

			@keyframes amp-loading-spinner {
				from {
					transform: rotate(0deg);
				}
				to {
					transform: rotate(360deg);
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

			.amp-spinner-container .amp-loading-spinner {
				display: inline-block;
				background-color: #949494;
				width: 18px;
				height: 18px;
				opacity: 0.7;
				border-radius: 100%;
				position: relative;
			}

			.amp-spinner-container .amp-loading-spinner::before {
				content: '';
				position: absolute;
				background-color: #fff;
				width: calc(18px / 4.5);
				height: calc(18px / 4.5);
				border-radius: 100%;
				transform-origin: calc(18px / 3) calc(18px / 3);
				top: calc((18px - 18px * (2 / 3)) / 2);
				left: calc((18px - 18px * (2 / 3)) / 2);
				animation: amp-loading-spinner 1s infinite linear;
			}
		</style>
		<?php
	}
}
