module.exports = {
	rootDir: '../../',
	...require( '@wordpress/scripts/config/jest-unit.config' ),
	transform: {
		'^.+\\.[jt]sx?$': '<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	setupFiles: [
		'<rootDir>/tests/unit/setup-globals',
	],
	testPathIgnorePatterns: [
		'<rootDir>/.git',
		'<rootDir>/node_modules',
		'<rootDir>/build',
		'.*/e2e/.*',
	],
	coveragePathIgnorePatterns: [ '/node_modules/', '<rootDir>/build/', '<rootDir>/assets/src/test/helpers/' ],
	coverageReporters: [ 'lcov' ],
	coverageDirectory: '<rootDir>/build/logs',
};
