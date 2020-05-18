/**
 * External dependencies
 */
const path = require( 'path' );
const fs = require( 'fs' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const WebpackBar = require( 'webpackbar' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const { defaultRequestToExternal, defaultRequestToHandle } = require( '@wordpress/dependency-extraction-webpack-plugin/util' );
const { dependencies } = require( './package' );

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

const ampValidation = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-validated-url-post-edit-screen': './assets/src/amp-validation/amp-validated-url-post-edit-screen.js',
		'amp-validated-urls-index': './assets/src/amp-validation/amp-validated-urls-index.js',
		'amp-validation-detail-toggle': './assets/src/amp-validation/amp-validation-detail-toggle.js',
		'amp-validation-single-error-url-details': './assets/src/amp-validation/amp-validation-single-error-url-details.js',
	},
	plugins: [
		...defaultConfig.plugins,
		new WebpackBar( {
			name: 'AMP Validation',
			color: '#1c5fec',
		} ),
	],
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
		new WebpackBar( {
			name: 'Block Editor',
			color: '#1773a8',
		} ),
	],
};

const classicEditor = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-post-meta-box': './assets/src/classic-editor/amp-post-meta-box.js',
	},
	plugins: [
		...defaultConfig.plugins,
		new WebpackBar( {
			name: 'Classic Editor',
			color: '#dc3232',
		} ),
	],
};

const admin = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-validation-tooltips': './assets/src/admin/amp-validation-tooltips.js',
		'amp-paired-browsing-app': './assets/src/admin/paired-browsing/app.js',
		'amp-paired-browsing-client': './assets/src/admin/paired-browsing/client.js',
	},
	module: {
		...defaultConfig.module,
		rules: [
			...defaultConfig.module.rules,
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
		new WebpackBar( {
			name: 'Admin',
			color: '#67b255',
		} ),
	],
};

const customizer = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-customize-controls': './assets/src/customizer/amp-customize-controls.js',
		'amp-customize-preview': './assets/src/customizer/amp-customize-preview.js',
		'amp-customizer-design-preview': './assets/src/customizer/amp-customizer-design-preview.js',
	},
	plugins: [
		...defaultConfig.plugins,
		new WebpackBar( {
			name: 'Customizer',
			color: '#f27136',
		} ),
	],
};

const gutenbergPackages = Object.keys( dependencies )
	.filter(
		( packageName ) =>
			packageName.startsWith( '@wordpress' ) || packageName.startsWith( '@babel' ),
	);

const wpPolyfills = {
	...defaultConfig,
	...sharedConfig,
	externals: {},
	plugins: [
		new DependencyExtractionWebpackPlugin( {
			useDefaults: false,
			requestToHandle: ( request ) => {
				if ( gutenbergPackages.includes( request ) ) {
					return undefined;
				}

				return defaultRequestToHandle( request );
			},
			requestToExternal: ( request ) => {
				if ( gutenbergPackages.includes( request ) ) {
					return undefined;
				}

				return defaultRequestToExternal( request );
			},
		} ),
		new CopyWebpackPlugin( [
			{
				from: 'node_modules/lodash/lodash.js',
				to: './vendor/lodash.js',
			},
			{
				from: 'node_modules/react/umd/react.production.min.js',
				to: './vendor/react.js',
			},
			{
				from: 'node_modules/react-dom/umd/react-dom.production.min.js',
				to: './vendor/react-dom.js',
			},
		] ),
		new WebpackBar( {
			name: 'WordPress Polyfills',
			color: '#21a0d0',
		} ),
	],
	entry: gutenbergPackages.reduce( ( memo, packageName ) => {
		const packageSlug = packageName.replace( '@wordpress/', 'wp-' ).replace( '@babel/', 'wp-' );
		const polyfillFile = path.resolve( __dirname, `assets/src/polyfills/${ packageSlug }.js` );

		if ( fs.existsSync( polyfillFile ) ) {
			memo[ packageSlug ] = polyfillFile;
		}

		return memo;
	}, {} ),
};

const setup = {
	...defaultConfig,
	...sharedConfig,
	entry: {
		'amp-setup': './assets/src/setup',
	},
	externals: {
		'amp-setup': 'ampSetup',
	},
	plugins: [
		...defaultConfig.plugins,
		new WebpackBar( {
			name: 'Setup',
			color: '#1773a8',
		} ),
	],
};

module.exports = [
	ampValidation,
	blockEditor,
	classicEditor,
	admin,
	customizer,
	wpPolyfills,
	setup,
];
