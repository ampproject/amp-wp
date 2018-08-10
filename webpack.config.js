/* global require, module, __dirname */

const path = require( 'path' );

module.exports = {
	entry: {
		'./assets/js/amp-blocks-compiled': './blocks/index.js',
		'./assets/js/amp-story-blocks-compiled': './blocks/stories.js',
		'./assets/js/amp-block-editor-toggle-compiled': './assets/src/amp-block-editor-toggle.js'
	},
	output: {
		path: path.resolve( __dirname ),
		filename: '[name].js'
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
