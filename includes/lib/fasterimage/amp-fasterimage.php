<?php

function amp_load_fasterimage_classes() {
	_deprecated_function( __FUNCTION__, '0.6.0' );
}

function amp_get_fasterimage_client( $user_agent ) {
	return new FasterImage\FasterImage( $user_agent );
}
