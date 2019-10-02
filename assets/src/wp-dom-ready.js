import domReady from '@wordpress/dom-ready';

if ( ! window.wp ) {
	window.wp = {};
}

wp.domReady = domReady;
