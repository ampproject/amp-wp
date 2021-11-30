/* global jQuery */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
// Disable reason: Needed so that the wp-pointer script is added to dependencies list by webpack.
// eslint-disable-next-line import/no-unresolved
import '@wordpress/pointer';

// WIP Pointer function
function sourcesPointer() {
	jQuery( document ).on( 'click', '.tooltip-button', function() {
		jQuery( this ).pointer( {
			content: jQuery( this ).next( '.tooltip' ).attr( 'data-content' ),
			position: {
				edge: 'left',
				align: 'center',
			},
			pointerClass: 'wp-pointer wp-pointer--tooltip',
		} ).pointer( 'open' );
	} );
}

domReady( sourcesPointer );
