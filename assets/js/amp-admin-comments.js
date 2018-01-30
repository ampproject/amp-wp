(function( $ ) {
	'use strict';

	/**
	 * Move the notice below the selector and disable selector.
	 */
	$( document ).on( 'ready', function() {
		var orderSelect = $( '#comment_order' ),
			notice = $( '#amp-comment-notice' );
		if ( orderSelect.length && notice.length ) {
			orderSelect.prop( 'disabled', true );
			notice.appendTo( orderSelect.parent() );
		}
	} );

})( jQuery );
