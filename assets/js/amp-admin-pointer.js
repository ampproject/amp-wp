/**
 * Adds an admin pointer that describes new features in 1.0.
 */

/* exported ampAdminPointer */
/* global ajaxurl, jQuery */
var ampAdminPointer = ( function( $ ) { // eslint-disable-line no-unused-vars
	'use strict';

	return {

		/**
		 * Loads the pointer.
		 *
		 * @param {Object} data - Module data.
		 * @return {void}
		 */
		load: function load( data ) {
			var options = $.extend(
				data.pointer.options,
				{
					/**
					 * Makes a POST request to store the pointer ID as dismissed for this user.
					 */
					close: function() {
						$.post( ajaxurl, {
							pointer: data.pointer.pointer_id,
							action: 'dismiss-wp-pointer'
						} );
					},

					/**
					 * Adds styling to the pointer, to ensure it appears alongside the AMP menu.
					 *
					 * @param {Object} event The pointer event.
					 * @param {Object} t Pointer element and state.
					 */
					show: function( event, t ) {
						t.pointer.css( 'position', 'fixed' );
					}
				}
			);

			$( data.pointer.target ).pointer( options ).pointer( 'open' );
		}
	};
}( jQuery ) );
