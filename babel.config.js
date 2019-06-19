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
			'inline-react-svg',
		],
		sourceMaps: true,
		env: {
			production: {
				plugins: [
					...config.plugins,
					'inline-react-svg',
					'transform-react-remove-prop-types',
				],
			},
		},
	};
};
