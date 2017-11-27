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
		 * Toggle animation speed.
		 *
		 * @since 0.6
		 */
		toggleSpeed: 200,

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
			$( '.edit-amp-status, [href="#amp_status"]' ).click( function( e ) {
				e.preventDefault();
				this.toggleAmpStatus( $( e.target ) );
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
		},

		/**
		 * Add AMP Preview button.
		 *
		 * @since 0.6
		 * @param {Object} $target Event target.
		 * @return {void}
		 */
		toggleAmpStatus: function( $target ) {
			var $container = $( '#amp-status-select' ),
				status = $container.data( 'amp-status' ),
				$checked;

			// Don't modify status on cancel button click.
			if ( ! $target.hasClass( 'button-cancel' ) ) {
				status = $( '[name="amp_status"]:checked' ).val();
			}

			$checked = $( '#amp-satus-' + status );

			// Toggle elements.
			$( '.edit-amp-status' ).fadeToggle( this.toggleSpeed );
			$container.slideToggle( this.toggleSpeed );

			// Update status.
			$container.data( 'amp-status', status );
			$checked.prop( 'checked', 'checked' );
			$( '.amp-status-text' ).text( $checked.next().text() );
		}
	};
})( window.jQuery );
