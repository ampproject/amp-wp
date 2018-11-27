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
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

// WIP Pointer function
function sourcesPointer() {
	jQuery( document ).on( 'click', '.tooltip-button', function() {
		jQuery( this ).pointer( {
			content: jQuery( this ).next( '.tooltip' ).attr( 'data-content' ),
			position: {
				edge: 'left',
				align: 'center'
			},
			pointerClass: 'wp-pointer wp-pointer--tooltip'
		} ).pointer( 'open' );
	} );
}

domReady( () => {
	sourcesPointer();
} );
