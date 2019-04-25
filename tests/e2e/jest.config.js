module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-e2e.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	setupFilesAfterEnv: [
		'<rootDir>/node_modules/@wordpress/jest-preset-default/scripts/setup-test-framework.js',
		'expect-puppeteer',
	],
	transformIgnorePatterns: [
		'node_modules',
		'<rootDir>/node_modules/@wordpress/scripts/config/puppeteer.config.js',
	],
	testPathIgnorePatterns: [
		'/\.git/',
		'/node_modules/',
	],
};
