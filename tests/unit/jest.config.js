module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	setupFiles: [
		'core-js/es/regexp/replace.js',
		'core-js/es/function/name',
		'core-js/es/promise',
		'core-js/es/symbol/async-iterator',
	],
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
