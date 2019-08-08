/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/babel-preset-default' );

module.exports = function( api ) {
	const config = defaultConfig( api );

	return {
		...config,
		sourceMaps: true,
		env: {
			production: {
				plugins: [
					...config.plugins,
					'transform-react-remove-prop-types',
				],
			},
		},
	};
};
