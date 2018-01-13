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
	add_action( 'amp_post_template_head', 'amp_post_template_add_scripts' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_fonts' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_boilerplate_css' );
	add_action( 'amp_post_template_head', 'amp_post_template_add_schemaorg_metadata' );
	add_action( 'amp_post_template_head', 'amp_add_generator_metadata' );
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
 * Print scripts.
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_scripts( $amp_template ) {
	$scripts = $amp_template->get( 'amp_component_scripts', array() );
	foreach ( $scripts as $element => $script ) {
		$custom_type = ( 'amp-mustache' === $element ) ? 'template' : 'element';
		printf(
			'<script custom-%s="%s" src="%s" async></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			esc_attr( $custom_type ),
			esc_attr( $element ),
			esc_url( $script )
		);
	}
	printf(
		'<script src="%s" async></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		esc_url( $amp_template->get( 'amp_runtime_script' ) )
	);
}

/**
 * Print fonts.
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_fonts( $amp_template ) {
	$font_urls = $amp_template->get( 'font_urls', array() );
	foreach ( $font_urls as $slug => $url ) {
		printf( '<link rel="stylesheet" href="%s">', esc_url( esc_url( $url ) ) ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	}
}

/**
 * Print boilerplate CSS.
 */
function amp_post_template_add_boilerplate_css() {
	?>
	<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
	<?php
}

/**
 * Print Schema.org metadata.
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_schemaorg_metadata( $amp_template ) {
	$metadata = $amp_template->get( 'metadata' );
	if ( empty( $metadata ) ) {
		return;
	}
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $metadata ); ?></script>
	<?php
}

/**
 * Print styles.
 *
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_styles( $amp_template ) {
	$styles = $amp_template->get( 'post_amp_styles' );
	if ( ! empty( $styles ) ) {
		echo '/* Inline styles */' . PHP_EOL; // WPCS: XSS OK.
		foreach ( $styles as $selector => $declarations ) {
			$declarations = implode( ';', $declarations ) . ';';
			printf( '%1$s{%2$s}', $selector, $declarations ); // WPCS: XSS OK.
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
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_analytics_data( $amp_template ) {
	$analytics_entries = $amp_template->get( 'amp_analytics' );
	if ( empty( $analytics_entries ) ) {
		return;
	}

	foreach ( $analytics_entries as $id => $analytics_entry ) {
		if ( ! isset( $analytics_entry['type'], $analytics_entry['attributes'], $analytics_entry['config_data'] ) ) {
			/* translators: %1$s is analytics entry ID, %2$s is actual entry keys. */
			_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'Analytics entry for %1$s is missing one of the following keys: `type`, `attributes`, or `config_data` (array keys: %2$s)', 'amp' ), esc_html( $id ), esc_html( implode( ', ', array_keys( $analytics_entry ) ) ) ), '0.3.2' );
			continue;
		}
		$script_element = AMP_HTML_Utils::build_tag( 'script', array(
			'type' => 'application/json',
		), wp_json_encode( $analytics_entry['config_data'] ) );

		$amp_analytics_attr = array_merge( array(
			'id'   => $id,
			'type' => $analytics_entry['type'],
		), $analytics_entry['attributes'] );

		echo AMP_HTML_Utils::build_tag( 'amp-analytics', $amp_analytics_attr, $script_element ); // WPCS: XSS OK.
	}
}

/**
 * Add generator metadata.
 *
 * @since 6.0
 */
function amp_add_generator_metadata() {
	printf( '<meta name="generator" content="%s" />', esc_attr( 'AMP Plugin v' . AMP__VERSION ) );
}
