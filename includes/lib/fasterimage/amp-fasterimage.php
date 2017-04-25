<?php

function amp_load_fasterimage_classes() {
	// We're not using composer to pull in FasterImage so we need to load the files manually
	$fasterimage__DIR__ = dirname( __FILE__ );

	// Stream files
	require_once( $fasterimage__DIR__ . '/Stream/Exception/StreamBufferTooSmallException.php' );
	require_once( $fasterimage__DIR__ . '/Stream/StreamableInterface.php' );
	require_once( $fasterimage__DIR__ . '/Stream/Stream.php' );

	// FasterImage files
	require_once( $fasterimage__DIR__ . '/Exception/InvalidImageException.php' );
	require_once( $fasterimage__DIR__ . '/ExifParser.php' );
	require_once( $fasterimage__DIR__ . '/ImageParser.php' );
	require_once( $fasterimage__DIR__ . '/FasterImage.php' );
}

function amp_get_fasterimage_client( $user_agent ) {
	if ( ! class_exists( 'FasterImage\FasterImage' ) ) {
		amp_load_fasterimage_classes();
	}

	return new FasterImage\FasterImage( $user_agent );
}
