/* global require, module, __dirname */

const path = require( 'path' );

module.exports = {
	entry: {
		'./assets/js/amp-blocks-compiled': './blocks/index.js',
		'./assets/js/wp-i18n-compiled': './assets/src/wp-i18n',
		'./assets/js/wp-dom-ready-compiled': './assets/src/wp-dom-ready',
		'./assets/js/amp-block-editor-toggle-compiled': './assets/src/amp-block-editor-toggle',
		'./assets/js/amp-validation-detail-toggle-compiled': './assets/src/amp-validation-detail-toggle',
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
