module.exports = {
	...require( '@wordpress/scripts/config/jest-e2e.config' ),
	setupFilesAfterEnv: [
		'<rootDir>/config/bootstrap.js',
	],
	testMatch: [
		'<rootDir>/specs/**/__tests__/**/*.js',
		'<rootDir>/specs/**/?(*.)(spec|test).js',
		'<rootDir>/specs/**/test/*.js',
		'<rootDir>/stories-editor/**/?(*.)(spec|test).js',
	],
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/../../node_modules/@wordpress/scripts/config/babel-transform',
	},
	transformIgnorePatterns: [
		'node_modules',
	],
	testPathIgnorePatterns: [
		'.git',
		'node_modules',
	],
};
