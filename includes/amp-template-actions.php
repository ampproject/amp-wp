<?php
add_action( 'amp_head', 'amp_head_title' );
function amp_head_title( $amp_template ) {
	?>
	<title><?php echo esc_html( $amp_template->get( 'document_title' ) ); ?></title>
	<?php
}

add_action( 'amp_head', 'amp_head_canonical' );
function amp_head_canonical( $amp_template ) {
	?>
	<link rel="canonical" href="<?php echo esc_url( $amp_template->get( 'canonical_url' ) ); ?>" />
	<?php
}

add_action( 'amp_head', 'amp_head_scripts' );
function amp_head_scripts( $amp_template ) {
	$scripts = $amp_template->get( 'amp_component_scripts', array() );
	foreach ( $scripts as $element => $script ) : ?>
		<script custom-element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
	<?php endforeach; ?>
	<?php
}

add_action( 'amp_head', 'amp_head_runtime_script' );
function amp_head_runtime_script( $amp_template ) {
	?>
	<script src="<?php echo esc_url( $amp_template->get( 'amp_runtime_script' ) ); ?>" async></script>
	<?php
}

add_action( 'amp_head', 'amp_head_schemaorg_metadata' );
function amp_head_schemaorg_metadata( $amp_template ) {
	?>
	<script type="application/ld+json"><?php echo json_encode( $amp_template->get( 'metadata' ) ); ?></script>
	<?php
}
