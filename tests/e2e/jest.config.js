module.exports = {
	...require( '@wordpress/scripts/config/jest-e2e.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/../../node_modules/@wordpress/scripts/config/babel-transform',
	},
	transformIgnorePatterns: [
		'node_modules',
	],
	setupFilesAfterEnv: [
		'<rootDir>/config/bootstrap.js',
		'expect-puppeteer',
	],
	testMatch: [
		'**/specs/**/*.js',
		'**/?(*.)spec.js',
	],
	testPathIgnorePatterns: [
		'.git',
		'node_modules',
	],
};
