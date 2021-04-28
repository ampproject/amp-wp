/**
 * External dependencies
 */
const path = require( 'path' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const WebpackBar = require( 'webpackbar' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Internal dependencies
 */
const IgnoreExtraneousStyleAssets = require( '../bin/ignore-extraneous-style-assets' );

const sharedConfig = {
	...defaultConfig,
	output: {
		path: path.resolve( process.cwd(), 'assets', 'js' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	optimization: {
		...defaultConfig.optimization,
		splitChunks: {
			...defaultConfig.optimization.splitChunks,
			cacheGroups: {
				...defaultConfig.optimization.splitChunks.cacheGroups,
				style: false,
			},
		},
		minimizer: defaultConfig.optimization.minimizer.concat( [
			new OptimizeCSSAssetsPlugin( {} ),
		] ),
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
		new IgnoreExtraneousStyleAssets(),
	],
};

const adminBar = {
	...sharedConfig,
	externals: {
		// Make localized data importable.
		'amp-qa-tester-data': 'ampQaTester',
	},
	entry: {
		'admin-bar': './assets/src/js/admin-bar/index.js',
		'admin-bar-light-dom': './assets/src/css/admin-bar-light-dom.css',
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
