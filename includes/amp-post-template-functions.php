<?php
/**
 * Callbacks for adding content to an AMP template.
 *
 * @package AMP
 */

/**
 * Register hooks.
 *
 * @internal
 */
function amp_post_template_init_hooks() {
	if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '5.7', '>=' ) ) {
		add_action( 'amp_post_template_head', 'wp_robots' );
	} else {
		add_action( 'amp_post_template_head', 'noindex' );
	}
	add_action( 'amp_post_template_head', 'amp_post_template_add_title' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_canonical' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_fonts' );
	add_action( 'amp_post_template_head', 'amp_add_generator_metadata' );
	add_action( 'amp_post_template_head', 'wp_generator' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_block_styles' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_default_styles' );
	add_action( 'amp_post_template_css', 'amp_post_template_add_editor_color_styles' );
	add_action( 'amp_post_template_css', 'amp_post_template_add_styles', 99 );
	add_action( 'amp_post_template_footer', 'amp_post_template_add_analytics_data' );

	add_action( 'admin_bar_init', [ 'AMP_Theme_Support', 'init_admin_bar' ] );
	add_action( 'amp_post_template_footer', 'wp_admin_bar_render' );

	// Printing scripts here is done primarily for the benefit of the admin bar. Note that wp_enqueue_scripts() is not called.
	add_action( 'amp_post_template_head', 'wp_print_head_scripts' );
	add_action( 'amp_post_template_footer', 'wp_print_footer_scripts' );
}

/**
 * Add title.
 *
 * @internal
 *
 * @param AMP_Post_Template $amp_template template.
 */
function amp_post_template_add_title( $amp_template ) {
	?>
	<title><?php echo esc_html( $amp_template->get( 'document_title' ) ); ?></title>
	<?php
}

/**
 * Add canonical link.
 *
 * @internal
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_canonical( $amp_template ) {
	?>
	<link rel="canonical" href="<?php echo esc_url( $amp_template->get( 'canonical_url' ) ); ?>" />
	<?php
}

/**
 * Print fonts.
 *
 * @internal
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_fonts( $amp_template ) {
	$font_urls = $amp_template->get( 'font_urls', [] );
	foreach ( $font_urls as $url ) {
		printf( '<link rel="stylesheet" href="%s">', esc_url( esc_url( $url ) ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	}
}

/**
 * Add block styles for core blocks and third-party blocks.
 *
 * @internal
 *
 * @since 1.5.0
 */
function amp_post_template_add_block_styles() {
	add_theme_support( 'wp-block-styles' );
	if ( function_exists( 'wp_common_block_scripts_and_styles' ) ) {
		wp_common_block_scripts_and_styles();
	}

	// Note that this will also print the admin-bar styles since WP_Admin_Bar::initialize() has been called.
	wp_styles()->do_items();
}

/**
 * Print editor color styles.
 *
 * @internal
 * @since 2.1
 */
function amp_post_template_add_editor_color_styles() {
	$color_palette = current( (array) get_theme_support( 'editor-color-palette' ) );

	if ( false === $color_palette ) {
		return;
	}

	foreach ( $color_palette as $color_option ) {
		$text_color = 127 > amp_get_relative_luminance_from_hex( $color_option['color'] ) ? '#fff' : '#000';

		printf(
			':root .has-%1$s-color { color: %2$s }
			:root .has-%1$s-background-color { color: %3$s; background-color: %2$s }',
			$color_option['slug'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$color_option['color'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$text_color // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}

/**
 * Get relative luminance from color hex value.
 *
 * Copied from `\Twenty_Twenty_One_Custom_Colors::get_relative_luminance_from_hex()`.
 *
 * @internal
 * @see https://github.com/WordPress/wordpress-develop/blob/acbbbd18b32b5429264622141a6d058b64f3a5ad/src/wp-content/themes/twentytwentyone/classes/class-twenty-twenty-one-custom-colors.php#L138-L156
 * @since 2.1
 *
 * @param $hex string Color hex value.
 * @return int Relative luminance value.
 */
function amp_get_relative_luminance_from_hex( $hex ) {

	// Remove the "#" symbol from the beginning of the color.
	$hex = ltrim( $hex, '#' );

	// Make sure there are 6 digits for the below calculations.
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	// Get red, green, blue.
	$red   = hexdec( substr( $hex, 0, 2 ) );
	$green = hexdec( substr( $hex, 2, 2 ) );
	$blue  = hexdec( substr( $hex, 4, 2 ) );

	// Calculate the luminance.
	$lum = ( 0.2126 * $red ) + ( 0.7152 * $green ) + ( 0.0722 * $blue );
	return (int) round( $lum );
}

/**
 * Print default styles.
 *
 * @since 2.0.1
 * @internal
 */
function amp_post_template_add_default_styles() {
	wp_print_styles( 'amp-default' );
}

/**
 * Print styles.
 *
 * @internal
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_styles( $amp_template ) {
	$stylesheets = $amp_template->get( 'post_amp_stylesheets' );
	if ( ! empty( $stylesheets ) ) {
		echo '/* Inline stylesheets */' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo implode( '', $stylesheets ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	$styles = $amp_template->get( 'post_amp_styles' );
	if ( ! empty( $styles ) ) {
		echo '/* Inline styles */' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( $styles as $selector => $declarations ) {
			$declarations = implode( ';', $declarations ) . ';';
			printf( '%1$s{%2$s}', $selector, $declarations ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}

/**
 * Print analytics data.
 *
 * @internal
 *
 * @since 0.3.2
 */
function amp_post_template_add_analytics_data() {
	$analytics = amp_add_custom_analytics();
	amp_print_analytics( $analytics );
}
