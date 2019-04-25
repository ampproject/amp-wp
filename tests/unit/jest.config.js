module.exports = {
	rootDir: '../../',
	preset: '@wordpress/jest-preset-default',
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
