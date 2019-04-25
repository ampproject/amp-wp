/**
 * External dependencies
 */
const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );

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
		minimizer: [ new TerserPlugin( {
			parallel: true,
			sourceMap: false,
			cache: true,
		} ) ],
	},
};

const ampStories = {
	...defaultConfig,
	...sharedConfig,
	externals: [
		...defaultConfig.externals,
		{
			// Make localized data importable.
			'amp-stories-fonts': 'ampStoriesFonts',
		},
	],
	entry: {
		'amp-stories': './assets/src/stories-editor/index.js',
	},
	output: {
		path: path.resolve( process.cwd(), 'assets', 'js' ),
		filename: '[name].js',
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
	optimization: {
		...sharedConfig.optimization,
		splitChunks: {
			cacheGroups: {
				stories: {
					name: 'amp-stories',
					test: /\.css$/,
					chunks: 'all',
					enforce: true,
				},
			},
		},
	},
};

const ampValidation = {
	...defaultConfig,
	...sharedConfig,
	externals: [
		...defaultConfig.externals,
		{
			// Make localized data importable.
			'amp-validation-i18n': 'ampValidationI18n',
		},
	],
	entry: {
		'amp-validated-url-post-edit-screen': './assets/src/amp-validation/amp-validated-url-post-edit-screen.js',
		'amp-validated-urls-index': './assets/src/amp-validation/amp-validated-urls-index.js',
		'amp-validation-detail-toggle': './assets/src/amp-validation/amp-validation-detail-toggle.js',
		'amp-validation-single-error-url-details': './assets/src/amp-validation/amp-validation-single-error-url-details.js',
	},
};

const blockEditor = {
	...defaultConfig,
	...sharedConfig,
	externals: [
		...defaultConfig.externals,
		{
			// Make localized data importable.
			'amp-block-editor-data': 'wpAmpEditor',
		},
	],
	entry: {
		'amp-block-editor': './assets/src/block-editor/index.js',
		'amp-editor-blocks': './assets/src/block-editor/amp-editor-blocks.js',
		'amp-block-validation': './assets/src/block-editor/amp-block-validation.js',
	},
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.css$/,
				use: 'null-loader',
			},
		],
	},
};

const classicEditor = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-post-meta-box': './assets/src/classic-editor/amp-post-meta-box.js',
	},
};

const admin = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-admin-pointer': './assets/src/admin/amp-admin-pointer.js',
		'amp-validation-tooltips': './assets/src/admin/amp-validation-tooltips.js',
	},
};

const wpPolyfills = {
	...defaultConfig,
	...sharedConfig,
	externals: {},
	entry: {
		'wp-i18n': './assets/src/polyfills/wp-i18n.js',
		'wp-dom-ready': './assets/src/polyfills/wp-dom-ready.js',
	},
};

module.exports = [
	ampStories,
	ampValidation,
	blockEditor,
	classicEditor,
	admin,
	wpPolyfills,
];
