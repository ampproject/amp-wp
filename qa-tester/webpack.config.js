/**
 * External dependencies
 */
const path = require( 'path' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const WebpackBar = require( 'webpackbar' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const sharedConfig = {
	output: {
		path: path.resolve( process.cwd(), 'assets', 'js' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	optimization: {
		minimizer: [
			new TerserPlugin( {
				parallel: true,
				sourceMap: false,
				cache: true,
				terserOptions: {
					output: {
						comments: /translators:/i,
					},
				},
				extractComments: false,
			} ),
			new OptimizeCSSAssetsPlugin( {} ),
		],
	},
	plugins: [
		...defaultConfig.plugins.map( ( plugin ) => {
			if ( plugin.constructor.name === 'MiniCssExtractPlugin' ) {
				plugin.options.filename = '../css/[name]-compiled.css';
			}
			return plugin;
		} ),
		new RtlCssPlugin( {
			filename: '../css/[name]-compiled-rtl.css',
		} ),
	],
};

const adminBar = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'admin-bar': [ './assets/src/js/admin-bar/index.js' ],
	},
	plugins: [
		...sharedConfig.plugins,
		new WebpackBar( {
			name: 'Admin Bar',
			color: '#36f271',
		} ),
	],
};

module.exports = [ adminBar ];
