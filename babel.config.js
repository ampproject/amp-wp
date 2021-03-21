/**
 * External dependencies
 */
const { extendDefaultPlugins } = require( 'svgo' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/babel-preset-default' );

module.exports = function( api ) {
	const config = defaultConfig( api );

	return {
		...config,
		plugins: [
			...config.plugins,
			'@babel/plugin-proposal-class-properties',
			[
				'inline-react-svg',
				{
					svgo: {
						plugins: extendDefaultPlugins( [
							{
								name: 'cleanupIDs',
								params: {
									minify: false, // Prevent duplicate SVG IDs from minification.
								},
							},
						] ),
					},
				},
			],
		],
		sourceMaps: true,
		env: {
			production: {
				plugins: [
					...config.plugins,
					'@babel/plugin-proposal-class-properties',
					[
						'inline-react-svg',
						{
							svgo: {
								plugins: extendDefaultPlugins( [
									{
										name: 'cleanupIDs',
										params: {
											minify: false, // Prevent duplicate SVG IDs from minification.
										},
									},
								] ),
							},
						},
					],
					'transform-react-remove-prop-types',
				],
			},
		},
	};
};
