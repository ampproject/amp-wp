/* exported AmpPostMetaBox */

/**
 * AMP Post Meta Box.
 *
 * @since 0.6
 */
var AmpPostMetaBox = ( function( $ ) {
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
			}.bind( this ) );
		},

		/**
		 * Add AMP Preview button.
		 *
		 * @since 0.6
		 * @return {void}
		 */
		addPreviewButton: function() {
			var $previewBtn = $( '#preview-action a.preview' );

			$previewBtn
				.clone()
				.insertAfter( $previewBtn )
				.addClass( 'amp-preview' )
				.prop( {
					'href': this.data.previewLink,
					'id': 'amp-' + $previewBtn.prop( 'id' ),
					'target': 'amp-' + $previewBtn.prop( 'target' )
				} )
				.parent()
				.addClass( 'has-next-sibling' );
		}
	};
})( window.jQuery );
