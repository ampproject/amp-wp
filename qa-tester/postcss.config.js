module.exports = {
	plugins: [
		require( 'postcss-import' ),
		require( 'postcss-preset-env' )( {
			stage: 0,
			preserve: false, // Omit pre-polyfilled CSS.
			features: {
				'nesting-rules': false, // Uses postcss-nesting which doesn't behave like Sass.
				'custom-properties': {
					preserve: true, // Do not remove :root selector.
				},
			},
			autoprefixer: {
				grid: true,
			},
		} ),
	],
};
