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
		<?php print_admin_styles(); ?>
	</head>
	<body>
		<a class="skip-link" href="#non-amp"><?php esc_html_e( 'Skip to the non-AMP iframe', 'amp' ); ?></a>
		<a class="skip-link" href="#amp"><?php esc_html_e( 'Skip to the AMP iframe', 'amp' ); ?></a>
		<section>
			<nav id="header">
				<ul>
					<li>
						<img src="<?php echo esc_url( amp_get_asset_url( 'images/amp-white-icon.svg' ) ); ?>" alt="AMP logo">
					</li>
					<li>
						<span><?php esc_html_e( 'Paired Browsing', 'amp' ); ?></span>
					</li>
					<li>
						<a id="exit-link"
							class="dashicons-before dashicons-migrate"
							href="<?php echo esc_url( $url ); ?>"
						>
							<?php esc_html_e( 'Exit', 'amp' ); ?>
						</a>
					</li>
				</ul>
			</nav>
		</section>

		<div class="container">
			<div id="non-amp">
				<iframe
					src="<?php echo esc_url( $url ); ?>"
					sandbox="allow-forms allow-scripts allow-same-origin allow-popups"
					title="<?php esc_attr__( 'non-AMP version', 'amp' ); ?>"
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
