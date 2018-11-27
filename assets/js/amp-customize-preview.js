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

/* exported ampCustomizePreview */

var ampCustomizePreview = ( function( api ) { // eslint-disable-line no-unused-vars
	'use strict';

	var component = {};

	/**
	 * Boot using data sent inline.
	 *
	 * @param {Object}  data - PHP exports.
	 * @param {boolean} data.available - Whether AMP is available.
	 * @param {boolean} data.enabled - Whether AMP is enabled.
	 * @return {void}
	 */
	component.boot = function boot( data ) {
		api.bind( 'preview-ready', function() {
			api.preview.bind( 'active', function() {
				api.preview.send( 'amp-status', data );
			} );
		} );
	};

	return component;
}( wp.customize ) );
