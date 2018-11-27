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

/* exported ampValidatedUrlsIndex */

const ampValidatedUrlsIndex = ( function() { // eslint-disable-line no-unused-vars
	let component = {
		classes: {}
	};

	/**
	 * The class for the new status
	 *
	 * @type {string}
	 */
	component.classes.new = 'new';

	/**
	 * Boot.
	 */
	component.boot = function boot() {
		component.highlightRowsWithNewStatus();
	};

	/**
	 * Highlight rows with new status.
	 */
	component.highlightRowsWithNewStatus = function highlightRowsWithNewStatus() {
		document.querySelectorAll( 'tr[id^="post-"]' ).forEach( function( row ) {
			if ( row.querySelector( 'span.status-text.' + component.classes.new ) ) {
				row.classList.add( 'new' );
			}
		} );
	};

	return component;
}() );
