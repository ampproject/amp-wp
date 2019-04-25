module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	testPathIgnorePatterns: [
		'/\.git/',
		'/node_modules/',
	],
	transformIgnorePatterns: [
		'node_modules/(?!(simple-html-tokenizer)/)',
	],
};
