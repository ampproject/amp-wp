/* global jQuery */
( function( $ ) {
	'use strict';

	var adminTables = {

		load: function() {
			$( document ).ready( $.proxy( function() {
				this.sourcesHideShow();
			}, this ) );
		},

		sourcesHideShow: function() {
			$( 'span.dashicons.toggle-sources' ).on( 'click', function() {
				$( this ).next( '.sources-container' ).toggleClass( 'collapsed' );
				$( this ).toggleClass( 'dashicons-arrow-down-alt2 dashicons-arrow-up-alt2' );
			});
		},
	};

	adminTables.load();

}( jQuery ) );
