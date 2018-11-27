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

/* global require, module, __dirname */

const path = require( 'path' );

module.exports = {
	entry: {
		'./assets/js/amp-blocks-compiled': './blocks/index.js',
		'./assets/js/amp-block-editor-toggle-compiled': './assets/src/amp-block-editor-toggle',
		'./assets/js/amp-validation-detail-toggle-compiled': './assets/src/amp-validation-detail-toggle',
		'./assets/js/amp-validation-tooltips-compiled': './assets/src/amp-validation-tooltips',
		'./assets/js/amp-validation-single-error-url-details-compiled': './assets/src/amp-validation-single-error-url-details'
	},
	output: {
		path: path.resolve( __dirname ),
		filename: '[name].js'
	},
	externals: {
		// Make localized data importable.
		'amp-validation-i18n': 'ampValidationI18n'
	},
	devtool: 'cheap-eval-source-map',
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules)/,
				use: {
					loader: 'babel-loader'
				}
			}
		]
	}
};
