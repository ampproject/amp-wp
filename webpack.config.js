/**
 * External dependencies
 */
const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
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
			new OptimizeCSSAssetsPlugin( { } ),
		],
	},
};

const ampStories = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-stories-editor': './assets/src/stories-editor/index.js',
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
					name: 'amp-stories-editor',
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
	externals: {
		// Make localized data importable.
		'amp-block-editor-data': 'ampBlockEditor',
	},
	entry: {
		'amp-block-editor': './assets/src/block-editor/index.js',
		'amp-block-validation': './assets/src/block-validation/index.js',
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
		'amp-validation-tooltips': './assets/src/admin/amp-validation-tooltips.js',
	},
};

const customizer = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-customize-controls': './assets/src/customizer/amp-customize-controls.js',
		'amp-customize-preview': './assets/src/customizer/amp-customize-preview.js',
		'amp-customizer-design-preview': './assets/src/customizer/amp-customizer-design-preview.js',
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
	customizer,
	wpPolyfills,
];
