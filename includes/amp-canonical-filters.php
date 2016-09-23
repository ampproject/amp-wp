<?php

// before Jetpack ever gets loaded, we need to remove a link rel prefetch for canonical AMP support
// TODO: These will be allowed in the AMP validator in the future, so remove as soon as it works.
function amp_canonical_disable_jetpack_dns_fetch() {
    if ( class_exists( 'Jetpack' ) ) {
        remove_action( 'wp_head', array( 'Jetpack', 'dns_prefetch' ) ); 
    }
}
add_action( 'wp_head', 'amp_canonical_disable_jetpack_dns_fetch', 0 ); // Hook early so we can unhook Jetpack