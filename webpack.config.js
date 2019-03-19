/* global process, require, module, __dirname */

/**
 * External dependencies
 */
const { BundleAnalyzerPlugin } = require( 'webpack-bundle-analyzer' );
const LiveReloadPlugin = require( 'webpack-livereload-plugin' );
const path = require( 'path' );

/**
 * WordPress dependencies
 */
const { camelCaseDash } = require( '@wordpress/scripts/utils' );

/**
 * Converts @wordpress/* string request into request object.
 *
 * Note this isn't the same as camel case because of the
 * way that numbers don't trigger the capitalized next letter.
 *
 * @example
 * formatRequest( '@wordpress/api-fetch' );
 * // { this: [ 'wp', 'apiFetch' ] }
 * formatRequest( '@wordpress/i18n' );
 * // { this: [ 'wp', 'i18n' ] }
 *
 * @param {string} request Request name from import statement.
 * @return {Object} Request object formatted for further processing.
 */
const formatRequest = ( request ) => {
	// '@wordpress/api-fetch' -> [ '@wordpress', 'api-fetch' ]
	const [ , name ] = request.split( '/' );

	// { this: [ 'wp', 'apiFetch' ] }
	return {
		this: [ 'wp', camelCaseDash( name ) ],
	};
};

const wordpressExternals = ( context, request, callback ) => {
	if ( /^@wordpress\//.test( request ) ) {
		callback( null, formatRequest( request ), 'this' );
	} else {
		callback();
	}
};

const externals = [
	{
		// Make localized data importable.
		'amp-validation-i18n': 'ampValidationI18n',
		react: 'React',
		'react-dom': 'ReactDOM',
		moment: 'moment',
		jquery: 'jQuery',
		lodash: 'lodash',
		'lodash-es': 'lodash',

		// Distributed NPM packages may depend on Babel's runtime regenerator.
		// In a WordPress context, the regenerator is assigned to the global
		// scope via the `wp-polyfill` script. It is reassigned here as an
		// externals to reduce the size of generated bundles.
		//
		// See: https://github.com/WordPress/gutenberg/issues/13890
		'@babel/runtime/regenerator': 'regeneratorRuntime',
	},
	wordpressExternals,
];

const isProduction = process.env.NODE_ENV === 'production';
const mode = isProduction ? 'production' : 'development';

const config = {
	mode,
	entry: {
		'./assets/js/amp-blocks-compiled': './assets/src/blocks/index.js',
		'./assets/js/amp-block-editor-toggle-compiled': './assets/src/amp-block-editor-toggle.js',
		'./assets/js/amp-validation-detail-toggle-compiled': './assets/src/amp-validation-detail-toggle.js',
		'./assets/js/amp-validation-single-error-url-details-compiled': './assets/src/amp-validation-single-error-url-details.js',
		'./assets/js/amp-story-editor-blocks-compiled': './assets/src/amp-story-editor-blocks.js',
		'./assets/js/amp-story-blocks-compiled': './assets/src/blocks/stories.js',
	},
	output: {
		path: path.resolve( __dirname ),
		filename: '[name].js',
		library: 'AMP',
		libraryTarget: 'this',
	},
	externals,
	resolve: {
		alias: {
			'lodash-es': 'lodash',
		},
	},
	devtool: isProduction ? undefined : 'cheap-eval-source-map',
	module: {
		rules: [
			{
				test: /\.js$/,
				use: 'source-map-loader',
				enforce: 'pre',
			},
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader',
				},
			},
			{
				test: /\.svg$/,
				loader: 'svg-inline-loader',
			},
		],
	},
	plugins: [
		// WP_BUNDLE_ANALYZER global variable enables utility that represents bundle content
		// as convenient interactive zoomable treemap.
		process.env.WP_BUNDLE_ANALYZER && new BundleAnalyzerPlugin(),
		// WP_LIVE_RELOAD_PORT global variable changes port on which live reload works
		// when running watch mode.
		! isProduction && new LiveReloadPlugin( { port: process.env.WP_LIVE_RELOAD_PORT || 35729 } ),
	].filter( Boolean ),
	stats: {
		children: false,
	},
};

if ( ! isProduction ) {
	// WP_DEVTOOL global variable controls how source maps are generated.
	// See: https://webpack.js.org/configuration/devtool/#devtool.
	config.devtool = process.env.WP_DEVTOOL || 'source-map';
}

module.exports = config;
