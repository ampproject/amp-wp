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

add_action( 'amp_post_template_head', 'amp_post_template_add_schemaorg_metadata' );
function amp_post_template_add_schemaorg_metadata( $amp_template ) {
	?>
	<script type="application/ld+json"><?php echo json_encode( $amp_template->get( 'metadata' ) ); ?></script>
	<?php
}
