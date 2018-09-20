/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

// WIP Pointer function
function sourcesPointer() {
	jQuery( '.tooltip' ).on( 'hover', function() {
		jQuery( this ).pointer( {
			content: this.innerHTML,
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
