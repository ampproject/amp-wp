/* global jQuery, ampAdminTables */
( function( $ ) {
	'use strict';

	var adminTables = {

		load: function() {
			$( document ).ready( $.proxy( function() {
				this.sourcesHideShow();
				this.allSourcesHideShow();
				this.addViewErrorsByTypeLinkButton();
			}, this ) );
		},

		sourcesHideShow: function() {
			$( 'span.dashicons.toggle-sources' ).on( 'click', function() {
				$( this ).next( '.sources-container' ).toggleClass( 'collapsed' );
				$( this ).toggleClass( 'dashicons-arrow-down dashicons-arrow-up' );
			} );
		},

		allSourcesHideShow: function() {
			$( '.double-arrow' ).on( 'click', function() {
				$( '.double-arrow' ).find( '.dashicons' ).toggleClass( 'dashicons-arrow-down dashicons-arrow-up' );
				$( 'span.dashicons.toggle-sources' ).toggleClass( 'dashicons-arrow-down dashicons-arrow-up' );
				$( '.sources-container' ).toggleClass( 'collapsed' );
			} );
		},

		addViewErrorsByTypeLinkButton: function() {
			var link = $( '<a></a>' );
			link.prop( 'text', ampAdminTables.errorIndexAnchor );
			link.prop( 'href', ampAdminTables.errorIndexLink );
			link.prop( 'class', 'page-title-action' );
			$( '.wp-heading-inline' ).after( link );
		}
	};

	adminTables.load();
}( jQuery ) );
