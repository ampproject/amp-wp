/**
 * External dependencies
 */
const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
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
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.css$/,
				use: [
					// prettier-ignore
					MiniCssExtractPlugin.loader,
					'css-loader',
					'postcss-loader',
				],
			},
		],
	},
	plugins: [
		...defaultConfig.plugins,
		new MiniCssExtractPlugin( {
			filename: '../css/[name]-compiled.css',
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
		'admin-bar': [ './assets/src/js/admin-bar.js' ],
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
