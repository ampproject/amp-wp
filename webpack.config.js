/* global process, require, module, __dirname */

const path = require( 'path' );

const externals = {
	// Make localized data importable.
	'amp-validation-i18n': 'ampValidationI18n',
	jquery: 'jQuery',
	lodash: 'lodash',
	react: 'React',
	'react-dom': 'ReactDOM',
};

// Define WordPress dependencies
const wpDependencies = [
	'blocks',
	'components',
	'compose',
	'data',
	'editor',
	'edit-post',
	'element',
	'hooks',
	'i18n',
	'api-fetch',
	'blocks',
	'components',
	'compose',
	'date',
	'editor',
	'element',
	'hooks',
	'i18n',
	'utils',
	'data',
	'viewport',
	'core-data',
	'plugins',
	'edit-post',
	'keycodes',
];

/**
 * Given a string, returns a new string with dash separators converted to
 * camel-case equivalent. This is not as aggressive as `_.camelCase` in
 * converting to uppercase, where Lodash will convert letters following
 * numbers.
 *
 * @param {string} string Input dash-delimited string.
 *
 * @return {string} Camel-cased string.
 */
function camelCaseDash( string ) {
	return string.replace(
		/-([a-z])/,
		( match, letter ) => letter.toUpperCase()
	);
}

wpDependencies.forEach( ( name ) => {
	externals[ `@wordpress/${ name }` ] = {
		this: [ 'wp', camelCaseDash( name ) ],
	};
} );

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
	mode: isProduction ? 'production' : 'development',
	entry: {
		'./assets/js/amp-blocks-compiled': './assets/src/blocks/index.js',
		'./assets/js/amp-block-editor-toggle-compiled': './assets/src/amp-block-editor-toggle.js',
		'./assets/js/amp-validation-detail-toggle-compiled': './assets/src/amp-validation-detail-toggle.js',
		'./assets/js/amp-validation-single-error-url-details-compiled': './assets/src/amp-validation-single-error-url-details.js',
		'./assets/js/amp-story-blocks-compiled': './assets/src/blocks/stories.js',
	},
	output: {
		path: path.resolve( __dirname ),
		filename: '[name].js',
		library: 'AMP',
		libraryTarget: 'this',
	},
	externals,
	devtool: isProduction ? undefined : 'cheap-eval-source-map',
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader',
				},
			},
		],
	},
};
