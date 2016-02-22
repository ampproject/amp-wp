<?php
// Callbacks for adding content to an AMP template

add_action( 'amp_post_template_head', 'amp_post_template_add_title' );
function amp_post_template_add_title( $amp_template ) {
	?>
	<title><?php echo esc_html( $amp_template->get( 'document_title' ) ); ?></title>
	<?php
}

add_action( 'amp_post_template_head', 'amp_post_template_add_canonical' );
function amp_post_template_add_canonical( $amp_template ) {
	?>
	<link rel="canonical" href="<?php echo esc_url( $amp_template->get( 'canonical_url' ) ); ?>" />
	<?php
}

add_action( 'amp_post_template_head', 'amp_post_template_add_scripts' );
function amp_post_template_add_scripts( $amp_template ) {
	$scripts = $amp_template->get( 'amp_component_scripts', array() );
	foreach ( $scripts as $element => $script ) : ?>
		<script custom-element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
	<?php endforeach; ?>
	<script src="<?php echo esc_url( $amp_template->get( 'amp_runtime_script' ) ); ?>" async></script>
	<?php
}

add_action( 'amp_post_template_head', 'amp_post_template_add_boilerplate_css' );
function amp_post_template_add_boilerplate_css( $amp_template ) {
	?>
	<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
	<?php
}

add_action( 'amp_post_template_head', 'amp_post_template_add_schemaorg_metadata' );
function amp_post_template_add_schemaorg_metadata( $amp_template ) {
	$metadata = $amp_template->get( 'metadata' );
	if ( empty( $metadata ) ) {
		return;
	}
	?>
	<script type="application/ld+json"><?php echo json_encode( $metadata ); ?></script>
	<?php
}
