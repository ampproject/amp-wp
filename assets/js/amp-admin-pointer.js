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
					}
				}
			);

			$( data.pointer.target ).pointer( options ).pointer( 'open' );
		}
	};
}( jQuery ) );
