/**
 * External dependencies
 */
const path = require( 'path' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );
const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const WebpackBar = require( 'webpackbar' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const { defaultRequestToExternal, defaultRequestToHandle, camelCaseDash } = require( '@wordpress/dependency-extraction-webpack-plugin/lib/util' );

const sharedConfig = {
	...defaultConfig,
	output: {
		path: path.resolve( process.cwd(), 'assets', 'js' ),
		filename: '[name].js',
		chunkFilename: '[name].js',
	},
	// TODO: Remove the `module` override once @wordpress/scripts upgrades to PostCSS 8.
	module: {
		...defaultConfig.module,
		rules: defaultConfig.module.rules.map(
			( rule ) => {
				const postCssLoader = Array.isArray( rule.use ) && rule.use.find(
					( loader ) => loader.loader && loader.loader.includes( 'postcss-loader' ),
				);

				if ( postCssLoader ) {
					postCssLoader.loader = 'postcss-loader';
				}

				return rule;
			},
		),
	},
	plugins: [
		...defaultConfig.plugins
			.map(
				( plugin ) => {
					if ( plugin.constructor.name === 'MiniCssExtractPlugin' ) {
						plugin.options.filename = '../css/[name].css';
					}
					return plugin;
				},
			)
			.filter( ( plugin ) => plugin.constructor.name !== 'CleanWebpackPlugin' ),
		new RtlCssPlugin( {
			filename: '../css/[name]-rtl.css',
		} ),
	],
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
	...sharedConfig,
	entry: {
		'amp-validated-url-post-edit-screen': './assets/src/amp-validation/amp-validated-url-post-edit-screen.js',
		'amp-validated-urls-index': './assets/src/amp-validation/amp-validated-urls-index.js',
		'amp-validation-detail-toggle': './assets/src/amp-validation/amp-validation-detail-toggle.js',
		'amp-validation-single-error-url-details': './assets/src/amp-validation/amp-validation-single-error-url-details.js',
		'amp-validation-counts': './assets/src/amp-validation/counts/index.js',
	},
	plugins: [
		...sharedConfig.plugins,
		new WebpackBar( {
			name: 'AMP Validation',
			color: '#1c5fec',
		} ),
	],
};

const blockEditor = {
	...sharedConfig,
	externals: {
		// Make localized data importable.
		'amp-block-editor-data': 'ampBlockEditor',
		'amp-block-validation': 'ampBlockValidation',
	},
	entry: {
		'amp-block-editor': './assets/src/block-editor/index.js',
		'amp-block-validation': './assets/src/block-validation/index.js',
	},
	plugins: [
		...sharedConfig.plugins,
		new WebpackBar( {
			name: 'Block Editor',
			color: '#1773a8',
		} ),
	],
};

const classicEditor = {
	...sharedConfig,
	entry: {
		'amp-post-meta-box': './assets/src/classic-editor/amp-post-meta-box.js',
	},
	plugins: [
		...sharedConfig.plugins,
		new WebpackBar( {
			name: 'Classic Editor',
			color: '#dc3232',
		} ),
	],
};

const admin = {
	...sharedConfig,
	entry: {
		'amp-validation-tooltips': './assets/src/admin/amp-validation-tooltips.js',
		'amp-paired-browsing-app': './assets/src/admin/paired-browsing/app.js',
		'amp-paired-browsing-client': './assets/src/admin/paired-browsing/client.js',
	},
	plugins: [
		...sharedConfig.plugins,
		new WebpackBar( {
			name: 'Admin',
			color: '#67b255',
		} ),
	],
};

const customizer = {
	...sharedConfig,
	entry: {
		'amp-customize-controls': './assets/src/customizer/amp-customize-controls.js',
		'amp-customize-controls-legacy': './assets/src/customizer/amp-customize-controls-legacy.js',
		'amp-customize-preview-legacy': './assets/src/customizer/amp-customize-preview-legacy.js',
		'amp-customizer-design-preview-legacy': './assets/src/customizer/amp-customizer-design-preview-legacy.js',
	},
	plugins: [
		...sharedConfig.plugins,
		new WebpackBar( {
			name: 'Customizer',
			color: '#f27136',
		} ),
	],
};

const WORDPRESS_NAMESPACE = '@wordpress/';
const BABEL_NAMESPACE = '@babel/';
const gutenbergPackages = [ '@babel/polyfill', '@wordpress/dom-ready', '@wordpress/i18n', '@wordpress/url' ].map(
	( packageName ) => {
		if ( 0 !== packageName.indexOf( WORDPRESS_NAMESPACE ) && 0 !== packageName.indexOf( BABEL_NAMESPACE ) ) {
			return null;
		}

		const camelCaseName = '@wordpress/i18n' === packageName
			? 'i18n'
			: camelCaseDash( packageName.replace( WORDPRESS_NAMESPACE, '' ).replace( BABEL_NAMESPACE, '' ) );

		const handle = packageName.replace( WORDPRESS_NAMESPACE, 'wp-' ).replace( BABEL_NAMESPACE, 'wp-' );

		return {
			camelCaseName,
			entryPath: 'polyfill' === camelCaseName ? path.resolve( __dirname, 'assets/src/polyfills/wp-polyfill' ) : packageName,
			handle,
			packageName,
		};
	},
).filter( ( packageData ) => packageData );

const wpPolyfills = {
	...sharedConfig,
	externals: {},
	entry: gutenbergPackages.reduce(
		( memo, { camelCaseName, entryPath } ) =>
			( { ...memo, [ camelCaseName ]: entryPath } ),
		{} ),
	output: {
		devtoolNamespace: 'wp',
		filename: ( pathData ) => `${ gutenbergPackages.find(
			( gutenbergPackage ) => pathData.chunk.name === gutenbergPackage.camelCaseName,
		).handle }.js`,
		path: path.resolve( __dirname, 'assets/js' ),
		library: [ 'wp', '[name]' ],
		libraryTarget: 'this',
	},
	plugins: [
		new DependencyExtractionWebpackPlugin( {
			useDefaults: false,
			requestToHandle: ( request ) => {
				if ( gutenbergPackages.find( ( { packageName } ) => packageName === request ) ) {
					return undefined;
				}

				return defaultRequestToHandle( request );
			},
			requestToExternal: ( request ) => {
				if ( gutenbergPackages.find( ( { packageName } ) => packageName === request ) ) {
					return undefined;
				}

				return defaultRequestToExternal( request );
			},
		} ),
		new CopyWebpackPlugin( {
			patterns: [
				{
					from: 'node_modules/lodash/lodash.js',
					to: './vendor/lodash.js',
				},
			],
		} ),
		new WebpackBar( {
			name: 'WordPress Polyfills',
			color: '#21a0d0',
		} ),
	],
};

const setup = {
	...sharedConfig,
	entry: {
		'amp-onboarding-wizard': [
			'./assets/src/onboarding-wizard',
		],
	},
	externals: {
		'amp-settings': 'ampSettings',
	},
	plugins: [
		...sharedConfig.plugins.filter(
			( plugin ) => plugin.constructor.name !== 'DependencyExtractionWebpackPlugin',
		),
		new DependencyExtractionWebpackPlugin( {
			useDefaults: false,
			// Most dependencies will be bundled for the AMP setup screen for compatibility across WP versions.
			requestToHandle: ( handle ) => {
				switch ( handle ) {
					case 'lodash':
					case '@wordpress/api-fetch':
					case '@wordpress/dom-ready':
					case '@wordpress/html-entities':
					case '@wordpress/url':
					case '@wordpress/i18n':
						return defaultRequestToHandle( handle );

					default:
						return undefined;
				}
			},
			requestToExternal: ( external ) => {
				switch ( external ) {
					case 'lodash':
					case '@wordpress/api-fetch':
					case '@wordpress/dom-ready':
					case '@wordpress/html-entities':
					case '@wordpress/url':
					case '@wordpress/i18n':
						return defaultRequestToExternal( external );

					default:
						return undefined;
				}
			},
		} ),
		new WebpackBar( {
			name: 'Onboarding wizard',
			color: '#1773a8',
		} ),
	],
};

const settingsPage = {
	...sharedConfig,
	entry: {
		'wp-api-fetch': [
			'./assets/src/polyfills/api-fetch.js',
		],
		'wp-components': [
			'@wordpress/components/build-style/style.css',
		],
		'amp-settings': [
			'./assets/src/settings-page',
		],
	},
	externals: {
		'amp-settings': 'ampSettings',
	},
	resolve: {
		alias: {
			'@wordpress/api-fetch__non-shim': require.resolve( '@wordpress/api-fetch' ),
		},
	},
	plugins: [
		...sharedConfig.plugins.filter(
			( plugin ) => plugin.constructor.name !== 'DependencyExtractionWebpackPlugin',
		),
		new DependencyExtractionWebpackPlugin( {
			useDefaults: false,
			// Most dependencies will be bundled for the AMP setup screen for compatibility across WP versions.
			requestToHandle: ( handle ) => {
				switch ( handle ) {
					case 'lodash':
					case '@wordpress/api-fetch':
					case '@wordpress/i18n':
						return defaultRequestToHandle( handle );

					default:
						return undefined;
				}
			},
			requestToExternal: ( external ) => {
				switch ( external ) {
					case 'lodash':
					case '@wordpress/api-fetch':
					case '@wordpress/i18n':
						return defaultRequestToExternal( external );

					default:
						return undefined;
				}
			},
		} ),
		new WebpackBar( {
			name: 'Settings page',
			color: '#67b255',
		} ),
	],
};

const mobileRedirection = {
	...sharedConfig,
	entry: {
		'mobile-redirection': './assets/src/mobile-redirection.js',
	},
	plugins: [
		...sharedConfig.plugins,
		new WebpackBar( {
			name: 'Mobile Redirection',
			color: '#f27136',
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
	settingsPage,
	mobileRedirection,
];
