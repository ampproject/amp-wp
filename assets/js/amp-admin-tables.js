/* global jQuery, ampAdminTables */
( function( $ ) {
	'use strict';

	var adminTables = {

		load: function() {
			$( document ).ready( $.proxy( function() {
				this.sourcesHideShow();
				this.allSourcesHideShow();
				this.addViewErrorsByTypeLinkButton();
				this.boldURLInPageTitle();
			}, this ) );
		},

		sourcesHideShow: function() {
			$( 'span.dashicons.toggle-sources' ).on( 'click', function() {
				$( this ).next( '.sources-container' ).toggleClass( 'collapsed' );
				$( this ).toggleClass( 'dashicons-arrow-down dashicons-arrow-up' );
			});
		},

		allSourcesHideShow: function() {
			$( '.double-arrow' ).on( 'click', function() {
				$( '.double-arrow' ).find( '.dashicons' ).toggleClass( 'dashicons-arrow-down dashicons-arrow-up' );
				$( 'span.dashicons.toggle-sources' ).toggleClass( 'dashicons-arrow-down dashicons-arrow-up' );
				$( '.sources-container' ).toggleClass( 'collapsed' );
			});
		},

		addViewErrorsByTypeLinkButton: function() {
			$( '.wp-heading-inline' ).after( '<a href="' + ampAdminTables.errorsByTypeLink + '" class="page-title-action">View errors by <strong>Type</strong></a>' );
		},

		boldURLInPageTitle: function() {
			var $heading = $( 'h1.wp-heading-inline' ),
				words = $heading.text().split( ' ' );
			words = words.map( function( item ) {
				return item === 'URL' ? '<strong>' + item + '</strong>' : item;
			} );
			$heading.html( words.join(' ') );
		}
	};

	adminTables.load();

}( jQuery ) );
