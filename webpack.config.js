/**
 * External dependencies
 */
const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

function recursiveIssuer( m ) {
	if ( m.issuer ) {
		return recursiveIssuer( m.issuer );
	} else if ( m.name ) {
		return m.name;
	}
	return false;
}

const config = {
	...defaultConfig,
	externals: [
		...defaultConfig.externals,
		{
			// Make localized data importable.
			'amp-validation-i18n': 'ampValidationI18n',
			'amp-stories-fonts': 'ampStoriesFonts',
		},
	],
	entry: {
		'amp-blocks': './assets/src/amp-blocks.js',
		'amp-block-editor-toggle': './assets/src/amp-block-editor-toggle.js',
		'amp-validation-detail-toggle': './assets/src/amp-validation-detail-toggle.js',
		'amp-validation-single-error-url-details': './assets/src/amp-validation-single-error-url-details.js',
		'amp-stories': './assets/src/amp-story-editor-blocks.js',
	},
	output: {
		path: path.resolve( process.cwd(), 'assets', 'js' ),
		filename: '[name]-compiled.js',
		library: 'AMP',
		libraryTarget: 'this',
	},
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.svg$/,
				loader: 'svg-inline-loader',
			},
			{
				test: /\.css$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
				],
			},
		],
	},
	plugins: [
		...defaultConfig.plugins,
		new MiniCssExtractPlugin( {
			filename: '[name].css',
		} ),
	],
	optimization: {
		splitChunks: {
			cacheGroups: {
				blocks: {
					name: 'amp-blocks',
					test: ( m, c, entry = 'amp-blocks' ) => m.constructor.name === 'CssModule' && recursiveIssuer( m ) === entry,
					chunks: 'all',
					enforce: true,
				},
				blocksEditorToggle: {
					name: 'amp-blocks-editor-toggle',
					test: ( m, c, entry = 'amp-blocks-editor-toggle' ) => m.constructor.name === 'CssModule' && recursiveIssuer( m ) === entry,
					chunks: 'all',
					enforce: true,
				},
				validationDetailToggle: {
					name: 'amp-validation-detail-toggle',
					test: ( m, c, entry = 'amp-validation-detail-toggle' ) => m.constructor.name === 'CssModule' && recursiveIssuer( m ) === entry,
					chunks: 'all',
					enforce: true,
				},
				validationSingleDetails: {
					name: 'amp-validation-single-error-url-details',
					test: ( m, c, entry = 'amp-validation-single-error-url-details' ) => m.constructor.name === 'CssModule' && recursiveIssuer( m ) === entry,
					chunks: 'all',
					enforce: true,
				},
				stories: {
					name: 'amp-stories',
					test: ( m, c, entry = 'amp-stories' ) => m.constructor.name === 'CssModule' && recursiveIssuer( m ) === entry,
					chunks: 'all',
					enforce: true,
				},
			},
		},
	},
};

module.exports = config;
