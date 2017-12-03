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
		 * Toggle animation speed.
		 *
		 * @since 0.6
		 */
		toggleSpeed: 200,

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

			$( '.edit-amp-status, [href="#amp_status"]' ).click( function( e ) {
				e.preventDefault();
				this.toggleAmpStatus( $( e.target ) );
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
			var $input;

			// Flag the AMP preview referer.
			$input = $( '<input>' )
				.prop( {
					'type': 'hidden',
					'name': 'amp-preview',
					'value': 'do-preview'
				} )
				.insertAfter( this.ampPreviewBtn );

			// Trigger Core preview button and remove AMP flag.
			$( this.previewBtn ).click();
			$input.remove();
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
