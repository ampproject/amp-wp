const path = require( 'path' );

module.exports = {
	entry: {
		'./assets/js/editor-blocks': './blocks/index.js'
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
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: 'babel-loader'
				}
			}
		]
	}
};
