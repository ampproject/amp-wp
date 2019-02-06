// WIP Pointer function
function sourcesPointer() {
	jQuery( document ).on( 'click', '.tooltip-button', function() {
		jQuery( this ).pointer( {
			content: jQuery( this ).next( '.tooltip' ).attr( 'data-content' ),
			position: {
				edge: 'left',
				align: 'center'
			},
			pointerClass: 'wp-pointer wp-pointer--tooltip'
		} ).pointer( 'open' );
	} );
}

// Run at DOM ready.
jQuery( sourcesPointer );
