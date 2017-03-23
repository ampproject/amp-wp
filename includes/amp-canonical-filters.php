<?php

// before Jetpack ever gets loaded, we need to remove a
// link rel prefetch for canonical AMP support
// TODO[@amedina] These will be allowed by the validator in the future
// TODO[@amedina]  remove as soon as it works

function amp_canonical_disable_jetpack_dns_fetch() {
	if ( class_exists( 'Jetpack') ) {
		remove_action( 'wp_head', array( 'Jetpack', 'dns_prefetch' ) );
	}
}
// Hook action early on so we can unhook Jetpack
add_action( 'wp_head', 'amp_canonical_disable_jetpack_dns_fetch', 0);