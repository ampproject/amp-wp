<?php
add_action( 'amp_head', 'amp_head_title' );
function amp_head_title( $amp_post ) {
	?>
	<title><?php echo wp_get_document_title(); ?></title>
	<?php
}

add_action( 'amp_head', 'amp_head_canonical' );
function amp_head_canonical( $amp_post ) {
	?>
	<link rel="canonical" href="<?php echo esc_url( get_permalink() ); ?>" />
	<?php
}

add_action( 'amp_head', 'amp_head_scripts' );
function amp_head_scripts( $amp_post ) {
	foreach ( $amp_post->get_scripts() as $element => $script ) : ?>
		<script custom-element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
	<?php endforeach; ?>
	<?php
}

add_action( 'amp_head', 'amp_head_runtime_script' );
function amp_head_runtime_script( $amp_post ) {
	?>
	<script src="https://cdn.ampproject.org/v0.js" async></script>
	<?php
}

add_action( 'amp_head', 'amp_head_metadata' );
function amp_head_metadata( $amp_post ) {
	?>
	<script type="application/ld+json"><?php echo json_encode( $amp_post->get_metadata() ); ?></script>
	<?php
}
