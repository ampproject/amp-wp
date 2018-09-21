/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

// WIP Pointer function
function sourcesPointer() {
	jQuery( document ).on( 'click', '.tooltip-button', function() {
		jQuery( this ).pointer( {
			content: jQuery( this ).next( '.tooltip' ).html(),
			position: {
				edge: 'top',
				align: 'left'
			},
			pointerClass: 'wp-pointer wp-pointer--tooltip'
		} ).pointer( 'open' );
	} );
}

domReady( () => {
	sourcesPointer();
} );
