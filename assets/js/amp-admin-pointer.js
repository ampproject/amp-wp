/**
 * This file is part of the AMP Plugin for WordPress.
 *
 * The AMP Plugin for WordPress is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2
 * of the License, or (at your option) any later version.
 *
 * The AMP Plugin for WordPress is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with the AMP Plugin for WordPress. If not, see <https://www.gnu.org/licenses/>.
 */

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
