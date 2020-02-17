<?php
/**
 * Callbacks for adding content to an AMP template.
 *
 * @package AMP
 */

/**
 * Register hooks.
 */
function amp_post_template_init_hooks() {
	add_action( 'amp_post_template_head', 'amp_post_template_add_title' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_canonical' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_fonts' );
	add_action( 'amp_post_template_head', 'amp_print_schemaorg_metadata' );
	add_action( 'amp_post_template_head', 'amp_add_generator_metadata' );
	add_action( 'amp_post_template_head', 'wp_generator' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_block_styles' );
	add_action( 'amp_post_template_css', 'amp_post_template_add_styles', 99 );
	add_action( 'amp_post_template_data', 'amp_post_template_add_analytics_script' );
	add_action( 'amp_post_template_footer', 'amp_post_template_add_analytics_data' );
}

/**
 * Add title.
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
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_fonts( $amp_template ) {
	$font_urls = $amp_template->get( 'font_urls', [] );
	foreach ( $font_urls as $slug => $url ) {
		printf( '<link rel="stylesheet" href="%s">', esc_url( esc_url( $url ) ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	}
}

/**
 * Add block styles for core blocks and third-party blocks.
 *
 * @since 1.5.0
 */
function amp_post_template_add_block_styles() {
	add_theme_support( 'wp-block-styles' );
	if ( function_exists( 'wp_common_block_scripts_and_styles' ) ) {
		wp_common_block_scripts_and_styles();
	}
	wp_styles()->do_items();
}

/**
 * Print styles.
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
 * Add analytics scripts.
 *
 * @param array $data Data.
 * @return array Data.
 */
function amp_post_template_add_analytics_script( $data ) {
	if ( ! empty( $data['amp_analytics'] ) ) {
		$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
	}
	return $data;
}

/**
 * Print analytics data.
 *
 * @since 0.3.2
 */
function amp_post_template_add_analytics_data() {
	$analytics = amp_add_custom_analytics();
	amp_print_analytics( $analytics );
}
