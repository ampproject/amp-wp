/* exported ampPostMetaBox */

/**
 * AMP Post Meta Box.
 *
 * @since 0.6
 */
var ampPostMetaBox = ( function( $ ) {
	'use strict';

	// Exports.
	return {
		/**
		 * Holds data.
		 *
		 * @since 0.6
		 */
		data: {},

		/**
		 * Core preview button selector.
		 *
		 * @since 0.6
		 */
		previewBtn: '#post-preview',

		/**
		 * AMP preview button selector.
		 *
		 * @since 0.6
		 */
		ampPreviewBtn: '#amp-post-preview',

		/**
		 * Boot plugin.
		 *
		 * @since 0.6
		 * @param {Object} data Object data.
		 * @return {void}
		 */
		boot: function( data ) {
			this.data = data;

			$( document ).ready( function() {
				this.addPreviewButton();
				this.listen();
			}.bind( this ) );
		},

		/**
		 * Events listener.
		 *
		 * @since 0.6
		 * @return {void}
		 */
		listen: function() {
			$( this.ampPreviewBtn ).on( 'click.amp-post-preview', function( e ) {
				e.preventDefault();
				this.onAmpPreviewButtonClick();
			}.bind( this ) );

			$( '#submitpost input[type="submit"]' ).on( 'click', function() {
				$( this.ampPreviewBtn ).addClass( 'amp-disabled' );
			}.bind( this ) );
		},

		/**
		 * Add AMP Preview button.
		 *
		 * @since 0.6
		 * @return {void}
		 */
		addPreviewButton: function() {
			$( this.previewBtn )
				.clone()
				.insertAfter( this.previewBtn )
				.prop( {
					'href': this.data.previewLink,
					'id': this.ampPreviewBtn.replace( '#', '' )
				} )
				.parent()
				.addClass( 'has-next-sibling' );
		},

		/**
		 * AMP Preview button click handler.
		 *
		 * We trigger the Core preview link for events propagation purposes.
		 *
		 * @since 0.6
		 * @return {void}
		 */
		onAmpPreviewButtonClick: function() {
			var currentHref = $( this.ampPreviewBtn ).prop( 'href' );

			$( this.previewBtn )
				.prop( 'href', $( this.ampPreviewBtn ).prop( 'href' ) )
				.trigger( 'click' )
				.prop( 'href', currentHref );
		}
	};
})( window.jQuery );
