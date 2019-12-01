<?php
/**
 * AMP Paired Browsing experience template.
 *
 * @package AMP
 */

$url     = remove_query_arg( AMP_Theme_Support::PAIRED_BROWSING_QUERY_VAR );
$amp_url = add_query_arg( amp_get_slug(), '1', $url );
?>

<!DOCTYPE html>
<html <?php language_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<title><?php esc_html_e( 'Loading&#8230;', 'amp' ); ?></title>
		<meta name="robots" content="noindex,nofollow">
		<?php print_admin_styles(); ?>
	</head>
	<body>
		<a class="skip-link" href="#non-amp"><?php esc_html_e( 'Skip to the non-AMP iframe', 'amp' ); ?></a>
		<a class="skip-link" href="#amp"><?php esc_html_e( 'Skip to the AMP iframe', 'amp' ); ?></a>
		<section>
			<nav id="header">
				<ul>
					<li class="iframe-label non-amp"><a id="non-amp-link" class="exit-link" title="<?php esc_attr_e( 'Exit paired browsing onto the non-AMP version.', 'amp' ); ?>" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Non-AMP', 'amp' ); ?><span class="dashicons dashicons-migrate"></span></a></li>
					<li class="iframe-label amp"><a id="amp-link" class="exit-link" title="<?php esc_attr_e( 'Exit paired browsing onto the AMP version.', 'amp' ); ?>" href="<?php echo esc_url( $amp_url ); ?>"><?php esc_html_e( 'AMP', 'amp' ); ?><span class="dashicons dashicons-migrate"></span></a></li>
				</ul>
			</nav>
		</section>

		<div class="container">
			<div class="disconnect-overlay">
				<div class="dialog" role="dialog">
					<div class="dialog-icon">
						<span class="dashicons dashicons-warning"></span>
					</div>

					<div class="dialog-text">
						<span class="general">
							<?php esc_html_e( 'The navigated URL is not available for paired browsing.', 'amp' ); ?>
						</span>

						<span class="invalid-amp">
							<?php esc_html_e( 'The navigated page does not have an AMP counterpart due to invalid AMP markup being kept.', 'amp' ); ?>
						</span>
					</div>

					<div class="dialog-buttons">
						<button class="button exit">
							<?php esc_html_e( 'Exit', 'amp' ); ?>
						</button>
						<button class="button go-back">
							<?php esc_html_e( 'Go Back', 'amp' ); ?>
						</button>
					</div>
				</div>
			</div>

			<div id="non-amp">
				<iframe
					src="<?php echo esc_url( $url ); ?>"
					sandbox="allow-forms allow-scripts allow-same-origin allow-popups"
					title="<?php esc_attr__( 'Non-AMP version', 'amp' ); ?>"
				></iframe>
			</div>

			<div id="amp">
				<iframe
					src="<?php echo esc_url( $amp_url ); ?>"
					sandbox="allow-forms allow-scripts allow-same-origin allow-popups"
					title="<?php esc_attr__( 'AMP version', 'amp' ); ?>"
				></iframe>
			</div>
		</div>

		<?php print_footer_scripts(); ?>
	</body>
</html>
